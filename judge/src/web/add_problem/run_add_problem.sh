#!/bin/bash

PROBLEM_ID=$(echo "$1" | base64 -d)
DESCRIPTION=$(echo "$2" | base64 -d)
EXEMPLARY_CODE=$(echo "$3" | base64 -d)
OUTPUT_DIR=$(echo "$4" | base64 -d)

# PROBLEM_ID=$1
# DESCRIPTION=$2
# EXEMPLARY_CODE=$3

#cd /home/Capstone_Design_Troy/judge/src/web/add_problem

# 1. 문제 생성
touch here0
echo $PROBLEM_ID >> here0
echo $DESCRIPTION >> here0
echo $EXEMPLARY_CODE >> here0
echo $OUTPUT_DIR >> here0

python3 make_question_and_code.py "$DESCRIPTION" "$EXEMPLARY_CODE"
if [ $? -ne 0 ]; then
    echo "문제 생성 실패"
    exit 1
fi
touch here1
echo "here1 생성"

# 2. 가이드라인 생성
# python3 make_guideline.py "$PROBLEM_ID" 2>> here1
# if [ $? -ne 0 ]; then
#     echo "가이드라인 생성 실패"
#     exit 1
# fi
touch here2
echo "here2 생성"

# 3. 이후 스크립트 실행 (예시)
#python3 post_process.py "$PROBLEM_ID"

cd /home/Capstone_Design_Troy/py/

touch here3


TAGGED_GUIDELINE="./../tagged_guideline/1260_step1.txt"
OUTPUT_DIRECTORY="./../flowcharts/"
echo $TAGGED_GUIDELINE >> here3
DESC="/home/Capstone_Design_Troy/judge/src/web/add_problem/question.txt"
echo $DESC >> here3

NEW_PID="1260"

touch fail

python3 make_flowchart.py "$TAGGED_GUIDELINE" "$OUTPUT_DIRECTORY" "$NEW_PID" 2>> fail
if [ $? -ne 0 ]; then
    echo "flowchart 생성 실패" >> fail
    exit 1
fi



touch here4
echo "스크립트 완료"