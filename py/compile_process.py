import re
import subprocess
import sys
import json

# ⚠️ 실행 시 문제 유발 가능성이 높은 경고 메시지 패턴 목록
DANGEROUS_WARNINGS = [
    r"incompatible pointer",
    r"comparison between pointer and integer",
    r"makes pointer from integer",
    r"cast to pointer from integer",
    r"array subscript is above array bounds",
    r"function returns address of local variable",
    r"format specifies type .* but the argument has type"
]

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

    match = re.search(r"ERROR: AddressSanitizer: ([^\n]+)", stderr)
    if not match:
        return None

    return {
        "file_name": "runtime",
        "level": "RUNTIME_ERROR",
        "line": None,
        "column": None,
        "message": f"AddressSanitizer: {match.group(1)}",
        "flag": None,
        "highlighted_code": stderr.splitlines()[0] if stderr else ""
    }

def compile_with_clang(source_file, output_file="a.out"):
    cmd = ["clang", source_file, "-Wall", "-Werror=incompatible-pointer-types", "-fsanitize=address", "-g", "-ftrapv"]
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
            if result:
                msg = result["message"].lower()
                # ERROR거나, 위험한 경고 패턴이면 에러로 승격
                if result["level"] == "ERROR" or any(re.search(pat, msg) for pat in DANGEROUS_WARNINGS):
                    result["level"] = "ERROR"
                    stderrs.append(result)

        runtime_stdout = ""
        runtime_stderr = ""

        # ❗ 위험 경고가 없을 때만 런타임 실행
        if returncode == 0 and not stderrs:
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