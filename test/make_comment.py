from openai import OpenAI
import os
import json
#import mysql.connector
import sys


if len(sys.argv) > 1:
    mistakes = sys.argv[1]

print(mistakes)

# OpenAI API 클라이언트 세팅
# api_key_ = os.getenv("OPENAI_API_KEY")
# client = OpenAI(api_key=api_key_)
mistakes = """
변수 선언 오류 6, 괄호 닫힘 오류 5, 비교 연산자 오류 5, 함수 선언 누락 6, 포인터 오류 6, 배열 인덱스 오류 5, 입출력 형식 지정자 오류 5, 표현식 누락 4
"""




prompt = """
다음은 대학교 1학년 학생들이 C언어 과제에서 제출한 코드들의 문법 오류를 유형별로 정리한 내용입니다.

이 데이터를 바탕으로, 학생들이 공통적으로 어려워하는 부분을 분석하고,
앞으로 어떤 개념을 더 공부해야 할지에 대해 친절하고 구체적인 종합 피드백을 작성해주세요.

개선 방향을 제시하는 방식으로 해주세요.
"""

# response = client.responses.create(
#     model="gpt-4o-mini-2024-07-18",
#     input=prompt + "\n\n" + mistakes
# )

# print(response.output_text)