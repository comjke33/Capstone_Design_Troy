import sys
import urllib.parse
import openai
import os
import mysql.connector
from dotenv import load_dotenv

# 환경 변수 파일 로드
dotenv_path = "/home/Capstone_Design_Troy/judge/src/web/add_problem/.env"
if os.path.exists(dotenv_path):
    load_dotenv(dotenv_path)

openai.api_key = os.getenv("OPENAI_API_KEY")

# MySQL 연결 설정
conn = mysql.connector.connect(
    host="localhost",
    user="hustoj",
    password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
    database="jol"
)

def get_model_answer(problem_id):
    """데이터베이스에서 모범 코드 가져오기"""
    try:
        cursor = conn.cursor()
        query = "SELECT exemplary_code FROM exemplary WHERE problem_id = %s"
        cursor.execute(query, (problem_id,))
        result = cursor.fetchone()
        if result:
            return result[0]
        else:
            return "모범 코드 없음"
    except Exception as e:
        return f"DB 오류: {str(e)}"
    finally:
        cursor.close()

def get_guideline(problem_id):
    """가이드라인 파일 가져오기"""
    guideline_path = f"/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{problem_id}_step1.txt"
    try:
        with open(guideline_path, 'r') as file:
            return file.read()
    except FileNotFoundError:
        return "가이드라인 파일 없음"

def generate_hint(block_code, block_number, guideline, model_answer):
    """OpenAI API를 이용하여 코드 블럭에 대한 힌트 생성"""
    prompt = f"""
    다음은 C 코드의 일부입니다.

    코드 블럭 (번호 {block_number}):
    {block_code}

    가이드라인:
    {guideline}

    모범 코드:
    {model_answer}

    이 코드에서 주어진 코드블럭을 작성하지 못하였습니다. 모범코드를 참고하여 코드 블럭이 수행해야 하는 역할을 설명하고, 
    주어진 코드블럭을 어떻게 작성해야 하는지에 대한 힌트를 알려주세요. 단, 코드를 알려주는 것은 안됩니다. 7줄 이내로 작성해주십시오.
    """
    try:
        client = openai.OpenAI()
        response = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {
                    "role": "system",
                    "content": "코드 작성 도움 시스템입니다. 코드 블럭의 역할과 작성 방법을 설명합니다."
                },
                {
                    "role": "user",
                    "content": prompt
                }
            ],
            max_tokens=300,
            temperature=0.7
        )
        return response.choices[0].message.content.strip()
    except Exception as e:
        return f"AI 피드백 생성 오류: {str(e)}"

def main():
    if len(sys.argv) != 4:
        print("error: 인자 부족")
        sys.exit(1)

    problem_id = sys.argv[1]
    block_index = sys.argv[2]
    block_code = urllib.parse.unquote(sys.argv[3])

    # 모범 코드 가져오기
    model_answer = get_model_answer(problem_id)

    # 가이드라인 가져오기
    guideline = get_guideline(problem_id)

    # 디버그 로그 추가
    with open("/tmp/python_input_debug.log", "a") as log_file:
        log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, guideline: {guideline}, model_answer: {model_answer}\n")

    # AI 피드백 생성
    hint = generate_hint(block_code, block_index, guideline, model_answer)

    # 피드백 출력
    print(f"{hint}")

if __name__ == "__main__":
    main()