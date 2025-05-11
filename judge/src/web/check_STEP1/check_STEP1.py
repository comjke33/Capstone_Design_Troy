import json
import os
import tempfile
from pycparser import parse_file, c_ast

# 경로 설정
from_path = "for_test.json"

# pycparser fake libc include 경로 설정
import pycparser
fake_libc_path = os.path.join(os.path.dirname(pycparser.__file__), 'utils', 'fake_libc_include')

# AST 파싱 함수
def get_ast_from_code(code_string):
    with tempfile.NamedTemporaryFile(mode='w', suffix='.c', delete=False) as temp:
        temp.write(code_string)
        temp_path = temp.name

    try:
        ast = parse_file(
            temp_path,
            use_cpp=True,
            cpp_path='clang',
            cpp_args=['-E', f'-I{fake_libc_path}']
        )
        os.unlink(temp_path)
        return ast
    except Exception as e:
        print("Parse Error:", e)
        os.unlink(temp_path)
        return None

# 메인 처리
def main():
    with open(from_path, "r") as f:
        data = json.load(f)

    source = data[0]['code']
    lines = source.split('\n')

    print("\n".join([f"{i+1:3d}: {line}" for i, line in enumerate(lines)]))

    # 사용자 입력
    line_num = int(input("Enter line number to replace: "))
    student_code_line = input("Enter student code line: ")

    original_line = lines[line_num - 1]
    lines[line_num - 1] = student_code_line

    modified_code = "\n".join(lines)

    print(f"\nReplaced line {line_num}:")
    print(f"  original: {original_line}")
    print(f"  new     : {student_code_line}")

    # AST 비교
    print("\nParsing original code...")
    original_ast = get_ast_from_code("\n".join(data[0]['code'].split('\n')))
    
    print("Parsing modified code...")
    modified_ast = get_ast_from_code(modified_code)

    if original_ast is None or modified_ast is None:
        print("Failed to parse one or both versions of the code.")
        return

    if repr(original_ast) == repr(modified_ast):
        print("✅ ASTs are identical. Logic unchanged.")
    else:
        print("❌ ASTs differ. Logic may be affected.")

if __name__ == "__main__":
    main()
