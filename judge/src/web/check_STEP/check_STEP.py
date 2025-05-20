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
    includes = []
    closing_braces = []
    inside_block = False
    block_indices = []

    for line in code_lines:
        if is_include_line(line):
            includes.append(line)
            all_blocks.append(includes)
            all_idx += 1
            includes = []
            continue

        if is_single_brace(line):
            closing_braces.append(line)
            all_blocks.append(closing_braces)
            all_idx += 1
            closing_braces = []
            continue

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


        elif is_tag_line(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            inside_block = False


        if inside_block or not is_tag_line(line):
            if line.strip() != "":
                current_block.append(line)

    return includes, blocks, closing_braces, all_blocks, block_indices

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def validate_code_output_full_io(code_lines, test_in_path):
    """코드 컴파일 및 테스트 케이스 실행"""
    exe_name = f"/tmp/test_program_{uuid.uuid4().hex}"
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False, dir="/tmp") as temp_file:
        temp_file.write(''.join(code_lines))
        temp_file.flush()
        temp_c_path = temp_file.name

    try:
        env = os.environ.copy()
        env["PATH"] = "/usr/lib/gcc/x86_64-linux-gnu/11:/usr/bin:/bin:/usr/sbin:/sbin:" + env.get("PATH", "")
        subprocess.run(
            ['gcc', temp_c_path, '-o', exe_name],
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

    for in_file in test_files:
        base_name = os.path.splitext(in_file)[0]
        out_file = base_name + '.out'
        in_path = os.path.join(test_in_path, in_file)
        out_path = os.path.join(test_in_path, out_file)

        with open(in_path, 'r') as fin:
            full_input = fin.read()
        with open(out_path, 'r') as fout:
            expected_output = fout.read().strip()
        print(full_input)
        full_input = full_input if full_input.endswith('\n') else full_input + '\n'

        try:
            result = subprocess.run(
                [exe_name],
                input=full_input,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                timeout=5
            )
            actual_output = result.stdout.strip()

            if actual_output != expected_output:
                print(f"[❌] 테스트 실패: {base_name}")
                print(actual_output)
                print(expected_output)
                return False
        except subprocess.TimeoutExpired:
            print("[❌] 실행 시간 초과")
            return False
        finally:
            if os.path.exists(exe_name):
                os.remove(exe_name)

    return True

def decode_escape_sequences(text):
    """이스케이프 시퀀스를 올바르게 변환"""
    try:
        return text.encode('utf-8').decode('unicode_escape')
    except Exception:
        return text

def main():
    """메인 함수"""
    if len(sys.argv) != 2:
        print("Usage: python3 check_STEP.py <param_file>")
        sys.exit(1)

    param_file = sys.argv[1]

    with open(param_file, 'r', encoding='utf-8') as f:
        params = json.load(f)

    pid = params["problem_id"]
    step = params["step"]
    line_num = int(params["index"])
    answer_file = params["answer_file"]

    with open(answer_file, 'r', encoding='utf-8') as f:
        student_code = f.read().strip()

    filename = f"../tagged_code/{pid}_step{step}.txt"
    test_in_path = f"../../../data/{pid}"

    code_lines = read_code_lines(filename)

    includes, blocks, closing_braces, all_blocks, block_indices = get_blocks(code_lines)

    block_num = int(line_num)
    new_code = student_code

    if not (0 <= block_num < len(blocks)):
        print("no")
        return

    # 올바르게 변환된 코드 블럭 교체
    new_block = [line + '\n' for line in new_code.split('\n')]
    blocks[block_num] = new_block
    all_blocks[block_indices[block_num][1]] = new_block


    final_code = ''.join(line for block in all_blocks for line in block)
    final_code = re.sub(r'\[[^\]]*\]', '', final_code)

    if validate_code_output_full_io(final_code, test_in_path):
        print("correct")
    else:
        print("no")

if __name__ == "__main__":
    main()