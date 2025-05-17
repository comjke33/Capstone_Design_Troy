import subprocess
import tempfile
import re
import os
import sys
import ast
import uuid
import json


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

    # # 마지막 블럭 추가
    # if current_block:
    #     blocks.append(current_block)
    #     # 인덱스 매칭
    #     block_indices.append((blocks_idx, all_idx))

    #     blocks_idx += 1
    # all_blocks.append(current_block)
    # all_idx += 1

    return includes, blocks, closing_braces, all_blocks, block_indices

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def replace_block(code_blocks, block_index, new_block):
    """지정한 블럭을 새 블럭으로 교체"""
    if 0 <= block_index < len(code_blocks):
        code_blocks[block_index] = new_block
    return code_blocks

def clean_block(block):
    """블럭에서 태그를 제거하여 반환"""
    return [line for line in block if not is_tag_line(line)]

def print_blocks(blocks):
    """블럭들을 순서대로 출력"""
    # for idx, block in enumerate(blocks):
    #     # print(f"\n🔹 블럭 {idx + 1}")
    #     for line in block:
            # print(line.rstrip())


def generate_unique_name():
    """유니크한 실행 파일 이름 생성"""
    return f"test_program_{uuid.uuid4().hex}"


def validate_code_output_full_io(code_lines, test_in_path, test_out_path):
    """전체 test.in을 입력하고 전체 출력과 비교"""
    # 임시 파일에 코드를 작성
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(''.join(code_lines))
        temp_file.flush()
        temp_c_path = temp_file.name

        try:
            env = os.environ.copy()
            env["PATH"] = "/usr/bin:" + env.get("PATH", "")
            # 1. 컴파일
            compile_result = subprocess.run(
                ['/usr/bin/gcc', '-o', 'test_program', temp_c_path],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                check=True,
                env=env
            )
        except subprocess.CalledProcessError as e:
            print(f"[❌] 컴파일 실패:\n{e.stderr}")
            return False
        
    test_files = [f for f in os.listdir(test_in_path) if f.endswith('.in')]
    test_files.sort()

    all_passed = True

    for in_file in test_files:
        base_name = os.path.splitext(in_file)[0]
        out_file = base_name + '.out'

        in_path = os.path.join(test_in_path, in_file)
        out_path = os.path.join(test_in_path, out_file)

        # 입력/출력 파일 읽기
        with open(in_path, 'r') as fin:
            full_input = fin.read()
        with open(out_path, 'r') as fout:
            expected_output = fout.read().strip()

        # 프로그램 실행
        result = subprocess.run(
            ['./test_program'],
            input=full_input,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=5
        )
        actual_output = result.stdout.strip()

        # 결과 비교
        if actual_output != expected_output:
            print(f"[❌] 테스트 실패: {base_name}")
            all_passed = False

    return all_passed
def main():
    if len(sys.argv) != 2:
        print("Usage: python3 check_STEP.py <param_file>")
        sys.exit(1)

    param_file = sys.argv[1]

    # 파일에서 JSON 파라미터 읽기
    with open(param_file, 'r', encoding='utf-8') as f:
        params = json.load(f)

    pid = params["problem_id"]
    step = params["step"]
    line_num = int(params["index"])
    student_code = params["answer"]

    print(f"Problem ID: {pid}, Step: {step}, Index: {line_num}, Code: {student_code}")

    filename = f"../tagged_code/{pid}_step{step}.txt"
    test_in_path = f"../../../data/{pid}"

    code_lines = read_code_lines(filename)

    # 최종 코드 생성
    final_code = student_code + '\n'
    final_code = re.sub(r'\[[^\]]*\]', '', final_code)

    # 컴파일 및 실행
    if validate_code_output_full_io(final_code, test_in_path):
        print("correct")
    else:
        print("no")

if __name__ == "__main__":
    main()