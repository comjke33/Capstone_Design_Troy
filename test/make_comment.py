from openai import OpenAI
import os
import json
import mysql.connector
import sys
import markdown




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
다음은 대학교 1학년 학생이 C언어 과제에서 제출한 코드들의 문법 오류를 유형별로 정리한 내용입니다.

이 데이터를 바탕으로, 학생이 공통적으로 어려워하는 부분을 분석하고,
앞으로 어떤 개념을 더 공부해야 할지에 대해 친절하고 구체적인 종합 피드백을 작성해주세요.

개선 방향을 제시하는 방식으로 해주세요.

데이터는 5일간의 문법 오류들과 지난 5일간의 문법 오류들 2개입니다.

5줄 이내로 피드백을 작성해주세요.
1줄이 끝날 때마다 보기 쉽게 줄바꿈을 처리해주세요.
"""

# 2. 각 사용자에 대해 오류 정보 조회 및 출력
for user in active_users:
    user_id = user['user_id']
    cursor.execute("SELECT * FROM user_weakness WHERE user_id = %s", (user_id,))
    weaknesses = cursor.fetchall()

    cursor.execute("SELECT * FROM user_weakness_now WHERE user_id = %s", (user_id,))
    weaknesses_dec = cursor.fetchall()

    result_lines = []
    dec_result_lines = []
    for row in weaknesses:
        type_code = int(row['mistake_type'])
        name = mistake_names.get(type_code, "알 수 없는 오류")
        count = row['mistake_count']
        result_lines.append(f"- {name} (오류 횟수: {count})")

    for row in weaknesses_dec:
        type_code = int(row['mistake_type'])
        name = mistake_names.get(type_code, "알 수 없는 오류")
        count = row['mistake_count']
        dec_result_lines.append(f"- {name} (오류 횟수: {count})")

    if result_lines:
        mistakes = f"아래는 '{user_id}' 사용자의 5일간 주요 코드 오류 항목입니다:\n\n" + "\n".join(result_lines)
        #print(mistakes)
        #print("\n" + "="*40 + "\n")

    if dec_result_lines:
        dec_mistakes = f"아래는 '{user_id}' 사용자의 저번 5일간 주요 코드 오류 항목입니다:\n\n" + "\n".join(dec_result_lines)
        #print(mistakes)
        #print("\n" + "="*40 + "\n")
    else:
        dec_mistakes = "저번 5일간 문법 오류 데이터가 없습니다."

    #이번 제출 횟수
    cursor.execute("SELECT * FROM submit WHERE user_id = %s", (user_id,))
    submit = cursor.fetchall()

    #저번 제출 횟수수
    cursor.execute("SELECT * FROM submit_dec WHERE user_id = %s", (user_id,))
    submit_dec = cursor.fetchall()    

    if submit:
        submit_data = f"아래는 '{user_id}' 사용자의 5일간 코드 제출 횟수 입니다:\n\n" + f"제출 횟수: {submit[0]['submit_count']}"

    if submit_dec:
        submit_data_dec = f"아래는 '{user_id}' 사용자의 저번 5일간 코드 제출 횟수 입니다:\n\n" + f"제출 횟수: {submit_dec[0]['submit_count']}"
    else:
        submit_data_dec = "저번 5일간 제출 횟수 데이터가 없습니다."

    

    #프롬프트 실행
    response = client.responses.create(
        model="gpt-4o-mini-2024-07-18",
        input=prompt + "\n\n" + mistakes + "\n\n" + submit_data + "\n\n" + dec_mistakes + "\n\n" + submit_data_dec
    )
    comment = response.output_text
    print(comment)

    #print(response.output_text)

    # comment 테이블에 데이터 삽입 또는 업데이트
    cursor.execute("SELECT 1 FROM comment WHERE user_id = %s", (user_id,))
    if cursor.fetchone():
        # 이미 존재하면 업데이트
        cursor.execute(
            "UPDATE comment SET comment = %s WHERE user_id = %s",
            (comment, user_id)
        )
    else:
        # 존재하지 않으면 새로 삽입
        cursor.execute(
            "INSERT INTO comment (user_id, comment) VALUES (%s, %s)",
            (user_id, comment)
        )

    #user_weakness_now -> user_weakness_dec
    # user_weakness_now 테이블의 모든 데이터 가져오기
    cursor.execute("SELECT user_id, mistake_type, mistake_count FROM user_weakness_now where user_id = %s", (user_id,))
    rows = cursor.fetchall()
    print(user_id)

    for row in rows:
        #user_id = row['user_id']  # 딕셔너리 키로 접근
        mistake_type = row['mistake_type']  # 딕셔너리 키로 접근
        mistake_count = row['mistake_count']  # 딕셔너리 키로 접근
        #mistake_count = int(mistake_count)  # mistake_count를 정수로 변환

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
    cursor.execute("SELECT user_id, mistake_type, mistake_count FROM user_weakness where user_id = %s", (user_id,))
    rows = cursor.fetchall()

    for row in rows:
        #user_id = row['user_id']  # 딕셔너리 키로 접근
        mistake_type = row['mistake_type']  # 딕셔너리 키로 접근
        mistake_count = row['mistake_count']  # 딕셔너리 키로 접근

        # user_weakness_now 테이블에 같은 데이터가 있는지 확인
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
    cursor.execute("SELECT user_id, mistake_type FROM user_weakness where user_id = %s", (user_id,))
    rows = cursor.fetchall()

    for row in rows:
        #user_id = row['user_id']  # 딕셔너리 키로 접근
        mistake_type = row['mistake_type']  # 딕셔너리 키로 접근
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


    #submit -> submit_dec
    # submit 테이블에서 해당 user_id 데이터 조회
    cursor.execute(
        "SELECT user_id, submit_count FROM submit WHERE user_id = %s",
        (user_id,)
    )
    row = cursor.fetchone()

    # 결과가 있을 경우 submit_dec에 삽입 또는 업데이트
    if row:
        cursor.execute(
            """
            INSERT INTO submit_dec (user_id, submit_count)
            VALUES (%s, %s)
            ON DUPLICATE KEY UPDATE submit_count = VALUES(submit_count)
            """,
            (row['user_id'], row['submit_count'])  # 딕셔너리 키로 접근
        )

    #submit 제출횟수 0으로 초기화
    submit_count = 0
    cursor.execute(
        "UPDATE submit SET submit_count = %s WHERE user_id = %s",
        (submit_count, user_id)
    )

    




conn.commit()

# 정리
cursor.close()
conn.close()


# print(response.output_text)