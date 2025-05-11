import subprocess
import tempfile
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

def clean_code(code_lines):
    """코드에서 불필요한 공백 및 들여쓰기를 자동으로 처리"""
    cleaned_lines = []
    
    for line in code_lines:
        # 양쪽 공백 제거 (하지만 들여쓰기는 유지)
        line = line.rstrip()
        
        # 여러 공백을 하나로 압축하지 않고 그대로 유지
        cleaned_lines.append(line)
    
    return cleaned_lines

def print_code_with_line_numbers(code_lines, title):
    """태그 줄 제외 후 줄 번호 붙여서 출력"""
    print(f"\n🔹 {title}")
    real_lines = filter_code_lines(code_lines)
    cleaned_lines = clean_code(real_lines)
    for i, line in enumerate(cleaned_lines, start=1):
        print(f"{i:3}: {line.rstrip()}")

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def generate_ast(code_lines):
    """태그를 제거한 코드로만 AST 생성"""
    code_lines_no_tags = filter_code_lines(code_lines)
    
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(''.join(code_lines_no_tags))
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

def adjust_indentation(original_code_lines, modified_code_lines, line_num):
    """수정된 코드의 들여쓰기를 원본 코드에 맞게 조정"""
    # 원본 코드에서 해당 라인의 들여쓰기 수준을 추출
    original_line = original_code_lines[line_num - 1]
    indentation = len(original_line) - len(original_line.lstrip())
    
    # 수정된 코드에 원본 코드의 들여쓰기를 맞춤
    modified_line = modified_code_lines[line_num - 1].strip()
    modified_code_lines[line_num - 1] = ' ' * indentation + modified_line

    return modified_code_lines

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

    # 들여쓰기를 원본 코드에 맞게 조정
    modified_code_lines = adjust_indentation(original_code_lines, modified_code_lines, actual_idx + 1)

    print(f"\n[🔁] {line_num}번 줄 교체됨:\n  ▶ 원본: {original_line.strip()}\n  ▶ 입력: {student_line.strip()}")

    # 수정된 코드 출력 (태그 제거 후)
    print("\n🔹 ✏️ 수정된 전체 코드:")
    real_modified_code = filter_code_lines(modified_code_lines)
    cleaned_modified_code = clean_code(real_modified_code)
    for line in cleaned_modified_code:
        print(line)

    print("\n[🧠] AST 분석 중 (원본)...")
    original_ast = generate_ast(original_code_lines)
    if original_ast is None:
        print("[🚫] 원본 코드 AST 생성 실패")
        return

    print("\n[🧠] AST 분석 중 (수정본)...")
    modified_ast = generate_ast(cleaned_modified_code)
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
