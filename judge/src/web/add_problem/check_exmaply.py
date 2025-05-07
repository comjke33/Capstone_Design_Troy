import mysql.connector
import sys

if len(sys.argv) > 1:
    problem_id = sys.argv[1]



def print_problem_info(problem_id):
    # MySQL 연결 설정
    conn = mysql.connector.connect(
        host="localhost",
        user="hustoj",
        password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
        database="jol"
    )
    cursor = conn.cursor()

    # exemplary 테이블에서 exemplary_code 가져오기
    cursor.execute("SELECT exemplary_code FROM exemplary WHERE problem_id = %s", (problem_id,))
    exemplary_result = cursor.fetchone()

    if exemplary_result:
        print("Exemplary Code:")
        print(exemplary_result[0])
    else:
        print("No exemplary code found.")

    # flowchart_node 테이블에서 num 기준 정렬하여 tag 가져오기
    cursor.execute("SELECT num, tag FROM flowchart_node WHERE problem_id = %s ORDER BY num ASC", (problem_id,))
    flowchart_results = cursor.fetchall()

    if flowchart_results:
        print("\nFlowchart Tags:")
        for num, tag in flowchart_results:
            print(f"{num}: {tag}")
    else:
        print("No flowchart nodes found.")

    cursor.close()
    conn.close()