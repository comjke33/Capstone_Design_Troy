import socket, json

HOST = '192.168.0.60'
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

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
    s.connect((HOST, PORT))
    s.sendall(json.dumps(problem_data, ensure_ascii=False).encode())
    s.shutdown(socket.SHUT_WR)

    result = s.recv(10000).decode()
    guide_data = json.loads(result)

    # 각 단계에 맞는 파일로 저장
    step_names = ['step1', 'step2', 'step3']
    for i, step in enumerate(step_names, 1):
        # step1_tagged_code.txt, step1_guideline.txt와 같은 파일로 저장
        tagged_code_filename = f"./total_test/{step}_tagged_code.txt"
        guideline_filename = f"./total_test/{step}_guideline.txt"
        
        with open(tagged_code_filename, "w", encoding="utf-8") as f:
            f.write(guide_data.get(f"{step}_tagged_code", ""))
        
        with open(guideline_filename, "w", encoding="utf-8") as f:
            f.write(guide_data.get(f"{step}_guideline", ""))
        
    # original_code_test.txt 파일에 원본 코드 저장
    # with open("original_code_test.txt", "w", encoding="utf-8") as f:
    #     f.write(first_item["code"])