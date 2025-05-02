import re
import sys

ERROR_PATTERN_MAP = {
    r"use of undeclared identifier|unused variable": 1,  # 변수 선언
    r"control reaches end of non-void function|non-void function.*should return|void function.*return|return with a value": 2,  # 함수 반환
    r"incompatible pointer type|incompatible integer to pointer conversion": 3,  # 포인터
    r"array index .* is past the end|subscripted value is not an array": 4,  # 배열 인덱스 오류
    r"format specifies type .* but the argument has type": 5,  # 입출력 형식 지정자
    r"invalid operands to binary expression|comparison between": 6,  # 연산자 사용 오류
    r"invalid suffix": 7,  # 정수/실수 리터럴 오류
    r"expected expression": 8  # 표현식 누락
}

def classify_error(error_msg):
    for pattern, concept in ERROR_PATTERN_MAP.items():
        if re.search(pattern, error_msg):
            return concept
    return "-1"

if __name__ == "__main__":
    if len(sys.argv) == 2:
        error_msg = sys.argv[1]
        concept = classify_error(error_msg)
        print(concept)
