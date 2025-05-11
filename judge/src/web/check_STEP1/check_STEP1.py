import os
from tree_sitter import Language, Parser

# Tree-sitter C 언어 모듈 로드 (미리 빌드되어 있어야 함)
C_LANGUAGE_PATH = "./build/my-languages.so"  # 이 경로는 사용자 환경에 따라 조정 필요
C_LANGUAGE = Language(C_LANGUAGE_PATH, "c")

parser = Parser()
parser.set_language(C_LANGUAGE)

def load_code_with_tags(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def strip_tags_and_get_code(lines):
    return [line for line in lines if not line.strip().startswith('[')]

def replace_line(lines, target_line_num, student_line):
    stripped_lines = strip_tags_and_get_code(lines)
    # 타겟 라인 번호는 1-based라고 가정
    if 1 <= target_line_num <= len(stripped_lines):
        original_line = stripped_lines[target_line_num - 1]
        stripped_lines[target_line_num - 1] = student_line + "\n"
        return original_line.strip(), student_line.strip(), ''.join(stripped_lines)
    else:
        raise IndexError("Invalid line number")

def parse_code(code, parser):
    try:
        tree = parser.parse(bytes(code, 'utf8'))
        return tree
    except Exception as e:
        print("Parse error:", e)
        return None

def main():
    filename = "1290.step1.txt"
    if not os.path.exists(filename):
        print(f"파일 '{filename}'이 존재하지 않습니다.")
        return

    original_lines = load_code_with_tags(filename)
    try:
        line_number = int(input("Enter line number to replace: "))
        student_line = input("Enter student code line: ")

        original_line, new_line, modified_code = replace_line(original_lines, line_number, student_line)

        print(f"\nReplaced line {line_number}:")
        print(f"  original: {original_line}")
        print(f"  new     : {new_line}")

        print("\nParsing original code...")
        original_code = ''.join(strip_tags_and_get_code(original_lines))
        tree_original = parse_code(original_code, parser)
        if tree_original is None:
            print("Failed to parse original code.")
            return

        print("Parsing modified code...")
        tree_modified = parse_code(modified_code, parser)
        if tree_modified is None:
            print("Failed to parse modified code.")
            return

        if tree_original.root_node.sexp() == tree_modified.root_node.sexp():
            print("\n✅ ASTs match: student code is semantically correct.")
        else:
            print("\n❌ ASTs do not match: student code changes the program structure.")

    except Exception as e:
        print("오류 발생:", e)

if __name__ == "__main__":
    main()
