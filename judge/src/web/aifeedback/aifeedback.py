import sys
import urllib.parse
import openai
import os
import mysql.connector
from dotenv import load_dotenv
import re
import json
import uuid

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

# 블럭 처리 관련 함수
def is_tag_line(line):
    """태그 줄인지 판별"""
    return bool(re.match(r"\s*\[.*_(start|end)\(\d+\)\]\s*", line))

def is_start_tag(line):
    """블럭 시작 태그인지 판별"""
    return "start" in line

def is_include_line(line):
    """헤더 선언(#include)인지 판별"""
    return line.strip().startswith("#")

def is_single_brace(line):
    """단독 중괄호인지 판별"""
    return line.strip() == "}"

def filter_code_lines(code_lines):
    """태그 줄 제거된 실제 코드 줄만 반환"""
    return [line for line in code_lines if not is_tag_line(line)]

def clean_block(block):
    """블럭에서 태그를 제거하여 반환"""
    return [line for line in block if not is_tag_line(line)]

def get_blocks(code_lines):
    """코드에서 블럭 단위로 추출"""
    all_blocks = []
    all_idx = 0
    blocks = []
    blocks_idx = 0
    current_block = []
    includes = []
    closing_braces = []
    inside_block = False
    block_indices = []

    for line in code_lines:
        if is_include_line(line):
            includes.append(line)
            all_blocks.append(includes)
            all_idx += 1
            includes = []
            continue

        if is_single_brace(line):
            closing_braces.append(line)
            all_blocks.append(closing_braces)
            all_idx += 1
            closing_braces = []
            continue

        if is_start_tag(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            current_block.append(line)
            inside_block = True


        elif is_tag_line(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            inside_block = False


        if inside_block or not is_tag_line(line):
            if line.strip() != "":
                current_block.append(line)

    return includes, blocks, closing_braces, all_blocks, block_indices

def get_guideline(problem_id, block_index, step):
    """가이드라인 파일에서 특정 블럭을 추출 (step 가변)"""
    guideline_path = f"/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{problem_id}_step{step}.txt"
    if os.path.exists(guideline_path):
        code_lines = read_code_lines(guideline_path)
        _, blocks, _, _, _ = get_blocks(code_lines)
        if block_index < len(blocks):
            return ''.join(clean_block(blocks[block_index]))
    return "블럭 가이드라인 없음"


def get_model_block(problem_id, block_index, step):
    """태그된 모범 코드에서 특정 블럭 추출"""
    model_path = f"/home/Capstone_Design_Troy/judge/src/web/tagged_code/{problem_id}_step{step}.txt"
    if os.path.exists(model_path):
        code_lines = read_code_lines(model_path)
        _, blocks, _, _, _ = get_blocks(code_lines)
        if block_index < len(blocks):
            return ''.join(clean_block(blocks[block_index]))
    return "모범 코드 블럭 없음"

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def generate_hint(block_code, block_number, guideline, model_block, model_answer):
    """OpenAI API를 이용하여 코드 블럭에 대한 힌트 생성"""
    prompt = f"""
    다음은 학생이 제출한 코드 블럭입니다. 이 블럭은 전체 프로그램의 특정 부분에 해당합니다.

    - '해당 블럭의 모범 코드'와 '학생이 제출한 코드'를 비교하여, 기능상 의미 있는 차이 및 문법오류가 있는 경우 **반드시 모든 핵심적인 차이점(누락된 동작, 잘못된 변수명, 잘못된 흐름 등)을 두 줄이내** 로 지적하세요.
    - '해당 블럭의 모법 코드'와 '학생이 제출한 코드'를 비교하여, 기능상 의미 있는 차이 및 문법오류가 없는 경우 **반드시 "정답입니다." 라고만 하세요.
    - 단순한 공백, 들여쓰기 등 **스타일만 다른 경우는 틀렸다고 하지 마세요**.
    - 학생 코드가 '작성못함'인 경우에는 정답 코드나 '해당 블럭의 모범 코드'를 절대 포함하지 마세요. 대신 간접적인 힌트만 제공하세요.

    - 두 번째 줄에는 이 블럭이 전체 코드 흐름에서 어떤 역할을 하는지를 간단히 설명하세요.
    - 세 번째 줄에는 이 블럭이 어떤 로직이나 기능 흐름에 기여하는지를 설명하세요. (예: 입력 준비, 조건 분기, 반복 등)

    - 출력은 반드시 4줄 이내로 제한하며, 번호는 붙이지 마세요.
    - 문장은 짧고 단정하게. 반복하지 마세요.
    - 마크업(강조, 줄바꿈, 인용 등)은 하지 마세요.


    학생이 제출한 코드:
    {block_code}

    가이드라인:
    {guideline}

    해당 블럭의 모범 코드:
    {model_block}

    전체 모범 코드:
    {model_answer}
    """

    # 💡 디버그 로그로 프롬프트 출력
    try:
        with open("/tmp/prompt_debug.log", "a") as f:
            f.write("==== OpenAI Prompt ====\n")
            f.write(prompt)
            f.write("\n=======================\n\n")
    except Exception as log_error:
        pass  # 로그 실패 시 무시

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
    if len(sys.argv) != 3:
        print("error: 인자 부족")
        sys.exit(1)

    param_file = sys.argv[1]
    feedback_file = sys.argv[2]

    if not os.path.exists(param_file):
        print(f"파일 경로 오류: {param_file}")
        sys.exit(1)

    try:
        with open(param_file, 'r', encoding='utf-8') as f:
            params = json.load(f)
        problem_id = params.get("problem_id", "0")
        block_index = int(params.get("index", 0))
        block_code = params.get("block_code", "작성못함")
        step = int(params.get("step", 1))

        # 모범 코드 및 가이드라인 불러오기
        model_answer = get_model_answer(problem_id)
        guideline = get_guideline(problem_id, block_index, step)
        model_block = get_model_block(problem_id, block_index, step)

        # 디버깅 정보 기록
        with open("/tmp/python_input_debug.log", "a") as log_file:
            log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, step: {step},model_block:{model_block}, guideline: {guideline}, model_answer: {model_answer}\n")

        # 피드백 생성
        # 올바른 호출
        hint = generate_hint(block_code, block_index, guideline, model_block, model_answer)

        # 피드백을 파일로 저장
        with open(feedback_file, 'w', encoding='utf-8') as f:
            f.write(hint)

    except Exception as e:
        with open(feedback_file, 'w', encoding='utf-8') as f:
            f.write(f"오류 발생: {str(e)}")

if __name__ == "__main__":
    main()