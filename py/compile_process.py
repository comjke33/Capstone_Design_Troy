import re
import subprocess
import sys
import json

def extract_error_context(error_message, source_code_path):
    pattern = r"^([^:\s]+):(\d+):(\d+): (warning|error): (.+?)(?: (\[[-\w]+\]))?$"
    match = re.match(pattern, error_message)

    if not match:
        return None

    file_name, line_str, col_str, level, message, flag = match.groups()
    line = int(line_str)
    column = int(col_str)

    try:
        with open(source_code_path, 'r') as f:
            lines = f.readlines()
    except FileNotFoundError:
        raise FileNotFoundError(f"File '{source_code_path}' not found")

    total_lines = len(lines)
    start = max(0, line - 2)
    end = min(total_lines, line + 1)

    output_lines = []

    for i in range(start, end):
        prefix = ">>" if (i + 1) == line else "  "
        line_number = f"{i + 1:>4}"
        code_line = lines[i].rstrip("\n")
        output_lines.append(f"{prefix} {line_number}: {code_line}")
        if (i + 1) == line:
            caret_pos = " " * (column - 1)
            output_lines.append(f"     {' ' * (len(line_number) + 2)}{caret_pos}^")

    return {
        "file_name": file_name,
        "level": level.upper(),
        "line": line,
        "column": column,
        "message": message,
        "flag": flag,
        "highlighted_code": "\n".join(output_lines)
    }

def extract_asan_runtime_error(stderr):
    if "ERROR: AddressSanitizer:" not in stderr:
        return None

    # AddressSanitizer 에러 블록 전체를 잡기 위해 여러 줄 파싱
    error_lines = []
    capturing = False

    for line in stderr.splitlines():
        if "ERROR: AddressSanitizer:" in line:
            capturing = True
        if capturing:
            error_lines.append(line)
        # 다음 오류가 나오기 전까지 수집
        if capturing and line == "":
            break

    if not error_lines:
        return None

    return {
        "file_name": "runtime",
        "level": "RUNTIME_ERROR",
        "line": None,
        "column": None,
        "message": "AddressSanitizer: " + error_lines[0],
        "flag": None,
        "highlighted_code": "\n".join(error_lines)
    }

def compile_with_clang(source_file, output_file="a.out"):
    cmd = [
        "clang", source_file,
        "-Wall",
        "-Werror",
        "-Werror=pointer-compare", 
        "-Werror=incompatible-pointer-types",
        "-fsanitize=address", "-g", "-ftrapv"
    ]
    try:
        result = subprocess.run(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        return result.returncode, result.stdout, result.stderr
    except FileNotFoundError:
        print("❌ Clang이 시스템에 설치되어 있지 않습니다.")
        return -1, "", "Clang not found"

def run_binary(output_file="a.out"):
    try:
        result = subprocess.run(["./" + output_file], stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        return result.returncode, result.stdout, result.stderr
    except Exception as e:
        return -1, "", str(e)

if __name__ == "__main__":
    if len(sys.argv) == 2:
        code = sys.argv[1]
        code_filepath = "compile_target_code.c"

        with open(code_filepath, "w") as f:
            f.write(code)

        returncode, stdout, stderr = compile_with_clang(code_filepath)

        stderrs = []
        for line in stderr.splitlines():
            result = extract_error_context(line, code_filepath)
            if result and (result["level"] in ["ERROR", "WARNING"] or "incompatible" in result["message"].lower()):
                stderrs.append(result)

        runtime_stdout = ""
        runtime_stderr = ""

        # ✅ 컴파일 성공 또는 에러 메시지 없을 때 런타임 검사
        if returncode == 0 or (returncode != 0 and not stderrs):
            run_returncode, runtime_stdout, runtime_stderr = run_binary()
            asan_error = extract_asan_runtime_error(runtime_stderr)
            if asan_error:
                stderrs.append(asan_error)

        results = {
            "returncode": returncode,
            "stdout": stdout,
            "runtime_stdout": runtime_stdout,
            "stderrs": stderrs
        }

        print(json.dumps(results, ensure_ascii=False, indent=2))