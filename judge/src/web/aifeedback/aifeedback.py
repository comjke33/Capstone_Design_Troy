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

def is_tag_line(line):
    """태그 줄인지 판별"""
    return bool(re.match(r"\s*\[.*_(start|end)\(\d+\)\]\s*", line))

def is_start_tag(line):
    """블럭 시작 태그인지 판별"""
    return "start" in line

def is_end_tag(line):
    """블럭 종료 태그인지 판별"""
    return "end" in line

def extract_block_number(line):
    """블럭 번호 추출"""
    match = re.search(r"\((\d+)\)", line)
    return int(match.group(1)) if match else -1

def get_blocks_from_file(file_path):
    """파일에서 블럭 단위로 추출"""
    if not os.path.exists(file_path):
        return {}

    with open(file_path, 'r') as f:
        lines = f.readlines()

    blocks = {}
    current_block = []
    block_number = None
    inside_block = False

    for line in lines:
        if is_tag_line(line):
            if is_start_tag(line):
                if current_block and block_number is not None:
                    blocks[block_number] = "".join(current_block).strip()
                block_number = extract_block_number(line)
                current_block = [line]
                inside_block = True
            elif is_end_tag(line):
                current_block.append(line)
                if block_number is not None:
                    blocks[block_number] = "".join(current_block).strip()
                current_block = []
                inside_block = False
        elif inside_block:
            current_block.append(line)

    # 마지막 블럭 처리
    if current_block and block_number is not None:
        blocks[block_number] = "".join(current_block).strip()

    return blocks

def get_guideline(problem_id, block_index):
    """가이드라인 파일에서 특정 블럭을 추출 (step1 고정)"""
    guideline_path = f"/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{problem_id}_step1.txt"
    blocks = get_blocks_from_file(guideline_path)

    return blocks.get(block_index, "블럭 가이드라인 없음")

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
    block_index = int(sys.argv[2])
    block_code = urllib.parse.unquote(sys.argv[3])

    # 모범 코드 가져오기
    model_answer = get_model_answer(problem_id)

    # 블럭별 가이드라인 가져오기 (step1 고정)
    guideline = get_guideline(problem_id, block_index)

    # 디버그 로그 추가
    with open("/tmp/python_input_debug.log", "a") as log_file:
        log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, guideline: {guideline}, model_answer: {model_answer}\n")

    # AI 피드백 생성
    hint = generate_hint(block_code, block_index, guideline, model_answer)

    # 피드백 출력
    print(f"{hint}")

if __name__ == "__main__":
    main()