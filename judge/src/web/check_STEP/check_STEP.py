import subprocess
import tempfile
import re
import os
import sys
import ast
import json

def is_tag_line(line):
    """태그 줄인지 판별"""
    return bool(re.match(r"\s*\[.*_(start|end)\(\d+\)\]\s*", line))

def is_start_tag(line):
    """블럭 시작 태그인지 판별"""
    return "start" in line

def read_code_lines(filename):
    """코드 파일 읽기"""
    with open(filename, 'r') as f:
        return f.readlines()

def replace_block(code_blocks, block_index, new_block):
    """지정한 블럭을 새 블럭으로 교체"""
    if 0 <= block_index < len(code_blocks):
        code_blocks[block_index] = new_block
    return code_blocks

def get_blocks(code_lines):
    """코드에서 블럭 단위로 추출"""
    all_blocks = []
    current_block = []
    blocks = []
    block_indices = []
    blocks_idx = 0

    for line in code_lines:
        if is_tag_line(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append(blocks_idx)
                blocks_idx += 1
                current_block = []
            current_block.append(line)
        else:
            current_block.append(line)

    if current_block:
        blocks.append(current_block)
        all_blocks.append(current_block)
        block_indices.append(blocks_idx)

    return blocks, all_blocks, block_indices

def compile_and_run(final_code, test_in_path):
    """코드 컴파일 및 테스트 케이스 실행"""
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(final_code)
        temp_file.flush()
        temp_c_path = temp_file.name

    try:
        subprocess.run(['gcc', temp_c_path, '-o', 'test_program'], check=True, stderr=subprocess.PIPE)
    except subprocess.CalledProcessError as e:
        print(f"[❌] 컴파일 실패: {e.stderr.decode()}")
        return False

    # 테스트 케이스 실행
    test_files = [f for f in os.listdir(test_in_path) if f.endswith('.in')]
    test_files.sort()

    for in_file in test_files:
        base_name = os.path.splitext(in_file)[0]
        out_file = base_name + '.out'
        in_path = os.path.join(test_in_path, in_file)
        out_path = os.path.join(test_in_path, out_file)

        # 입력 파일 읽기
        with open(in_path, 'r') as fin:
            full_input = fin.read()

        result = subprocess.run(
            ['./test_program'],
            input=full_input,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True
        )

        with open(out_path, 'r') as fout:
            expected_output = fout.read().strip()

        actual_output = result.stdout.strip()

        if actual_output != expected_output:
            print(f"❌ {base_name}: 출력 불일치")
            print(f"예상: {expected_output}\n실제: {actual_output}")
            return False

    print("✅ 모든 테스트 케이스 통과")
    return True

def main():
    if len(sys.argv) != 4:
        print("Usage: python3 script.py <problem_id> <line_num> <student_code>")
        sys.exit(1)

    problem_id = sys.argv[1]
    line_num = int(sys.argv[2])
    student_code = sys.argv[3].encode('utf-8').decode('unicode_escape')

    filename = f"../tagged_code/{problem_id}_step2.txt"
    test_in_path = f"../../../data/{problem_id}"

    code_lines = read_code_lines(filename)
    blocks, all_blocks, block_indices = get_blocks(code_lines)

    # 코드 블록 교체
    new_block = [line + '\n' for line in student_code.split('\\n')]
    blocks[line_num] = new_block
    all_blocks[block_indices[line_num]] = new_block

    # 최종 코드 생성
    final_code = ''.join(line for block in all_blocks for line in block)

    # 교체된 코드 디버깅
    with open("/tmp/updated_code.c", "w") as f:
        f.write(final_code)

    # 컴파일 및 실행
    if compile_and_run(final_code, test_in_path):
        print("correct")
    else:
        print("no")

if __name__ == "__main__":
    main()