from openai import OpenAI
import os
import json
#import mysql.connector
import sys


if len(sys.argv) > 1:
    mistakes = sys.argv[1]

mistakes = """
변수 선언 오류 6, 괄호 닫힘 오류 5, 비교 연산자 오류 5, 함수 선언 누락 6, 포인터 오류 6, 배열 인덱스 오류 5, 입출력 형식 지정자 오류 5, 표현식 누락 4
"""
# OpenAI API 클라이언트 세팅
# api_key_ = os.getenv("OPENAI_API_KEY")
# client = OpenAI(api_key=api_key_)



prompt = """
대학교 1학년 C언어 OJ에서 학생이 제출한 코드들의 문법 오류를 정리한 내용을 보고 종합적인 코멘트를 작성해주세요.


"""

response = client.responses.create(
    model="gpt-4o-mini-2024-07-18",
    input=prompt + "\n\n" + mistakes
)

print(response.output_text)