import re
import os
import sys
from dotenv import load_dotenv


# from transformers import AutoTokenizer
# from transformers import AutoModelForSeq2SeqLM
from graphviz import Digraph

# --- 모델 준비 ---
# model_path = "./kot5-small-finetuned-model"
# tokenizer = AutoTokenizer.from_pretrained(model_path)
# model = AutoModelForSeq2SeqLM.from_pretrained(model_path)

import os
import re
import sys
import json
import mysql.connector





# with open(log_path, "w") as f:
#     f.write(f"DOTENV PATH: {dotenv_path}\n")
#     f.write(f"File Exists: {os.path.exists(dotenv_path)}\n")
#     f.write(f"API_KEY: {api_key_}\n")
#     f.write("실행은 돼요..\n")

###########################
# 블록 파싱 (함수 단위로 그룹화)
###########################
def parse_guideline_blocks(text):
    lines = text.split('\n')

    functions = []  # 함수별 block 리스트
    current_function_blocks = []

    current_block = []
    current_depth = None
    inside_function = False

    for line in lines:
        func_start = re.search(r'\[func_def_start\((\d+)\)\]', line)
        func_end = re.search(r'\[func_def_end\((\d+)\)\]', line)

        start = re.search(r'\[(\w+)_start\((\d+)\)\]', line)
        end = re.search(r'\[(\w+)_end\((\d+)\)\]', line)

        if func_start:
            inside_function = True
            current_function_blocks = []
            continue

        if func_end:
            if current_block:
                current_function_blocks.append((current_depth, current_block))
            functions.append(current_function_blocks)
            current_block = []
            current_depth = None
            inside_function = False
            continue

        if not inside_function:
            continue

        if start:
            tag_type, depth = start.group(1), int(start.group(2))
            if current_block:
                current_function_blocks.append((current_depth, current_block))
            current_block = [line]
            current_depth = depth

        elif end:
            current_block.append(line)
            current_function_blocks.append((current_depth, current_block))
            current_block = []
            current_depth = None

        else:
            if current_block is not None:
                current_block.append(line)

    return functions

###########################
# 블록 역할 분류
###########################
def classify_block(block_lines):
    block_text = "\n".join(block_lines)
    if re.search(r'scanf|입력|입력받|scanf', block_text):
        return "입력"
    elif re.search(r'printf|출력|print', block_text):
        return "출력"
    else:
        return "처리"

###########################
# 함수별 블록 정보 처리
###########################
def process_guideline(text):
    functions = parse_guideline_blocks(text)
    all_results = []

    for func_idx, blocks in enumerate(functions):
        results = []
        for idx, (depth, block_lines) in enumerate(blocks):
            if depth is None:
                continue
            filtered_lines = [line for line in block_lines if not line.strip().startswith('[') and line != '']
            role = classify_block(filtered_lines)
            block_text = "\n".join(filtered_lines)

            results.append({
                "블록번호": idx + 1,
                "깊이": depth,
                "역할": role,
                "내용": block_text,
                "index": idx
            })
        all_results.append(results)
        for result in all_results:
            print(result)
    return all_results  # 함수별 리스트

###########################
# 코드 라인 추출
###########################
def extract_code_lines(res_content):
    code_lines = []
    for line in res_content.split('\n'):
        line = line.strip()
        if re.match(r'\[.*_(start|end)\(\d+\)\]', line):
            continue
        if line:
            code_lines.append(line)
    return code_lines

###########################
# 블록 안에 출력 여부 확인
###########################
def has_output_in_deeper_blocks(current_index, current_depth, results):
    for next_idx in range(current_index + 1, len(results)):
        next_block = results[next_idx]
        next_depth = next_block["깊이"]
        if next_depth > current_depth:
            code_lines = extract_code_lines(next_block["내용"])
            for line in code_lines:
                if "printf" in line or "출력" in line or "print" in line:
                    return True
        elif next_depth <= current_depth:
            break
    return False

###########################
# GPT 요약 생성 (함수별)
###########################
def generate_summary_gpt(buffer_lines, problem):
    start_line = min(item["index"] for item in buffer_lines)
    end_line = max(item["index"] for item in buffer_lines)

    dotenv_path = "/home/Capstone_Design_Troy/judge/src/web/add_problem/.env"
    load_dotenv(dotenv_path)

    api_key_ = os.getenv("OPENAI_API_KEY")

    from openai import OpenAI
    client = OpenAI(api_key=api_key_)

    block_texts = []
    for idx, block in enumerate(buffer_lines):
        block_texts.append(
            f"블록 {idx+1} (역할: {block['역할']}) - {block['내용']}"
        )

    merged_blocks = "\n".join(block_texts)

    response = client.chat.completions.create(
        model="gpt-4o-mini",
        messages=[
            {
                "role": "system",
                "content": (
                    f"너는 프로그래밍 언어 전문가이자 흐름도 분석가야. 다음은 문제 설명이야: {problem}\n"
                    "각 블록들의 코드 설명을 제공할 테니, 이들의 관계를 고려해서 의미가 반영된 요약을 15글자 이내로 큰따옴표 없이 생성해줘.\n"
                    "특히 반복, 조건, 출력 흐름의 관계를 무시하지 마."
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

###########################
# 메인
###########################
if __name__ == "__main__":


    guideline_path = ""
    output_dir = ""
    problem_id = ""

    if len(sys.argv) == 4:
        guideline_path = sys.argv[1]
        output_dir = sys.argv[2]
        problem_id = sys.argv[3]



    guideline_path = f"../tagged_guideline/{problem_id}_step1.txt"
    output_dir = "../flowcharts/"

    guideline_text = ""
    problem = ""

    with open(guideline_path, "r", encoding="utf-8") as f:
        guideline_text = f.read()

    # TODO
    # SQL에서 problem 선언
    # MySQL 연결 설정
    conn = mysql.connector.connect(
        host="localhost",
        user="hustoj",
        password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
        database="jol"
    )

    cursor = conn.cursor()
    cursor.execute("SELECT description FROM problem WHERE problem_id = %s", (problem_id,))
    problem = cursor.fetchall()
    
    all_results = process_guideline(guideline_text)  # 함수별 결과

    os.makedirs(output_dir, exist_ok=True)

    flowcharts = []

    for func_idx, results in enumerate(all_results):
        buffer_lines = []
        save_text = []

        idx = 0
        while idx < len(results):
            res = results[idx]

            if len(buffer_lines) == 0:
                buffer_lines.append(res)
                idx += 1
                continue

            if res["역할"] == buffer_lines[-1]["역할"]:
                current_depth = res["깊이"]
                found_output = has_output_in_deeper_blocks(idx, current_depth, results)

                if found_output:
                    save_text.append([block for block in buffer_lines])
                    buffer_lines = [res]

                    next_idx = idx + 1
                    while next_idx < len(results):
                        next_block = results[next_idx]
                        next_depth = next_block["깊이"]

                        if next_depth > current_depth:
                            buffer_lines.append(next_block)
                            next_idx += 1
                        else:
                            break
                    idx = next_idx
                    continue

                else:
                    buffer_lines.append(res)
                    idx += 1
                    continue

            else:
                if res["역할"] == "출력" and res["깊이"] > buffer_lines[-1]["깊이"]:
                    buffer_lines.append(res)
                    idx += 1
                    continue

                save_text.append([block for block in buffer_lines])
                buffer_lines = [res]
                idx += 1

        if buffer_lines:
            save_text.append([block for block in buffer_lines])

        for i, text in enumerate(save_text):
            result = generate_summary_gpt(text, problem)
            summary = result["data"]
            start_line = result["start_line"]
            end_line = result["end_line"]

            flowcharts.append({
                "함수번호": func_idx + 1,
                "summary": summary,
                "start_line": start_line,
                "end_line": end_line
            })

    # 결과 출력
    # for flow in flowcharts:
    #     print(f"[함수{flow['함수번호']}] {flow['summary']} ({flow['start_line']}~{flow['end_line']})")
    
    # json_output_path = os.path.join(output_dir, "flowcharts_info.json")
    # with open(json_output_path, "w", encoding="utf-8") as f:
    #     json.dump(flowcharts, f, ensure_ascii=False, indent=4)        

    for idx in range(len(flowcharts)):
        # print(flow.get("summary"), flow.get("start_line"), flow.get("end_line"))
        png_address = '/flowcharts/' + str(problem_id) + '_' + str(idx+1)
        cursor = conn.cursor(dictionary=True)
        cursor.execute(
            "INSERT INTO flowchart (problem_id, png_address, png_number, start_num, end_num) VALUES (%s, %s, %s, %s, %s)",
            (problem_id, png_address, idx+1, flowcharts[idx].get("start_line"), flowcharts[idx].get("end_line"))
        )
        problem = cursor.fetchall()
        
        print("--------------------------------------------------") 



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

        print(flowcharts)
    # print(flowcharts)

    conn.commit()

    # 정리
    cursor.close()
    conn.close()
    