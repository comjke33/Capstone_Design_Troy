import subprocess
import sys
import re
import json

BASE_URL = "http://192.168.0.85/reference.php"

CONCEPT_LINKS = {
    # 변수 선언
    r"use of undeclared identifier|unused variable|'[^']+' undeclared|mixing declarations and code is incompatible": {
        "개념": "변수 선언",
        "링크": f"{BASE_URL}#변수-선언"
    },
    r"variable '.*' is used uninitialized": {
        "개념": "변수 초기화 누락",
        "링크": f"{BASE_URL}#변수-선언"
    },

    # 함수 선언 누락
    r"implicit declaration of function|call to undeclared function": {
        "개념": "함수 선언 누락",
        "링크": f"{BASE_URL}#함수-선언-누락"
    },

    # 함수 반환
    r"control reaches end of non-void function|non-void function.*should return|void function.*return|return with a value|should not return a value|returning '.*' from a function with incompatible return type|initializing '.*?' with an expression of incompatible type 'void'": {
        "개념": "함수 반환",
        "링크": f"{BASE_URL}#함수-반환"
    },

    # 세미콜론 누락
    r"expected ';'": {
        "개념": "세미콜론 누락",
        "링크": f"{BASE_URL}#세미콜론-누락"
    },

    # 괄호 오류 (identifier 분리 고려)
    r"expected '\)'|expected '\]'|expected '\}'|expected '\(' after": {
        "개념": "괄호 닫힘 오류",
        "링크": f"{BASE_URL}#괄호-닫힘-오류"
    },

    # 표현식 누락
    r"expected expression.*": {
        "개념": "표현식 누락",
        "링크": f"{BASE_URL}#표현식-누락"
    },

    # 포인터 오류
    r"incompatible pointer type|incompatible integer to pointer conversion|passing .* to parameter of incompatible type": {
        "개념": "포인터",
        "링크": f"{BASE_URL}#포인터"
    },
    r"dereferencing a null pointer": {
        "개념": "포인터",
        "링크": f"{BASE_URL}#포인터"
    },

    # 배열 접근 오류
    r"array index .* is past the end|subscripted value is not an array": {
        "개념": "배열 접근 오류",
        "링크": f"{BASE_URL}#배열-접근-오류"
    },
    

    # 입출력 형식
    r"format specifies type .* but the argument has type": {
        "개념": "입출력 형식 지정자",
        "링크": f"{BASE_URL}#입출력-형식-지정자"
    },

    # 연산자 오류
    r"invalid operands to binary expression|comparison between|invalid operands to logical expression": {
        "개념": "연산자 사용 오류",
        "링크": f"{BASE_URL}#연산자-사용-오류"
    },

    # 비교 연산자
    r"using the result of an assignment as a condition without parentheses": {
        "개념": "비교 연산자",
        "링크": f"{BASE_URL}#비교-연산자"
    },

    # 리터럴 오류
    r"invalid suffix|invalid digit .* in decimal constant": {
        "개념": "정수/실수 리터럴 오류",
        "링크": f"{BASE_URL}#정수실수-리터럴-오류"
    },

    # 상수 수정
    r"read-only variable is not assignable|assignment of read-only variable": {
        "개념": "상수 수정 오류",
        "링크": f"{BASE_URL}#상수-수정-오류"
    },

    # 함수 중복
    r"redefinition of": {
        "개념": "함수 정의 중복",
        "링크": f"{BASE_URL}#함수-정의-중복"
    },

    # 함수 인자
    r"incompatible type for argument|too few arguments|too many arguments to function call|incompatible .* to .* conversion": {
        "개념": "함수 인자 순서 오류",
        "링크": f"{BASE_URL}#함수-인자-순서-오류"
    },

    # 형 변환
    r"implicit conversion from .* to .*|conversion from .* to .* changes value": {
        "개념": "형 변환 오류",
        "링크": f"{BASE_URL}#형-변환-오류"
    },

    # 전처리기 오류
    r"#include expects .*|file not found|include.*error": {
        "개념": "전처리기 오류",
        "링크": f"{BASE_URL}#전처리기-오류"
    },
    
    # 표준 함수 오용
    r"implicitly declaring library function|implicit declaration of library function": {
    "개념": "표준 함수 오용",
    "링크": f"{BASE_URL}#표준-함수-오용"
    }
}

def map_to_concepts(errors: str):
    enriched = []
    current_block = []

    for line in errors.splitlines():
        if re.match(r"^.*(error|warning|AddressSanitizer):.*", line):
            if current_block:
                enriched.append("\n".join(current_block))
                current_block = []
            current_block.append(line)
        elif line.strip():
            current_block.append(line)

    if current_block:
        enriched.append("\n".join(current_block))

    results = []
    for block in enriched:
        # AddressSanitizer 런타임 오류 우선 처리
        if "AddressSanitizer" in block:
            results.append({
                "concepts": "런타임 오류",
                "block": block,
                "link": f"{BASE_URL}#런타임-오류"
            })
            continue  # 다른 매칭 안 함

        matched = False
        for pattern, info in CONCEPT_LINKS.items():
            if re.search(pattern, block):
                results.append({
                    "concepts": info["개념"],
                    "block": block,
                    "link": info["링크"]
                })
                matched = True
                break   # 첫 번째 매칭만 사용

        if not matched:
            results.append({
                "concepts": "알 수 없는 오류",
                "block": block,
                "link": f"{BASE_URL}#알-수-없는-오류"
            })


    return results

if __name__ == "__main__":
    if len(sys.argv) == 2:
        compile_result = sys.argv[1]
        links = map_to_concepts(compile_result)

        print(json.dumps(links, ensure_ascii=False))
        
