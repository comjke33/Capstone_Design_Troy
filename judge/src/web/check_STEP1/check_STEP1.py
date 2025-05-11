import subprocess
import tempfile
import difflib
import re

def is_tag_line(line):
    """[self_start(n)] 등 태그 줄인지 판별"""
    return bool(re.match(r"\s*\[.*_(start|end)\(\d+\)\]\s*", line))

def filter_code_lines(code_lines):
    """태그 줄 제거된 실제 코드 줄만 반환"""
    return [line for line in code_lines if not is_tag_line(line)]

def get_actual_line_index(code_lines, logical_line_number):
    """
    논리적 줄 번호(= 태그 제외 후 학생이 본 줄 번호) → 실제 파일 내 줄 인덱스 반환
    """
    count = 0
    for i, line in enumerate(code_lines):
        if not is_tag_line(line):
            count += 1
        if count == logical_line_number:
            return i
    return None

def normalize_ast(ast_str):
    """AST 정규화: 공백 및 불필요한 개행 제거"""
    lines = ast_str.splitlines()
    cleaned = [line.strip() for line in lines if line.strip()]
    return '\n'.join(cleaned)

def print_code_with_line_numbers(code_lines, title):
    """태그 줄 제외 후 줄 번호 붙여서 출력"""
    print(f"\n🔹 {title}")
    real_lines = filter_code_lines(code_lines)
    for i, line in enumerate(real_lines, start=1):
        print(f"{i:3}: {line.rstrip()}")

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def generate_ast(code_lines):
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(''.join(code_lines))
        temp_file.flush()
        try:
            result = subprocess.run(
                ['clang', '-Xclang', '-ast-dump', '-fsyntax-only', temp_file.name],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True,
                check=True
            )
            return result.stdout
        except subprocess.CalledProcessError as e:
            print(f"[❌] AST 파싱 실패:\n{e.stderr}")
            return None

def main():
    filename = "1290_step1.txt"
    original_code_lines = read_code_lines(filename)

    print_code_with_line_numbers(original_code_lines, "🔍 원본 코드 (수정할 줄 선택)")

    try:
        line_num = int(input("\n✏️ 바꿀 줄 번호 입력: "))
        student_line = input("✏️ 학생 코드 한 줄 입력: ")
    except ValueError:
        print("⚠️ 숫자와 코드 줄을 올바르게 입력하세요.")
        return

    # 실제 파일 내 줄 인덱스 확인
    actual_idx = get_actual_line_index(original_code_lines, line_num)
    if actual_idx is None:
        print("⚠️ 유효하지 않은 줄 번호입니다.")
        return

    # 코드 교체
    modified_code_lines = original_code_lines[:]
    original_line = modified_code_lines[actual_idx]
    modified_code_lines[actual_idx] = student_line + '\n'

    print(f"\n[🔁] {line_num}번 줄 교체됨:\n  ▶ 원본: {original_line.strip()}\n  ▶ 입력: {student_line.strip()}")
    print_code_with_line_numbers(modified_code_lines, "✏️ 수정된 코드")

    print("\n[🧠] AST 분석 중 (원본)...")
    original_ast = generate_ast(filter_code_lines(original_code_lines))
    if original_ast is None:
        print("[🚫] 원본 코드 AST 생성 실패")
        return

    print("\n[🧠] AST 분석 중 (수정본)...")
    modified_ast = generate_ast(filter_code_lines(modified_code_lines))
    if modified_ast is None:
        print("[🚫] 수정 코드 AST 생성 실패")
        return

    # AST 정규화
    norm_original = normalize_ast(original_ast)
    norm_modified = normalize_ast(modified_ast)

    if norm_original == norm_modified:
        print("\n✅ AST 동일: 의미상 동일한 코드입니다.")
    else:
        print("\n❌ AST 차이 있음 (의미 변경 가능성이 있습니다)")

if __name__ == "__main__":
    main()
