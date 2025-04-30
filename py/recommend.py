import json
import sys
from typing import List, Tuple

# ✅ 알고리즘 관련 태그 (가중치 2 적용 대상)
algorithm_tags = {
    "최대공약수(GCD) / 최소공배수(LCM)",
    "소수 판별",
    "약수 구하기",
    "배수와 나머지",
    "최대값/최소값 찾기",
    "배열 정렬",
    "중복 제거",
    "배열 뒤집기",
    "순차 탐색",
    "좌표 이동",
    "행렬 연산",
    "문자열 조작"
}

# ✅ 문제 데이터 로드
with open('/home/Capstone_Design_Troy/py/codeup_all_problems_tagged.json', 'r', encoding='utf-8') as f:
    problems = json.load(f)

# ✅ 태그 인자 입력 받기
tags_input = sys.argv[1:]
if not tags_input:
    print("❌ 태그 정보가 없습니다.")
    sys.exit(1)

# ✅ 유사도 계산 함수
def weighted_similarity(tags1: List[str], tags2: List[str]) -> float:
    set1, set2 = set(tags1), set(tags2)
    if not set1 or not set2:
        return 0.0

    # 가중치 포함한 교집합 및 합집합 계산
    weighted_intersection = sum(
        2 if tag in algorithm_tags else 1 for tag in set1 & set2
    )
    weighted_union = sum(
        2 if tag in algorithm_tags else 1 for tag in set1 | set2
    )

    base_score = weighted_intersection / weighted_union

    # ✨ 문제의 태그가 1개이고 일치한 경우 보너스
    bonus = 0.0
    if len(set2) == 1 and (set1 & set2) and len(set1) == 1:
        bonus += 1.0

    return base_score + bonus

# ✅ 추천 함수
def recommend(tags: List[str], top_k: int = 3) -> List[Tuple[str, str, float, str, List[str]]]:
    results = []
    for problem in problems:
        problem_tags = problem.get("tags", [])
        score = weighted_similarity(tags, problem_tags)
        if score > 0:
            results.append((
                problem["problem_id"],
                problem["title"],
                score,
                problem["link"],
                problem_tags
            ))
    results.sort(key=lambda x: x[2], reverse=True)
    return results[:top_k]

# 🔖 태그 출력
print("🔖 현재 문제의 태그")
for tag in tags_input:
    print(f"- {tag}")

# 🔍 추천 결과 출력
print("\n🔍 유사한 Codeup 문제 추천 결과")
recommendations = recommend(tags_input)

for pid, title, score, link, taglist in recommendations:
    print(f"{pid}||{title}||{score:.2f}||{link}||{','.join(taglist)}")
