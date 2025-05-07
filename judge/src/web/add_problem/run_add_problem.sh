#!/bin/bash

PROBLEM_ID=$1
DESCRIPTION=$2
EXEMPLARY_CODE=$3

cd /home/Capstone_Design_Troy/judge/src/web/add_problem

touch here1

# 1. 문제 생성
python3 make_question_and_code.py "$DESCRIPTION" "$EXEMPLARY_CODE"

touch here2

# 2. 가이드라인 생성
python3 make_guideline.py "$PROBLEM_ID"

touch here3

# 3. 이후 스크립트 실행 (예시)
#python3 post_process.py "$PROBLEM_ID"