import sys
import openai
import os

from dotenv import load_dotenv

dotenv_path = "/home/Capstone_Design_Troy/judge/src/web/add_problem/.env"
load_dotenv(dotenv_path)

def generate_hint(block_code, block_number):
    """OpenAI API를 이용하여 코드 블럭에 대한 힌트 생성"""
    openai.api_key = os.getenv("OPENAI_API_KEY")

    prompt = f"""
    다음은 C 코드의 일부입니다.

    코드 블럭 (번호 {block_number}):
    {block_code}

    이 코드에서 주어진 코드블럭을 작성하지 못하였습니다. 코드 블럭이 수행해야 하는 역할을 설명하고, 
    어떻게 작성해야 하는지에 대한 힌트를 알려주세요. 단, 코드를 알려주는 것은 안됩니다. 7줄 이내로 작성해주십시오.
    """
    try:
        response = openai.Completion.create(
            model="gpt-4o-mini",
            prompt=prompt,
            max_tokens=300,
            temperature=0.7
        )
        return response['choices'][0]['text'].strip()
    except Exception as e:
        return f"AI 피드백 생성 오류: {str(e)}"

def main():
    if len(sys.argv) != 4:
        print("error: 인자 부족")
        sys.exit(1)

    problem_id = sys.argv[1]
    block_index = sys.argv[2]
    block_code = sys.argv[3]

    # AI 피드백 생성
    hint = generate_hint(block_code, block_index)

    # 피드백 출력
    print(f"{hint}")

if __name__ == "__main__":
    main()