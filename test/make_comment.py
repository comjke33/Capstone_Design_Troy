from openai import OpenAI
import os
import json
import mysql.connector
import sys




# MySQL 연결 설정
conn = mysql.connector.connect(
    host="localhost",
    user="hustoj",
    password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
    database="jol"
)

# OpenAI API 클라이언트 세팅
api_key_ = os.getenv("OPENAI_API_KEY")
client = OpenAI(api_key=api_key_)

cursor = conn.cursor(dictionary=True)

# 1. 제출 횟수가 15 이상인 사용자 조회
cursor.execute("SELECT user_id FROM submit WHERE submit_count >= 15")
active_users = cursor.fetchall()

# mistake_type 이름 매핑
mistake_names = {
    1: "변수 선언 오류",
    2: "함수 선언 누락",
    3: "함수 반환 오류",
    4: "포인터 오류",
    5: "배열 인덱스 오류",
    6: "입출력 형식 지정자 오류",
    7: "연산자 사용 오류",
    8: "정수/실수 리터럴 오류",
    9: "표현식 누락",
    10: "형 변환 오류",
    11: "세미콜론 누락",
    12: "괄호 닫힘 오류",
    13: "함수 인자 개수/타입 오류",
    14: "함수 정의 중복",
    15: "비교 연산자 오류",
    -1: "기타 오류"
}

# OpenAI API 클라이언트 세팅
# api_key_ = os.getenv("OPENAI_API_KEY")
# client = OpenAI(api_key=api_key_)




prompt = """
다음은 대학교 1학년 학생들이 C언어 과제에서 제출한 코드들의 문법 오류를 유형별로 정리한 내용입니다.

이 데이터를 바탕으로, 학생들이 공통적으로 어려워하는 부분을 분석하고,
앞으로 어떤 개념을 더 공부해야 할지에 대해 친절하고 구체적인 종합 피드백을 작성해주세요.

개선 방향을 제시하는 방식으로 해주세요.
"""

# 2. 각 사용자에 대해 오류 정보 조회 및 출력
for user in active_users:
    user_id = user['user_id']
    cursor.execute("SELECT * FROM user_weakness WHERE user_id = %s", (user_id,))
    weaknesses = cursor.fetchall()

    result_lines = []
    for row in weaknesses:
        type_code = int(row['mistake_type'])
        name = mistake_names.get(type_code, "알 수 없는 오류")
        count = row['mistake_count']
        result_lines.append(f"- {name} (오류 횟수: {count})")

    if result_lines:
        mistakes = f"아래는 '{user_id}' 사용자의 지난 5일간 주요 코드 오류 항목입니다:\n\n" + "\n".join(result_lines)
        #print(mistakes)
        #print("\n" + "="*40 + "\n")

    #프롬프트 실행
    response = client.responses.create(
        model="gpt-4o-mini-2024-07-18",
        input=prompt + "\n\n" + mistakes
    )

    print(response.output_text)

    # comment 테이블에 데이터 삽입 또는 업데이트
    cursor.execute("SELECT 1 FROM comment WHERE user_id = %s", (user_id,))
    if cursor.fetchone():
        # 이미 존재하면 업데이트
        cursor.execute(
            "UPDATE comment SET comment = %s WHERE user_id = %s",
            (response.output_text, user_id)
        )
    else:
        # 존재하지 않으면 새로 삽입
        cursor.execute(
            "INSERT INTO comment (user_id, comment) VALUES (%s, %s)",
            (user_id, response.output_text)
        )

    #user_weakness_now -> user_weakness_dec
    # user_weakness_now 테이블의 모든 데이터 가져오기
    cursor.execute("SELECT user_id, mistake_type, mistake_count FROM user_weakness_now")
    rows = cursor.fetchall()

    for row in rows:
        print(row)
        user_id, mistake_type, mistake_count = row
        print(mistake_count)
        mistake_count = int(mistake_count)
        print(mistake_count)

        # user_weakness_dec 테이블에 같은 데이터가 있는지 확인
        cursor.execute(
            "SELECT 1 FROM user_weakness_dec WHERE user_id = %s AND mistake_type = %s",
            (user_id, mistake_type)
        )

        if cursor.fetchone():
            # 이미 존재하면 업데이트
            cursor.execute(
                "UPDATE user_weakness_dec SET mistake_count = %s WHERE user_id = %s AND mistake_type = %s",
                (mistake_count, user_id, mistake_type)
            )
        else:
            # 존재하지 않으면 삽입
            cursor.execute(
                "INSERT INTO user_weakness_dec (user_id, mistake_type, mistake_count) VALUES (%s, %s, %s)",
                (user_id, mistake_type, mistake_count)
            )

    #user_weakness -> user_weakness_now
    # user_weakness 테이블의 모든 데이터 가져오기
    cursor.execute("SELECT user_id, mistake_type, mistake_count FROM user_weakness")
    rows = cursor.fetchall()

    for row in rows:
        user_id, mistake_type, mistake_count = row
        mistake_count = int(mistake_count)

        # user_weakness_dec 테이블에 같은 데이터가 있는지 확인
        cursor.execute(
            "SELECT 1 FROM user_weakness_now WHERE user_id = %s AND mistake_type = %s",
            (user_id, mistake_type)
        )

        if cursor.fetchone():
            # 이미 존재하면 업데이트
            cursor.execute(
                "UPDATE user_weakness_now SET mistake_count = %s WHERE user_id = %s AND mistake_type = %s",
                (mistake_count, user_id, mistake_type)
            )
        else:
            # 존재하지 않으면 삽입
            cursor.execute(
                "INSERT INTO user_weakness_now (user_id, mistake_type, mistake_count) VALUES (%s, %s, %s)",
                (user_id, mistake_type, mistake_count)
            )

    #user_weakness count값들 0으로 초기화
    # user_weakness 테이블에서 user_id, mistake_type만 가져오기
    cursor.execute("SELECT user_id, mistake_type FROM user_weakness")
    rows = cursor.fetchall()

    for row in rows:
        user_id, mistake_type = row
        mistake_count = 0  # 항상 0으로 설정

        # user_weakness 테이블에 같은 데이터가 있는지 확인
        cursor.execute(
            "SELECT 1 FROM user_weakness WHERE user_id = %s AND mistake_type = %s",
            (user_id, mistake_type)
        )

        if cursor.fetchone():
            # 이미 존재하면 mistake_count를 0으로 업데이트
            cursor.execute(
                "UPDATE user_weakness SET mistake_count = %s WHERE user_id = %s AND mistake_type = %s",
                (mistake_count, user_id, mistake_type)
            )
        else:
            # 존재하지 않으면 삽입 (mistake_count는 0)
            cursor.execute(
                "INSERT INTO user_weakness (user_id, mistake_type, mistake_count) VALUES (%s, %s, %s)",
                (user_id, mistake_type, mistake_count)
            )

    #submit 제출횟수 0으로 초기화
    cursor.execute(
        "UPDATE submit SET submit_count = 0 WHERE user_id = %s",
        (user_id)
    )




conn.commit()

# 정리
cursor.close()
conn.close()


# print(response.output_text)