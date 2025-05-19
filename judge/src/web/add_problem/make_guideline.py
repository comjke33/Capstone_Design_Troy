import socket, json
import sys

if len(sys.argv) > 1:
    pid = sys.argv[1]

HOST = '172.30.1.85'
PORT = 9966

#파일에서 JSON 데이터 읽기
file_path = './questions_and_codes.json'

with open(file_path, 'r', encoding='utf-8') as f:
	data_list = json.load(f)

first_item = data_list[0]

problem_data = [{
	"question": first_item["question"],
	"code": first_item["code"]
}]

# tagged_code_filename = f"./../tagged_code/{pid}_step1_tagged_code.txt"
# with open(tagged_code_filename, "w", encoding="utf-8") as f:
#     f.write("success")

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
    s.connect((HOST, PORT))
    s.sendall(json.dumps(problem_data, ensure_ascii=False).encode())
    s.shutdown(socket.SHUT_WR)

    # 데이터 수신: 데이터를 여러 번에 걸쳐 받기
    buffer_size = 4096  # 한 번에 받을 최대 크기 (예시로 4KB)
    received_data = b""
    full_message = ""

    while True:
        chunk = s.recv(buffer_size)
        if not chunk:
            break
        received_data += chunk

    # 전체 데이터를 문자열로 디코딩 (단 한 번)
    try:
        full_message = received_data.decode('utf-8')
    except UnicodeDecodeError as e:
        print("UTF-8 디코딩 오류:", e)
        # 오류 무시하고 대체 문자로 표시
        full_message = received_data.decode('utf-8', errors='replace')

    # "데이터 생성중..." 메시지 제외
    cleaned_message_lines = []
    for line in full_message.splitlines():
        if "데이터 생성중..." in line:
            print(line.strip())
            continue
        cleaned_message_lines.append(line)

    # 최종 JSON 문자열 구성
    full_message = "\n".join(cleaned_message_lines)

    # 최종 진짜 데이터 처리
    try:
        guide_data = json.loads(full_message)
    except json.JSONDecodeError as e:
        print("JSON 파싱 오류:", e)
        print("수신한 데이터:", full_message)
        exit(1)

    # 각 단계에 맞는 파일로 저장
    step_names = ['step1', 'step2', 'step3']
    for i, step in enumerate(step_names, 1):
        # step1_tagged_code.txt, step1_guideline.txt와 같은 파일로 저장
        tagged_code_filename = f"./../tagged_code/{pid}_{step}.txt"
        guideline_filename = f"./../tagged_guideline/{pid}_{step}.txt"
        
        with open(tagged_code_filename, "w", encoding="utf-8") as f:
            f.write(guide_data.get(f"{step}_tagged_code", ""))
        
        with open(guideline_filename, "w", encoding="utf-8") as f:
            f.write(guide_data.get(f"{step}_guideline", ""))
        
    # original_code_test.txt 파일에 원본 코드 저장
    # with open("original_code_test.txt", "w", encoding="utf-8") as f:
    #     f.write(first_item["code"])
