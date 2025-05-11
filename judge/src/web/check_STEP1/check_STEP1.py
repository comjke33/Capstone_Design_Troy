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

def print_code_with_line_numbers(code_lines, title):
    print(f"\n🔹 {title}")
    real_lines = filter_code_lines(code_lines)
    for i, line in enumerate(real_lines, start=1):
        print(f"{i:3}: {line.rstrip()}")

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def print_code_with_line_numbers(code_lines, title):
    print(f"\n🔹 {title}")
    for i, line in enumerate(code_lines, start=1):
        print(f"{i:3}: {line.rstrip()}")

def replace_line(code_lines, line_number, student_line):
    new_code = code_lines[:]
    original = new_code[line_number - 1]
    new_code[line_number - 1] = student_line + '\n'
    return new_code, original

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
    original_code_lines = read_code_lines("1290_step1.txt")

    # 태그 제외한 줄만 보여주기
    print_code_with_line_numbers(original_code_lines, "🔍 원본 코드 (수정할 줄 선택)")

    # 입력 받기
    try:
        line_num = int(input("\n✏️ 바꿀 줄 번호 입력: "))
        student_line = input("✏️ 학생 코드 한 줄 입력: ")
    except ValueError:
        print("⚠️ 숫자와 코드 줄을 올바르게 입력하세요.")
        return

    # 태그 제외한 실제 줄들만 필터링해서 줄 번호에 맞는 위치 찾기
    real_lines = filter_code_lines(original_code_lines)
    if not (1 <= line_num <= len(real_lines)):
        print("⚠️ 줄 번호가 유효하지 않습니다.")
        return

    # 실제 줄 번호 매핑 (전체에서 몇 번째 줄인지)
    actual_line_idx = [i for i, line in enumerate(original_code_lines) if not is_tag_line(line)][line_num - 1]

    # 코드 교체
    modified_code_lines = original_code_lines[:]
    original_line = modified_code_lines[actual_line_idx]
    modified_code_lines[actual_line_idx] = student_line + '\n'

    print(f"\n[🔁] {line_num}번 줄 교체됨:\n  원본: {original_line.strip()}\n  입력: {student_line}")

    print_code_with_line_numbers(modified_code_lines, "✏️ 수정된 코드")

    # AST 생성 (태그 제외한 코드로만)
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

    if original_ast.strip() == modified_ast.strip():
        print("\n✅ AST 동일: 의미상 동일한 코드입니다.")
    else:
        print("\n❌ AST 차이 있음 (아래 비교):")
        diff = difflib.unified_diff(
            original_ast.splitlines(),
            modified_ast.splitlines(),
            fromfile='original',
            tofile='modified',
            lineterm=''
        )
        print('\n'.join(diff))


if __name__ == "__main__":
    main()
