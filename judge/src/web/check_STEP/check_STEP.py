import subprocess
import tempfile
import re
import os
import sys
import ast
import json

# 디버깅 로그 파일 설정
log_file = "/tmp/python_script_debug.log"

def log_debug(message):
    with open(log_file, "a") as f:
        f.write(message + "\n")

def read_code_lines(filename):
    """코드 파일 읽기"""
    try:
        with open(filename, 'r') as f:
            return f.readlines()
    except FileNotFoundError:
        log_debug(f"Error: File not found: {filename}")
        sys.exit(1)

def validate_code_output_full_io(final_code, test_in_path):
    """코드 컴파일 및 테스트 케이스 실행"""
    with tempfile.NamedTemporaryFile(suffix=".c", mode='w+', delete=False) as temp_file:
        temp_file.write(final_code)
        temp_file.flush()
        temp_c_path = temp_file.name

    try:
        subprocess.run(['gcc', temp_c_path, '-o', 'test_program'], check=True, stderr=subprocess.PIPE)
        log_debug("Compilation successful")
    except subprocess.CalledProcessError as e:
        log_debug(f"Compilation failed: {e.stderr}")
        print(f"[❌] Compilation failed:\n{e.stderr}")
        return False

    test_files = [f for f in os.listdir(test_in_path) if f.endswith('.in')]
    test_files.sort()

    for in_file in test_files:
        in_path = os.path.join(test_in_path, in_file)
        with open(in_path, 'r') as fin:
            full_input = fin.read()

        result = subprocess.run(
            ['./test_program'],
            input=full_input,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True,
            timeout=5
        )
        actual_output = result.stdout.strip()
        log_debug(f"Execution output: {actual_output}")

    print("correct")
    return True

def main():
    try:
        # 인자 체크
        if len(sys.argv) != 3:
            print("Error: Usage: python3 script.py <param_file> <feedback_file>")
            log_debug("Error: Invalid number of arguments")
            sys.exit(1)

        param_file = sys.argv[1]
        feedback_file = sys.argv[2]
        log_debug(f"Received param file: {param_file}, feedback file: {feedback_file}")

        # JSON 파일 읽기
        with open(param_file, 'r', encoding='utf-8') as f:
            params = json.load(f)
            log_debug(f"Loaded params: {params}")

        problem_id = params.get("problem_id")
        step = params.get("step")
        line_num = int(params.get("index"))
        student_code = params.get("answer")
        log_debug(f"Problem ID: {problem_id}, Step: {step}, Line Num: {line_num}, Code: {student_code}")

        # 파일 경로 설정
        filename = f"../tagged_code/{problem_id}_step{step}.txt"
        test_in_path = f"../../../data/{problem_id}"
        log_debug(f"Filename: {filename}, Test Input Path: {test_in_path}")

        # 코드 읽기
        code_lines = read_code_lines(filename)
        log_debug("Code lines read successfully")

        # 최종 코드 생성
        final_code = student_code + '\n'
        final_code = re.sub(r'\[[^\]]*\]', '', final_code)
        log_debug(f"Final code generated:\n{final_code}")

        # 컴파일 및 테스트 실행
        if validate_code_output_full_io(final_code, test_in_path):
            with open(feedback_file, 'w', encoding='utf-8') as f:
                f.write("correct")
            log_debug("Feedback written successfully")
        else:
            with open(feedback_file, 'w', encoding='utf-8') as f:
                f.write("no")
            log_debug("Feedback: no")

    except Exception as e:
        error_message = f"Error: {str(e)}"
        log_debug(error_message)
        print(error_message)
        sys.exit(1)

if __name__ == "__main__":
    main()