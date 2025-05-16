import subprocess
import tempfile
import re
import os
import sys
import ast
import json

# 디버깅 로그 파일 설정
log_file = "/tmp/python_script_debug.log"

def log_debug(message):
    with open(log_file, "a") as f:
        f.write(message + "\n")

try:
    # 입력 인자 확인
    log_debug(f"Received args: {sys.argv}")

    # 파일 경로 파라미터 확인
    param_file = sys.argv[1]
    feedback_file = sys.argv[2]
    log_debug(f"Param file: {param_file}, Feedback file: {feedback_file}")

    # JSON 파일 읽기
    with open(param_file, 'r', encoding='utf-8') as f:
        params = json.load(f)
        log_debug(f"Loaded params: {params}")

    # 피드백 파일 작성
    with open(feedback_file, 'w', encoding='utf-8') as f:
        f.write("correct")
    log_debug(f"Successfully wrote to feedback file: {feedback_file}")

except Exception as e:
    error_message = f"Error: {str(e)}"
    log_debug(error_message)
    print(error_message)
    sys.exit(1)
    
    
def is_tag_line(line):
    """태그 줄인지 판별"""
    return bool(re.match(r"\s*\[.*_(start|end)\(\d+\)\]\s*", line))

def is_start_tag(line):
    """블럭 시작 태그인지 판별"""
    return "start" in line

def is_include_line(line):
    """헤더 선언(#include)인지 판별"""
    return line.strip().startswith("#")

def is_single_brace(line):
    """단독 중괄호인지 판별"""
    return line.strip() == "}"

def filter_code_lines(code_lines):
    """태그 줄 제거된 실제 코드 줄만 반환"""
    return [line for line in code_lines if not is_tag_line(line)]

def get_blocks(code_lines):
    """코드에서 블럭 단위로 추출"""
    all_blocks = []
    all_idx = 0
    blocks = []
    blocks_idx = 0
    current_block = []
    includes = []  # #include 블럭 저장
    closing_braces = []  # 단독 } 블럭 저장
    inside_block = False
    block_indices = []

    for line in code_lines:
        # 헤더 선언 (#include)은 상수 블럭으로 처리
        if is_include_line(line):
            includes.append(line)
            all_blocks.append(includes)
            all_idx += 1
            includes = []
            continue
        
        # 단독 중괄호는 상수 블럭으로 처리
        if is_single_brace(line):
            closing_braces.append(line)
            all_blocks.append(closing_braces)
            all_idx += 1
            closing_braces = []
            continue
        
        # 블럭 시작 조건: start 태그를 만나면 새 블럭 시작
        if is_start_tag(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            current_block.append(line)
            inside_block = True
        
        # 블럭 종료 조건: 다음 블럭의 시작 태그를 만나면 블럭 종료
        elif is_tag_line(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            inside_block = False
        
        # 블럭 내부 코드 추가
        if inside_block or not is_tag_line(line):
            current_block.append(line)

    return includes, blocks, closing_braces, all_blocks, block_indices

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def replace_block(code_blocks, block_index, new_block):
    """지정한 블럭을 새 블럭으로 교체"""
    if 0 <= block_index < len(code_blocks):
        code_blocks[block_index] = new_block
    return code_blocks

def validate_code_output_full_io(code_lines, test_in_path):
    """전체 test.in을 입력하고 전체 출력과 비교"""
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(''.join(code_lines))
        temp_file.flush()

        try:
            subprocess.run(
                ['/usr/bin/gcc', '-o', 'test_program', temp_file.name],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                check=True
            )
        except subprocess.CalledProcessError as e:
            print(f"[❌] 컴파일 실패:\n{e.stderr}")
            return False

    test_files = [f for f in os.listdir(test_in_path) if f.endswith('.in')]
    test_files.sort()

    for in_file in test_files:
        base_name = os.path.splitext(in_file)[0]
        out_file = base_name + '.out'
        in_path = os.path.join(test_in_path, in_file)
        out_path = os.path.join(test_in_path, out_file)

        with open(in_path, 'r') as fin:
            full_input = fin.read()
        with open(out_path, 'r') as fout:
            expected_output = fout.read().strip()

        result = subprocess.run(
            ['./test_program'],
            input=full_input,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=5
        )
        actual_output = result.stdout.strip()

        if actual_output != expected_output:
            print(f"❌ {base_name}: 출력 불일치")
            print(f"예상: {expected_output}\n실제: {actual_output}")
            return False

    print("✅ 모든 테스트 케이스 통과")
    return True

def main():
    if len(sys.argv) != 5:
        print("Usage: python3 script.py <problem_id> <step> <line_num> <student_code>")
        sys.exit(1)

    pid = sys.argv[1]
    step = sys.argv[2]
    line_num = int(sys.argv[3])
    student_code = ast.literal_eval(f"'{sys.argv[4]}'")

    # 파일 경로 설정
    filename = f"../tagged_code/{pid}_step{step}.txt"
    test_in_path = f"../../../data/{pid}"

    # 코드 읽기
    code_lines = read_code_lines(filename)

    # 블럭 단위로 코드 파싱
    includes, blocks, closing_braces, all_blocks, block_indices = get_blocks(code_lines)

    # 교체할 코드 블럭
    new_block = [line + '\n' for line in student_code.split('\\n')]
    if not (0 <= line_num < len(blocks)):
        print("⚠️ 유효하지 않은 블럭 번호입니다.")
        return

    # 블럭 교체
    blocks[line_num] = new_block
    all_blocks[block_indices[line_num][1]] = new_block

    # 최종 코드 생성
    final_code = ''.join(line for block in all_blocks for line in block)
    final_code = re.sub(r'\[[^\]]*\]', '', final_code)

    # 컴파일 및 테스트
    if validate_code_output_full_io(final_code, test_in_path):
        print("correct")
    else:
        print("no")

if __name__ == "__main__":
    main()