import subprocess
import tempfile
import re
import json

BASE_URL = "https://github.com/comjke33/Capstone_Design_Troy/blob/main/ref.md"

CONCEPT_LINKS = {
    r"use of undeclared identifier|unused variable": {
        "개념": "변수 선언",
        "링크": f"{BASE_URL}#변수-선언"
    },
    r"implicit declaration of function|call to undeclared function": {
        "개념": "함수 선언 누락",
        "링크": f"{BASE_URL}#함수-선언-누락"
    },
    r"control reaches end of non-void function|non-void function.*should return|void function.*return|return with a value": {
        "개념": "함수 반환",
        "링크": f"{BASE_URL}#함수반환"
    },
    r"expected ';'": {
        "개념": "세미콜론 누락",
        "링크": f"{BASE_URL}#세미콜론-누락"
    },
    r"expected '\)'|expected '\]'|expected '\}'": {
        "개념": "괄호 닫힘 오류",
        "링크": f"{BASE_URL}#괄호-닫힘-오류"
    },
    r"expected expression": {
        "개념": "표현식 누락",
        "링크": f"{BASE_URL}#표현식-누락"
    },
    r"incompatible pointer type|incompatible integer to pointer conversion": {
        "개념": "포인터",
        "링크": f"{BASE_URL}#포인터"
    },
    r"array index .* is past the end": {
        "개념": "배열 인덱스 초과",
        "링크": f"{BASE_URL}#배열-인덱스-초과"
    },
    r"subscripted value is not an array": {
        "개념": "배열 인덱싱 오류",
        "링크": f"{BASE_URL}#배열-인덱싱-오류"
    },
    r"format specifies type .* but the argument has type": {
        "개념": "입출력 형식 지정자",
        "링크": f"{BASE_URL}#입출력-형식-지정자"
    },
    r"invalid operands to binary expression|comparison between": {
        "개념": "연산자 사용 오류",
        "링크": f"{BASE_URL}#연산자-사용-오류"
    },
    r"invalid operands to binary expression .* == .*|assignment makes integer": {
        "개념": "비교 연산자",
        "링크": f"{BASE_URL}#비교-연산자"
    },
    r"invalid suffix": {
        "개념": "정수/실수 리터럴 오류",
        "링크": f"{BASE_URL}#정수실수-리터럴-오류"
    },
    r"redefinition of": {
        "개념": "함수 정의 중복",
        "링크": f"{BASE_URL}#함수-정의-중복"
    },
    r"incompatible type for argument|too few arguments": {
        "개념": "함수 인자 순서 오류",
        "링크": f"{BASE_URL}#함수-인자-순서-오류"
    }
}

def analyze_with_clang(code: str):
    with tempfile.NamedTemporaryFile(suffix=".c", delete=False, mode="w") as tmp:
        tmp.write(code)
        tmp_path = tmp.name

    result = subprocess.run(
        ["clang", "-fsyntax-only", tmp_path],
        capture_output=True,
        text=True
    )

    return result.stderr.strip()

def extract_errors_to_json(errors: str, output_path="./test.json"):
    lines = errors.splitlines()
    results = []
    i = 0
    while i < len(lines):
        line = lines[i]
        if "error:" in line or "warning:" in line:
            # 에러 메시지 추출
            msg = line.split("error:")[-1].strip() if "error:" in line else line.split("warning:")[-1].strip()

            # 코드 줄과 위치 줄 추출
            code_line = ""
            if i + 1 < len(lines):
                code_line_candidate = lines[i + 1].strip()
                if code_line_candidate and not code_line_candidate.startswith("^"):
                    code_line = code_line_candidate

            # 개념과 링크 매핑
            link = None
            for pattern, info in CONCEPT_LINKS.items():
                if re.search(pattern, msg, re.IGNORECASE):
                    link = info["링크"]
                    break

            if link:
                results.append({
                    "link": link,
                    "error": msg,
                    "code": code_line
                })

        i += 1

    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

    print(f"✅ 분석 완료. {output_path}에 저장됨.")

if __name__ == "__main__":
    with open("./code.txt", "r", encoding="utf-8") as f:
        code = f.read()

    errors = analyze_with_clang(code)
    if errors:
        print("❌ 문법 오류가 있습니다.\n")
        extract_errors_to_json(errors)
    else:
        print("✔️ 문법 오류 없음.")