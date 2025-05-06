import re
import os
import sys

from transformers import AutoTokenizer
from transformers import AutoModelForSeq2SeqLM
from graphviz import Digraph

api_key_ = os.getenv("OPENAI_API_KEY")

# --- 모델 준비 ---
# model_path = "./kot5-small-finetuned-model"
# tokenizer = AutoTokenizer.from_pretrained(model_path)
# model = AutoModelForSeq2SeqLM.from_pretrained(model_path)

def parse_guideline_blocks(text):
    lines = text.split('\n')
    blocks = []
    current_block = []
    current_depth = None

    for line in lines:
        # 블록 시작
        start = re.search(r'\[(\w+)_start\((\d+)\)\]', line)
        end = re.search(r'\[(\w+)_end\((\d+)\)\]', line)

        if start:
            tag_type, depth = start.group(1), int(start.group(2))
            if current_block:
                blocks.append( (current_depth, current_block) )
            current_block = [line]
            current_depth = depth

        elif end:
            current_block.append(line)
            blocks.append( (current_depth, current_block) )
            current_block = []
            current_depth = None

        else:
            if current_block is not None:
                current_block.append(line)

    return blocks

def classify_block(block_lines):
    block_text = "\n".join(block_lines)
    # 입력
    if re.search(r'scanf|입력|입력받|scanf', block_text):
        return "입력"
    # 출력
    elif re.search(r'printf|출력|print', block_text):
        return "출력"
    # 나머지는 처리
    else:
        return "처리"

def process_guideline(text):
    blocks = parse_guideline_blocks(text)
    results = []

    for idx, (depth, block_lines) in enumerate(blocks):
        if depth is None:
            continue
        filtered_lines = [line for line in block_lines if not line.strip().startswith('[') and not line == '']
        role = classify_block(filtered_lines)
        block_text = "\n".join(filtered_lines)

        results.append({
            "블록번호": idx + 1,
            "깊이": depth,
            "역할": role,
            "내용": block_text,
            "index": idx 
        })

    return results

def extract_code_lines(res_content):
    code_lines = []
    for line in res_content.split('\n'):
        line = line.strip()
        # 태그 줄은 제외
        if re.match(r'\[.*_(start|end)\(\d+\)\]', line):
            continue
        if line:  # 빈 줄 제외
            code_lines.append(line)
    return code_lines

def analyze_line_type(line):
    # 변수 선언
    if re.match(r'(int|float|double|char)\s+\w+', line):
        return "변수 선언"
    # 입력
    elif "scanf" in line or "입력" in line:
        return "입력"
    # 출력
    elif "printf" in line or "출력" in line:
        return "출력"
    # 조건
    elif re.search(r'if|switch', line):
        return "조건"
    # 반복
    elif re.search(r'for|while|do', line):
        return "반복"
    # 기타
    else:
        return "처리"

def generate_summary_hf(buffer_lines):
    if not buffer_lines:
        return "(내용 없음)"

    merged_data = ", ".join([line['내용'] for line in buffer_lines])

    # 방법 1️⃣ HuggingFace 모델 사용
    input_ids = tokenizer("요약: " + merged_data, return_tensors="pt", truncation=True, padding=True, max_length=128).input_ids
    output_ids = model.generate(input_ids, max_length=128, num_beams=4)
    summary = tokenizer.decode(output_ids[0], skip_special_tokens=True)
    return summary.strip()

def generate_summary_gpt(buffer_lines, problem_path):
    # line_number 중 최소, 최대 구하기
    start_line = min(item["index"] for item in buffer_lines)
    end_line = max(item["index"] for item in buffer_lines)

    from openai import OpenAI
    client = OpenAI(api_key=api_key_)

    # 문제 설명 읽기
    with open(problem_path, "r", encoding="utf-8") as f:
        problem_description = " ".join(line.strip() for line in f.readlines())

    # 블록 데이터 준비
    block_texts = []
    for idx, block in enumerate(buffer_lines):
        block_texts.append(
            f"블록 {idx+1} (역할: {block['역할']}) - {block['내용']}"
        )

    merged_blocks = "\n".join(block_texts)

    # ChatGPT에게 구조화된 prompt 전달
    response = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[
            {
                "role": "system",
                "content": (
                    "너는 프로그래밍 언어 전문가이자 흐름도 분석가야. "
                    "다음은 문제 설명이야: " + problem_description + 
                    "\n각 블록들의 코드 설명을 제공할 테니, 이들의 관계를 고려해서 의미가 반영된 요약을 15글자 이내로 큰따옴표 없이 생성해줘."
                    "\n특히 반복, 조건, 출력 흐름의 관계를 무시하지 마."
                )
            },
            {
                "role": "user",
                "content": merged_blocks
            }
        ],
    )

    data = response.choices[0].message.content.strip()
    if data.startswith('"') and data.endswith('"'):
        data = data[1:-1]

    return {
        "data": data,
        "start_line": start_line,
        "end_line": end_line
    }

def has_output_in_deeper_blocks(current_index, current_depth, results):
    for next_idx in range(current_index + 1, len(results)):
        next_block = results[next_idx]
        # print("next_block: ", next_block)
        next_depth = next_block["깊이"]
        if next_depth > current_depth:
            # 이 블록 내용에서 출력 관련 코드가 있는지 검사
            code_lines = extract_code_lines(next_block["내용"])
            for line in code_lines:
                if "printf" in line or "출력" in line or "print" in line:
                    return True
        elif next_depth <= current_depth:
            # 깊이가 낮거나 같은 블록이 나오면 더 이상 검사 안 함
            break
    return False


if __name__ == "__main__":
    if len(sys.argv) == 5:
        guideline_path = sys.argv[1]
        problem_path = sys.argv[2]
        output_dir = sys.argv[3]
        problem_id = sys.argv[4]
    
    # guideline_path = 'guideline1.txt'
    # problem_path = 'problem.txt'
    # output_dir = "./output/"
    # problem_id = "1000"

    guideline_text = ""

    with open(guideline_path, "r", encoding="utf-8") as f:
        guideline_text = f.read()

    results = process_guideline(guideline_text)

    buffer_lines = []
    save_text = []
    save_summary = []
    flowcharts = []

    idx = 0
    while idx < len(results):
        res = results[idx]

        if len(buffer_lines) == 0:
            buffer_lines.append(res)
            idx += 1
            continue

        # 역할이 같을 때
        if res["역할"] == buffer_lines[-1]["역할"]:
            current_depth = res["깊이"]
            found_output = has_output_in_deeper_blocks(idx, current_depth, results)

            if found_output:
                save_text.append([block for block in buffer_lines])


                # 현재 블록 추가
                buffer_lines = [res]

                # idx 다음 블록들 중 current_depth보다 깊은 것들은 다 추가
                next_idx = idx + 1
                while next_idx < len(results):
                    next_block = results[next_idx]
                    next_depth = next_block["깊이"]

                    if next_depth > current_depth:
                        buffer_lines.append(next_block)
                        next_idx += 1
                    else:
                        break  # 더 깊지 않으면 멈춤

                idx = next_idx  # 다음 검사할 블록으로 이동
                continue

            else:
                buffer_lines.append(res)
                idx += 1
                continue

        else:
            if res["역할"] == "출력" and res["깊이"] > buffer_lines[-1]["깊이"]:
                # 출력 블록이 깊이가 더 깊으면 buffer_lines에 추가
                buffer_lines.append(res)
                idx += 1
                continue

            # 역할 다르면 지금까지 모은 블록 요약
            save_text.append([block for block in buffer_lines])
            buffer_lines = [res]
            idx += 1

    # 마지막 남은 buffer_lines 처리
    if buffer_lines:
        save_text.append([block for block in buffer_lines])


    for i, text in enumerate(save_text):
        result = generate_summary_gpt(text, problem_path)
        summary = result["data"]
        start_line = result["start_line"]
        end_line = result["end_line"]

        flowcharts.append({
            "summary": summary,
            "start_line": start_line,
            "end_line": end_line
        })

    # for flow in flowcharts:
    #     print(flow.get("summary"), flow.get("start_line"), flow.get("end_line"))
    #     print("--------------------------------------------------") 

    os.makedirs(output_dir, exist_ok=True)

    # 시작 노드 설정
    start_node = "start"
    end_node = "end"

    for highlight_idx in range(len(flowcharts)):

        dot = Digraph(comment="Flowchart", format="png")
        dot.attr(fontname="Malgun Gothic", rankdir="TB")  # Top -> Bottom

        # 시작 노드
        dot.node(start_node, "Start", shape="ellipse", fillcolor="lightblue", style="filled", fontname="Malgun Gothic")

        prev = start_node

        for idx, flow in enumerate(flowcharts):
            desc = flow.get("summary")
            node_id = f"step{idx}"

            # 현재 highlight_idx 번째 노드만 빨간색 테두리
            if idx == highlight_idx:
                dot.node(
                    node_id, desc,
                    shape="box",
                    fontname="Malgun Gothic",
                    style="filled",
                    fillcolor="white",
                    color="red",
                    penwidth="2"
                )
            else:
                dot.node(
                    node_id, desc,
                    shape="box",
                    fontname="Malgun Gothic",
                    style="filled",
                    fillcolor="white"
                )

            dot.edge(prev, node_id)
            prev = node_id

        # 마지막 끝 노드
        dot.node(end_node, "End", shape="ellipse", fillcolor="lightblue", style="filled", fontname="Malgun Gothic")
        dot.edge(prev, end_node)

        # 파일로 출력 → problem_id_1.png, problem_id_2.png, ...
        filename = os.path.join(output_dir, f"{problem_id}_{highlight_idx + 1}")
        dot.render(filename, cleanup=True)

        # print(f"파일 저장됨: {filename}.png")
    print(flowcharts)
    