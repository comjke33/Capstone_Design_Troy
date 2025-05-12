import subprocess
import tempfile
import re
import os

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
            all_blocks.append(line)
            all_idx += 1
            continue
        
        # 단독 중괄호는 상수 블럭으로 처리
        if is_single_brace(line):
            all_blocks.append(line)
            all_idx += 1
            continue
        
        # 블럭 시작 조건: start 태그를 만나면 새 블럭 시작
        if is_start_tag(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
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
    for idx, block in enumerate(blocks):
        print(f"\n🔹 블럭 {idx + 1}")
        for line in block:
            print(line.rstrip())

def compile_and_run(code, test_in_path, test_out_path):
    """코드 컴파일 및 테스트 케이스 실행"""
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(code)
        temp_file.flush()

        exe_path = temp_file.name.replace(".c", "")

        try:
            # 컴파일
            compile_result = subprocess.run(
                ['clang', temp_file.name, '-o', exe_path],
                stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True
            )
            if compile_result.returncode != 0:
                print(f"[❌] 컴파일 오류:\n{compile_result.stderr}")
                return

            # 테스트 입력/출력 파일 불러오기
            with open(test_in_path, 'r') as fin:
                test_input = fin.read()
            with open(test_out_path, 'r') as fout:
                expected_output = fout.read().strip()

            # 실행
            run_result = subprocess.run(
                [exe_path],
                input=test_input,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                timeout=5
            )
            actual_output = run_result.stdout.strip()

            # 결과 비교
            if actual_output == expected_output:
                print("✅ 정답입니다!")
            else:
                print("❌ 출력 불일치:")
                print("예상 출력:")
                print(expected_output)
                print("실제 출력:")
                print(actual_output)

        except subprocess.TimeoutExpired:
            print("⏰ 실행 시간 초과")
        finally:
            os.remove(temp_file.name)
            if os.path.exists(exe_path):
                os.remove(exe_path)

def main():
    # 파일 경로 설정
    filename = "../tagged_code/1292_step2.txt"
    test_in_path = "../../../data/1292/sample.in"
    test_out_path = "../../../data/1292/sample.out"
    
    # 코드 읽기
    code_lines = read_code_lines(filename)

    # 블럭 단위로 코드 파싱
    includes, blocks, closing_braces, all_blocks, block_indices = get_blocks(code_lines)

    print("🔧 #include 블럭")
    print("".join(includes))

    print_blocks(blocks)

    try:
        block_num = int(input("\n✏️ 교체할 블럭 번호 입력 (1부터 시작): ")) - 1
        new_code = input("✏️ 교체할 코드 블럭 입력 (줄바꿈은 \\n 사용): ")
    except ValueError:
        print("⚠️ 잘못된 입력입니다.")
        return

    if not (0 <= block_num < len(blocks)):
        print("⚠️ 유효하지 않은 블럭 번호입니다.")
        return

    # 새 코드 블럭 생성
    new_block = [line + '\n' for line in new_code.split('\\n')]
    blocks[block_num] = new_block
    all_blocks[block_indices[block_num][1]] = new_block

    # 블럭을 합쳐서 코드 생성
    final_code = ''.join(line for block in all_blocks for line in block)
    print("\n🔄 수정된 코드:")
    for block in all_blocks:
        for line in block:
            print(line)

    print("---------------------")

    print(final_code)

    # 수정된 코드 컴파일 및 테스트
    compile_and_run(final_code, test_in_path, test_out_path)

if __name__ == "__main__":
    main()