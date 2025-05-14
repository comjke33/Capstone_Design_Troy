import sys
import urllib.parse
import openai
import os
from dotenv import load_dotenv

# 환경 변수 파일 로드
dotenv_path = "/home/Capstone_Design_Troy/judge/src/web/add_problem/.env"
if os.path.exists(dotenv_path):
    load_dotenv(dotenv_path)

openai.api_key = os.getenv("OPENAI_API_KEY")

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

    이 코드에서 주어진 코드블럭을 작성하지 못하였습니다. 코드 블럭이 수행해야 하는 역할을 설명하고, 
    어떻게 작성해야 하는지에 대한 힌트를 알려주세요. 단, 코드를 알려주는 것은 안됩니다. 7줄 이내로 작성해주십시오.
    """
    try:
        client = openai.OpenAI()
        response = client.chat.completions.create(
            model="gpt-4o",
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
    if len(sys.argv) != 6:
        print("error: 인자 부족")
        sys.exit(1)

    problem_id = sys.argv[1]
    block_index = sys.argv[2]
    block_code = urllib.parse.unquote(sys.argv[3])
    guideline = urllib.parse.unquote(sys.argv[4])
    model_answer = urllib.parse.unquote(sys.argv[5])

    # 디버그 로그 추가
    with open("/tmp/python_input_debug.log", "a") as log_file:
        log_file.write(f"Received problem_id: {problem_id}, block_index: {block_index}, block_code: {block_code}, guideline: {guideline}, model_answer: {model_answer}\n")

    # AI 피드백 생성
    hint = generate_hint(block_code, block_index, guideline, model_answer)

    # 피드백 출력
    print(f"{hint}")

if __name__ == "__main__":
    main()