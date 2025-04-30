import subprocess
import re

BASE_URL = "https://github.com/comjke33/Capstone_Design_Troy/blob/main/ref.md"

CONCEPT_LINKS = {
    # 변수 선언 관련
    r"use of undeclared identifier|unused variable": {
        "개념": "변수 선언",
        "링크": f"{BASE_URL}#변수-선언"
    },
    # 함수 선언 누락
    r"implicit declaration of function|call to undeclared function": {
        "개념": "함수 선언 누락",
        "링크": f"{BASE_URL}#함수-선언-누락"
    },
    # 함수 반환
    r"control reaches end of non-void function|non-void function.*should return|void function.*return|return with a value": {
        "개념": "함수 반환",
        "링크": f"{BASE_URL}#함수반환"
    },
    # 세미콜론 누락
    r"expected ';'": {
        "개념": "세미콜론 누락",
        "링크": f"{BASE_URL}#세미콜론-누락"
    },
    # 괄호 오류
    r"expected '\)'|expected '\]'|expected '\}'": {
        "개념": "괄호 닫힘 오류",
        "링크": f"{BASE_URL}#괄호-닫힘-오류"
    },
    # 표현식 누락
    r"expected expression": {
        "개념": "표현식 누락",
        "링크": f"{BASE_URL}#표현식-누락"
    },
    # 포인터 오류
    r"incompatible pointer type|incompatible integer to pointer conversion": {
        "개념": "포인터",
        "링크": f"{BASE_URL}#포인터"
    },
    # 배열 인덱싱 관련
    r"array index .* is past the end": {
        "개념": "배열 인덱스 초과",
        "링크": f"{BASE_URL}#배열-인덱스-초과"
    },
    r"subscripted value is not an array": {
        "개념": "배열 인덱싱 오류",
        "링크": f"{BASE_URL}#배열-인덱싱-오류"
    },
    # 입출력 형식
    r"format specifies type .* but the argument has type": {
        "개념": "입출력 형식 지정자",
        "링크": f"{BASE_URL}#입출력-형식-지정자"
    },
    # 연산자 오류
    r"invalid operands to binary expression|comparison between": {
        "개념": "연산자 사용 오류",
        "링크": f"{BASE_URL}#연산자-사용-오류"
    },
    # 비교 연산자
    r"invalid operands to binary expression .* == .*|assignment makes integer": {
        "개념": "비교 연산자",
        "링크": f"{BASE_URL}#비교-연산자"
    },
    # 정수/실수 리터럴 오류
    r"invalid suffix": {
        "개념": "정수/실수 리터럴 오류",
        "링크": f"{BASE_URL}#정수실수-리터럴-오류"
    },
    # 함수 중복
    r"redefinition of": {
        "개념": "함수 정의 중복",
        "링크": f"{BASE_URL}#함수-정의-중복"
    },
    # 함수 인자 오류
    r"incompatible type for argument|too few arguments": {
        "개념": "함수 인자 순서 오류",
        "링크": f"{BASE_URL}#함수-인자-순서-오류"
    }
}

def map_to_concepts(errors: str):
    enriched = []
    current_block = []

    for line in errors.splitlines():
        if re.match(r"^.*(error|warning):.*", line):
            if current_block:
                enriched.append("\n".join(current_block))
                current_block = []
            current_block.append(line)
        elif line.strip():
            current_block.append(line)

    if current_block:
        enriched.append("\n".join(current_block))

    result = []
    for block in enriched:
        matched = False
        for pattern, info in CONCEPT_LINKS.items():
            if re.search(pattern, block):
                result.append(f"❗ [{info['개념']}] 관련 오류 감지\n{block}\n➡ 참고 링크: {info['링크']}")
                matched = True
        if not matched:
            result.append(f"⚠️ 알 수 없는 오류\n{block}")
    return "\n\n".join(result)


def matching_hyperlink(compile_result):

        links = map_to_concepts(errors)

        return links