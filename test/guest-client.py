import socket, json

HOST = '192.168.0.60'
PORT = 9966

#파일에서 JSON 데이터 읽기
file_path = './question_and_code_test3.json'

with open(file_path, 'r', encoding='utf-8') as f:
	data_list = json.load(f)

first_item = data_list[0]

problem_data = [{
	"question": first_item["question"],
	"code": first_item["code"]
}]

with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
#	s.connect((HOST,PORT))
#	s.sendall(json.dumps(problem_data, ensure_ascii=False).encode())
#	s.shutdown(socket.SHUT_WR)

#	result = s.recv(10000).decode()
#	guide_data = json.loads(result)

	#with open("test.txt","w") as f:
		#f.write(guide_data["guideline"])

	#with open("tagged_code.txt","w") as f:
		#f.write(guide_data["code"])
		
	with open("original_code.txt","w") as f:
		f.write(first_item["code"])
