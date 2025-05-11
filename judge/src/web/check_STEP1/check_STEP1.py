import subprocess
import tempfile
import difflib

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

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

    try:
        line_num = int(input("Enter line number to replace: "))
        student_line = input("Enter student code line: ")
    except ValueError:
        print("숫자와 올바른 문자열을 입력해 주세요.")
        return

    modified_code_lines, original_line = replace_line(original_code_lines, line_num, student_line)

    print(f"\n[🔁] Replaced line {line_num}:\n  original: {original_line.strip()}\n  new     : {student_line}")

    print("\n[🧠] Parsing original code...")
    original_ast = generate_ast(original_code_lines)
    if original_ast is None:
        print("[🚫] 원본 코드 AST 생성 실패")
        return

    print("\n[🧠] Parsing modified code...")
    modified_ast = generate_ast(modified_code_lines)
    if modified_ast is None:
        print("[🚫] 수정 코드 AST 생성 실패")
        return

    if original_ast.strip() == modified_ast.strip():
        print("\n✅ AST 동일: 학생 코드가 의미상 동일합니다.")
    else:
        print("\n❌ AST 차이 발생:")
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