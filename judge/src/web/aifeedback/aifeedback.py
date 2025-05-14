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
    includes = []  # #include 블럭 저장
    closing_braces = []  # 단독 } 블럭 저장
    inside_block = False
    block_indices = []

    for line in code_lines:
        # 헤더 선언 (#include)은 상수 블럭으로 처리
        if is_include_line(line):
            includes.append(line)
            all_blocks.append(includes)
            all_idx += 1
            includes = []
            continue
        
        # 단독 중괄호는 상수 블럭으로 처리
        if is_single_brace(line):
            closing_braces.append(line)
            all_blocks.append(closing_braces)
            all_idx += 1
            closing_braces = []
            continue
        
        # 블럭 시작 조건: start 태그를 만나면 새 블럭 시작
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
        
        # 블럭 종료 조건: 다음 블럭의 시작 태그를 만나면 블럭 종료
        elif is_tag_line(line):
            if current_block:
                blocks.append(current_block)
                all_blocks.append(current_block)
                block_indices.append((blocks_idx, all_idx))
                blocks_idx += 1
                all_idx += 1
                current_block = []
            inside_block = False
        
        # 블럭 내부 코드 추가
        if inside_block or not is_tag_line(line):
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