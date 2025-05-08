import json
import os
import sys

if len(sys.argv) > 2:
    new_question = sys.argv[1]
    new_code = sys.argv[2]


new_entry = {
    "question": new_question,
    "code": new_code
}

# # 파일이 이미 존재하면 기존 데이터 읽기
# if os.path.exists("Survey2.json"):
#     with open("Survey2.json", "r", encoding="utf-8") as f:
#         try:
#             data = json.load(f)
#             # 기존 파일이 리스트가 아니라면 강제로 리스트로 변환
#             if isinstance(data, dict):
#                 data = [data]
#         except json.JSONDecodeError:
#             data = []
# else:
#     data = []

data = []
# 새 코드 추가
data.append(new_entry)

# 다시 JSON 파일로 저장
with open("questions_and_codes.json", "w", encoding="utf-8") as f:
    json.dump(data, f, indent=4, ensure_ascii=False)

with open ("question.txt", "w", encoding="utf-8") as f:
    f.write(new_question)

print("코드가 data.json에 추가되었습니다.")
