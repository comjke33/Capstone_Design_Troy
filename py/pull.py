import subprocess
import sys
import json

def run_compile_process(code):
    result = subprocess.run(
        ["python3", "compile_process.py", code],
        capture_output=True, text=True
    )
    return json.loads(result.stdout)

def run_classify_error(message):
    result = subprocess.run(
        ["python3", "classify_error.py", message],
        capture_output=True, text=True
    )
    return result.stdout.strip()

def run_matching_hyperlink(message):
    result = subprocess.run(
        ["python3", "matching_hyperlink.py", message],
        capture_output=True, text=True
    )
    return json.loads(result.stdout)

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python3 run_full_analysis.py '<C code string>'")
        sys.exit(1)

    code = sys.argv[1]
    compile_data = run_compile_process(code)
    final_output = {
        "compile_result": compile_data,
        "classified": [],
        "linked": []
    }

    for err in compile_data.get("stderrs", []):
        msg = err.get("message", "")
        class_id = run_classify_error(msg)
        link_result = run_matching_hyperlink(msg)
        final_output["classified"].append({
            "message": msg,
            "concept_id": class_id
        })
        final_output["linked"].append({
            "message": msg,
            "matches": link_result
        })

    print(json.dumps(final_output, ensure_ascii=False, indent=2))
