import pymysql
import json

# 유사도 계산 가중치 기준
algorithm_tags = {
    "최대공약수(GCD) / 최소공배수(LCM)", "소수 판별", "약수 구하기", "배수와 나머지",
    "최대값/최소값 찾기", "배열 정렬", "중복 제거", "배열 뒤집기", "순차 탐색",
    "좌표 이동", "행렬 연산", "문자열 조작", "피보나치 수열", "수학 관련 문제", "진법 변환"
}

# 유사도 계산 함수
def weighted_similarity(tags1, tags2):
    set1, set2 = set(tags1), set(tags2)
    if not set1 or not set2:
        return 0.0
    weighted_intersection = sum(3 if t in algorithm_tags else 1 for t in set1 & set2)
    weighted_union = sum(3 if t in algorithm_tags else 1 for t in set1 | set2)
    base_score = weighted_intersection / weighted_union
    if len(set1) == 1 and set1 == set2:
        base_score += 1.0
    return base_score

# 대상 문제 리스트
target_ids = [
    "1044", "1045", "1046", "1047", "1048", "1049", "1050", "1051", "1052", "1053",
    "1054", "1055", "1056", "1057", "1058", "1059", "1060", "1061", "1062", "1063",
    "1066", "1067", "1068", "1069", "1070", "1071", "1072", "1073", "1074", "1075",
    "1076", "1077", "1078", "1079", "1080", "1081", "1082", "1083", "1084", "1085",
    "1091", "1094", "1096", "1098", "1101", "1103", "1105", "1107", "1108", "1110"
]

# DB 연결 (서버 내부 실행 기준)
db = pymysql.connect(
    host="localhost",
    user="hustoj",
    password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
    database="jol",
    charset="utf8mb4",
    cursorclass=pymysql.cursors.DictCursor
)
cursor = db.cursor()

# CodeUp 문제 데이터 로드 (로컬 JSON)
with open('/home/Capstone_Design_Troy/py/codeup_all_problems_tagged.json', 'r', encoding='utf-8') as f:
    codeup_problems = json.load(f)

# 추천 시작
output_lines = []

for pid in target_ids:
    # DB에서 문제 태그 가져오기
    cursor.execute("""
        SELECT t.name FROM problem_tag pt
        JOIN tag t ON pt.tag_id = t.tag_id
        WHERE pt.problem_id = %s
    """, (pid,))
    result = cursor.fetchall()
    tag_list = [row["name"] for row in result]

    if not tag_list:
        continue

    # DB에서 문제 제목 가져오기
    cursor.execute("SELECT title FROM problem WHERE problem_id = %s", (pid,))
    title_row = cursor.fetchone()
    if not title_row:
        continue
    origin_title = title_row["title"]

    # 유사 문제 추천
    similarities = []
    for p in codeup_problems:
        sim = weighted_similarity(tag_list, p.get("tags", []))
        if sim > 0.3:
            similarities.append((p["problem_id"], p["title"], p["link"], sim))

    similarities.sort(key=lambda x: x[3], reverse=True)
    top3 = similarities[:3]

    # 마크다운 출력
    output_lines.append(f"### ✅ 문제 {pid} - {origin_title}")
    for spid, stitle, slink, _ in top3:
        output_lines.append(f"- **추천 문제:** [{stitle}]({slink})")
    output_lines.append("")  # 빈 줄

# 마크다운 파일 저장
with open("/home/Capstone_Design_Troy/py/recommend_output.md", "w", encoding="utf-8") as f:
    f.write("\n".join(output_lines))

print("✅ recommend_output.md 파일 생성 완료.")
