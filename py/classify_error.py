import re
import sys

ERROR_PATTERN_MAP = {
    # 1. 변수 선언 오류
    r"use of undeclared identifier|undeclared variable|is uninitialized when used here": 1,

    # 2. 함수 선언 누락
    r"implicit declaration of function|call to undeclared function": 2,

    # 3. 함수 반환 오류
    r"control reaches end of non-void function|non-void function.*should return|void function.*return|return with a value|should not return a value|returning .* from a function with incompatible return type|return type of 'main' is not 'int'": 3,

    # 4. 포인터 오류
    r"incompatible pointer type|incompatible integer to pointer conversion|assignment makes pointer from integer|passing .* to parameter of incompatible type|called object type .* is not a function or function pointer": 4,

    # 5. 배열 인덱스 오류
    r"array index .* is past the end|subscripted value is not an array": 5,

    # 6. 입출력 형식 지정자 오류
    r"format specifies type .* but the argument has type|format string is not a string literal|more '%' conversions than data arguments": 6,

    # 7. 연산자 사용 오류
    r"invalid operands to binary expression|comparison between|invalid operands to .* operator|expression is not assignable|variable '.*' is incremented both in the loop header and in the loop body": 7,

    # 8. 정수/실수 리터럴 오류
    r"invalid suffix|invalid digit .* in decimal constant|literal is too large": 8,

    # 9. 표현식 누락
    r"expected expression|expected primary-expression|expected statement|'break' statement not in loop or switch statement": 9,

    # 10. 형 변환 오류
    r"implicit conversion from .* to .*|cannot initialize a variable of type .* with an .* of type .*|assignment makes .* without a cast": 10,

    # 11. 세미콜론 누락
    r"expected ';'": 11,

    # 12. 괄호 닫힘 오류
    r"expected '\)'|expected '\]'|expected '\}'|expected '\(' after|expected identifier|expected parameter declarator|add explicit braces to avoid dangling else": 12,

    # 13. 함수 인자 개수/타입 오류
    r"too (few|many) arguments to function call|passing .* to parameter of incompatible type|incompatible type for argument": 13,

    # 14. 함수 정의 중복
    r"redefinition of .*": 14,

    # 15. 비교 연산자 오류
    r"invalid operands to binary expression .*==.*|assignment makes integer from pointer|using the result of an assignment as a condition without parentheses|equality comparison result unused": 15,
    
    # 16. 표준 함수 오용
    r"implicitly declaring library function": 16,

    # 17. 전처리기 오류
    r"header file not found|#include expects": 17,

    # 18. 런타임 오류
    r"AddressSanitizer:.*": 18,
    
    
    
    


    
    

   
    

   
    
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

