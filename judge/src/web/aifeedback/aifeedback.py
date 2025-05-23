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

def read_code_lines(filename):
    with open(filename, 'r') as f:
        return f.readlines()

def generate_hint(block_code, block_number, guideline, model_answer):
    """OpenAI API를 이용하여 코드 블럭에 대한 힌트 생성"""
    prompt = f"""
    학생 코드와 가이드라인, 모범 코드를 참고하여 문제점을 간단히 분석해주세요.
    1. 학생이 제출한 코드는 전체 모범 코드의 일부분에 대한 가이드라인을 보고 작성한 것 입니다.
    2. 학생 코드가 가이드라인과 다른 부분을 간단히 지적하고 이유를 설명하세요.
    3. 가이드라인에 맞게 수정하려면 어떤 방향으로 수정해야 하는지 제안해주세요.
    4. 가이드라인의 내용이 전체 코드에서 어떤 의미인지, 어떤 알고리즘인지 간단히 설명해주세요.
    5. 마크업은 하지 말아주세요.
    6. 문단 시작에는 번호를 붙이지 마세요.
    7. (중요)전체 출력은 최대 3줄 이내여야 합니다.
    8. 문장 호흡은 짧게 해주세요.

    학생이 제출한 코드:
    {block_code}

    가이드라인:
    {guideline}

    모범 코드:
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

        # 디버깅 정보 기록
        with open("/tmp/python_input_debug.log", "a") as log_file:
            log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, step: {step}, guideline: {guideline}, model_answer: {model_answer}\n")

        # 피드백 생성
        hint = generate_hint(block_code, block_index, guideline, model_answer)

        # 피드백을 파일로 저장
        with open(feedback_file, 'w', encoding='utf-8') as f:
            f.write(hint)

    except Exception as e:
        with open(feedback_file, 'w', encoding='utf-8') as f:
            f.write(f"오류 발생: {str(e)}")

if __name__ == "__main__":
    main()