import re
import subprocess
import sys
import json


def extract_error_context(error_message, source_code_path):
    pattern = r"^([^:\s]+):(\d+):(\d+): (warning|error): (.+?)(?: (\[[-\w]+\]))?$"
    match = re.match(pattern, error_message)

    if not match:
        return None
        # raise ValueError("Invalid Clang error message format")

    file_name, line_str, col_str, level, message, flag = match.groups()
    line = int(line_str)
    column = int(col_str)

    try:
        with open(source_code_path, 'r') as f:
            lines = f.readlines()
    except FileNotFoundError:
        raise FileNotFoundError(f"File '{source_code_path}' not found")

    total_lines = len(lines)
    start = max(0, line - 2)         # 에러 줄의 이전 줄
    end = min(total_lines, line + 1) # 에러 줄의 다음 줄 포함

    output_lines = []

    for i in range(start, end):
        prefix = ">>" if (i + 1) == line else "  "
        line_number = f"{i + 1:>4}"
        code_line = lines[i].rstrip("\n")
        output_lines.append(f"{prefix} {line_number}: {code_line}")
        if (i + 1) == line:
            caret_pos = " " * (column - 1)
            output_lines.append(f"     {' ' * (len(line_number) + 2)}{caret_pos}^")

    # 줄들을 하나의 문자열로 병합
    highlighted_code = "\n".join(output_lines)

    result = {
        "file_name": file_name,
        "level": level.upper(),
        "line": line,
        "column": column,
        "message": message,
        "flag": flag,
        "highlighted_code": highlighted_code
    }

    return result

def compile_with_clang(source_file, output_file="a.out"):
    cmd = ["clang", source_file, "-Wall", "-Werror"]

    try:
        result = subprocess.run(
            cmd,
            stdout=subprocess.PIPE,
            stderr=subprocess.PIPE,
            text=True
        )

        return result.returncode, result.stdout, result.stderr

    except FileNotFoundError:
        print("❌ Clang이 시스템에 설치되어 있지 않습니다.")
        return -1, "", "Clang not found"

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
            stderrs.append(result)

        stderrs = [r for r in stderrs if r is not None]
    
        results = {
            "returncode": returncode,
            "stdout": stdout,
            "stderrs": stderrs
        }

        print(json.dumps(results))
        