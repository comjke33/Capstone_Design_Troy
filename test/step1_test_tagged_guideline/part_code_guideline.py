import json

# JSON 파일 불러오기
with open("step1_test.json", "r", encoding="utf-8") as f:
    data = json.load(f)

# 첫 번째 항목에서 code 추출
code_text = data[0]["code"]

# 두 번째 항목에서 guideline 추출
guideline_text = data[1]["guideline"]

# 각각 텍스트 파일로 저장
with open("tagged_code.txt", "w", encoding="utf-8") as f:
    f.write(code_text)

with open("guideline.txt", "w", encoding="utf-8") as f:
    f.write(guideline_text)

print("tagged_code.txt와 guideline.txt가 생성되었습니다.")
