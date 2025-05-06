import os
import sys

from transformers import AutoTokenizer
from transformers import AutoModelForSeq2SeqLM

from graphviz import Digraph

file_path = 'guideline_code1.txt'

# 입력 관련 키워드
INPUT_KEYWORDS = ["입력", "scanf", "사용자", "값 받기", "입력받기", "받는다"]

# 출력 관련 키워드
OUTPUT_KEYWORDS = ["출력", "printf", "화면에", "보여준다", "출력한다"]

# 처리/기능 관련 키워드
PROCESS_KEYWORDS = ["계산", "처리", "합산", "더한다", "뺀다", "곱한다", "나눈다", "비교", "판단", "조건", "반복", "변환", "수행"]

# 함수 정의 키워드
FUNCTION_KEYWORDS = [
    "함수 정의", "함수 선언", "함수를 작성한다", "함수를 구현한다",
    "함수를 만든다", "함수 생성", "함수를 정의한다", "새로운 함수", "함수"
]

def classify_line(line):
    line_lower = line.lower()  # 소문자로 비교 (대소문자 무시)

    if line.startswith("#") or line.startswith("["):  # 주석 처리된 줄은 -1
        return -1

    if any(keyword in line_lower for keyword in INPUT_KEYWORDS):
        return 1
    elif any(keyword in line_lower for keyword in OUTPUT_KEYWORDS):
        return 3
    elif any(keyword in line_lower for keyword in PROCESS_KEYWORDS):
        return 2
    elif any(keyword in line_lower for keyword in FUNCTION_KEYWORDS):
        return 4
    else:
        return -1  # 아무 키워드에도 해당하지 않음


def generate_summary(input):
    # data 값만 뽑아서 , 로 연결
    merged_data = ", ".join(item["data"] for item in input)

    # line_number 중 최소, 최대 구하기
    start_line = min(item["line_number"] for item in input)
    end_line = max(item["line_number"] for item in input)

    model_path = "../kot5-small-finetuned-model"

    tokenizer = AutoTokenizer.from_pretrained(model_path)
    model = AutoModelForSeq2SeqLM.from_pretrained(model_path)

    # prefix 추가 (학습할 때 "요약:"으로 학습했으니 동일하게)
    input_ids = tokenizer("요약: " + merged_data, return_tensors="pt", truncation=True, padding=True, max_length = 64).input_ids

    # 모델에 넣고 출력 생성
    output_ids = model.generate(input_ids, max_length=64, num_beams=4)

    # 디코딩해서 텍스트로 변환
    summary = tokenizer.decode(output_ids[0], skip_special_tokens=True)

    return summary, start_line, end_line


if __name__ == "__main__":
    # if len(sys.argv) == 2:
    #     guideline_filename = sys.argv[1]

    # 초기화
    current_category = None
    buffer_lines = []

    flowcharts = []

    with open(file_path, 'r', encoding='utf-8') as f:
        for line_number, line in enumerate(f, start=1):
            line = line.strip()
            if not line:
                continue  # 빈 줄 건너뛰기
            
            
            category = classify_line(line)
            # print(line_number, "    ", line, "   ", category)
            if category == -1:
                continue

            if current_category is None:
                # 첫 줄이라면 category 설정
                current_category = category
                buffer_lines.append({
                    "data" : line,
                    "line_number" : line_number
                })

            elif category == current_category:
                # 같은 카테고리면 계속 모음
                buffer_lines.append({
                    "data" : line,
                    "line_number" : line_number
                })

            else:
                # 다른 카테고리면 기존 모은 데이터 처리
                result, start_num, end_num = generate_summary(buffer_lines)
                flowcharts.append({
                    "category": current_category,
                    "summary": result,
                    "start_line": start_num,
                    "end_line": end_num
                })

                buffer_lines = []  # 버퍼 초기화
                buffer_lines.append({
                    "data" : line,
                    "line_number" : line_number
                })  # 새로운 카테고리로 시작
                current_category = category

    # 파일 끝나고 마지막 남은 데이터 처리
    result, start_num, end_num = generate_summary(buffer_lines)
    flowcharts.append({
        "category": current_category,
        "summary": result,
        "start_line": start_num,
        "end_line": end_num
    })
    # for flow in flowcharts:
    #     print(flow.get("category"), flow.get("summary"), flow.get("start_line"), flow.get("end_line"))
    #     print("--------------------------------------------------") 

    for flow in flowcharts:
        # 디렉토리 생성
        output_dir = "/home/Capstone_Design_Troy/judge/src/web/flowcharts/"
        os.makedirs(output_dir, exist_ok=True)

        # 그래프 생성
        dot = Digraph(comment="Flowchart", format="png")
        dot.attr(fontname="Malgun Gothic", rankdir="TB")  # Top -> Bottom

        # 시작 노드
        dot.node("start", "Start", shape="ellipse", fillcolor="lightblue", style="filled", fontname="Malgun Gothic")

        prev = "start"

        # 각 설명문을 네모박스로 추가하고 연결
        for idx, desc in enumerate(flow.get("summary")):
            node_id = f"step{idx}"
            dot.node(node_id, desc, shape="box", fontname="Malgun Gothic", style="filled", fillcolor="white")
            dot.edge(prev, node_id)
            prev = node_id

        # 마지막 끝 노드
        dot.node("end", "End", shape="ellipse", fillcolor="lightblue", style="filled", fontname="Malgun Gothic")
        dot.edge(prev, "end")

        # 파일로 출력

        ## TODO filename에 규칙맞춰서 넣기
        filename = os.path.join(output_dir, "flowchart_simple")
        dot.render(filename, cleanup=True)

        print(f"순서도 생성 완료: {filename}.png")


        ## TODO problem_id, filename, start_line, end_line SQL에 저장하기