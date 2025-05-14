import sys
import urllib.parse
import openai
import os
import mysql.connector
from dotenv import load_dotenv
import re

# 환경 변수 파일 로드
dotenv_path = "/home/Capstone_Design_Troy/judge/src/web/add_problem/.env"
if os.path.exists(dotenv_path):
    load_dotenv(dotenv_path)

openai.api_key = os.getenv("OPENAI_API_KEY")

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

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def get_guideline(problem_id, block_index, step):
    """가이드라인 파일에서 특정 블럭을 추출"""
    guideline_path = f"/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{problem_id}_step{step}.txt"
    if os.path.exists(guideline_path):
        code_lines = read_code_lines(guideline_path)
        if block_index < len(code_lines):
            return ''.join(code_lines[block_index])
    return "블럭 가이드라인 없음"

def generate_hint(block_code, block_number, guideline, model_answer):
    """OpenAI API를 이용하여 코드 블럭에 대한 힌트 생성"""
    prompt = f"""
    학생이 작성한 코드에 대해 전체 가이드라인과 전체 모범코드를 참고하여 학생이 작성한 코드의 문제점이 무엇인지, 

    이 코드에서 주어진 코드블럭을 제대로 작성하지 못하였습니다. 모범코드를 참고하여 코드 블럭이 수행해야 하는 역할을 설명하고, 
    주어진 코드블럭에 대해서만을 어떻게 작성해야 하는지에 대한 힌트를 알려주세요. 단, 모든 힌트가 아닌 주어진 블록에 대해서만 알려주세요.
    또한 코드를 알려주는 것은 안됩니다. 7줄 이내로 작성해주십시오.

    학생이 제출한 코드 (블록번호 : {block_number}):
    {block_code}

    블록별 가이드라인:
    {guideline}

    전체 모범 코드:
    {model_answer}
    """
    try:
        client = openai.OpenAI()
        response = client.chat.completions.create(
            model="gpt-4o-mini",
            messages=[
                {"role": "system", "content": "코드 작성 도움 시스템입니다. 코드 블럭의 역할과 작성 방법을 설명합니다."},
                {"role": "user", "content": prompt}
            ],
            max_tokens=300,
            temperature=0.7
        )
        return response.choices[0].message.content.strip()
    except Exception as e:
        return f"AI 피드백 생성 오류: {str(e)}"

def main():
    if len(sys.argv) != 5:
        print("error: 인자 부족")
        sys.exit(1)

    problem_id = sys.argv[1]
    block_index = int(sys.argv[2])
    block_code = urllib.parse.unquote(sys.argv[3])
    step = int(sys.argv[4])  # step 인자 추가

    model_answer = get_model_answer(problem_id)
    guideline = get_guideline(problem_id, block_index, step)

    with open("/tmp/python_input_debug.log", "a") as log_file:
        log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, step: {step}, guideline: {guideline}, model_answer: {model_answer}\n")

    hint = generate_hint(block_code, block_index, guideline, model_answer)
    print(f"{hint}")

if __name__ == "__main__":
    main()