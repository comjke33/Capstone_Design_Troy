from openai import OpenAI
import os
import json
import re
import mysql.connector
import sys

if len(sys.argv) > 1:
    problem_id = sys.argv[1]


# OpenAI API 클라이언트 세팅
api_key_ = os.getenv("OPENAI_API_KEY")
client = OpenAI(api_key=api_key_)


file_path = './questions_and_codes.json'

with open(file_path, 'r', encoding='utf-8') as f:
    data_list = json.load(f)

first_item = data_list[0]

# 문제 설명과 코드
problem_description = first_item["question"]

code = first_item["code"]

def format_c_code(code: str) -> str:
    import re
    
    # 먼저 문자열 리터럴을 추출하여 보관
    string_literals = []
    
    def extract_strings(code_text):
        # 문자열 패턴: 따옴표로 시작하고, 그 안에 따옴표가 아닌 문자 또는 이스케이프된 문자가 있고, 따옴표로 끝남
        pattern = r'"([^"\\]|\\.)*"'
        result = code_text
        
        # 모든 문자열 찾기
        for match in re.finditer(pattern, code_text):
            string_literals.append(match.group(0))
            placeholder = f"__STRING_LITERAL_{len(string_literals)-1}__"
            # 한 번에 하나의 문자열만 치환 (정확한 위치에)
            result = result.replace(match.group(0), placeholder, 1)
        
        return result
    
    # 문자열 추출
    code_without_strings = extract_strings(code)
    
    # 토큰화를 위한 구분자 추가
    code_without_strings = re.sub(r'([{}();,])', r' \1 ', code_without_strings)
    
    # 라인 단위로 처리
    lines = code_without_strings.splitlines()
    formatted_lines = []
    indent_level = 0
    
    control_keywords = {"if", "else", "for", "while", "do", "switch"}
    
    for line_content in lines:
        if not line_content.strip():
            continue
            
        # 전처리기 지시문 처리
        if line_content.strip().startswith('#'):
            formatted_lines.append(line_content.strip())
            continue
            
        tokens = line_content.split()
        if not tokens:
            continue
            
        current_line = ""
        in_for_header = False
        paren_depth = 0
        i = 0
        
        while i < len(tokens):
            token = tokens[i]
            
            # 문자열 리터럴 복원
            if "__STRING_LITERAL_" in token:
                # 정규식으로 패턴 추출
                pattern = r'__STRING_LITERAL_(\d+)__'
                match = re.search(pattern, token)
                if match:
                    index = int(match.group(1))
                    # 토큰이 정확히 패턴과 일치하는지 확인
                    if token == f"__STRING_LITERAL_{index}__":
                        token = string_literals[index]
                    else:
                        # 부분적으로 포함된 경우 (다른 문자와 결합된 경우)
                        token = token.replace(f"__STRING_LITERAL_{index}__", string_literals[index])
            
            if token == "for":
                in_for_header = True
                paren_depth = 0
                
            if token == "(" and in_for_header:
                paren_depth += 1
            elif token == ")" and in_for_header:
                paren_depth -= 1
                if paren_depth == 0:
                    in_for_header = False
                    
            if token == ';':
                current_line += ';'
                if not in_for_header:
                    formatted_lines.append(" " * (indent_level * 4) + current_line.strip())
                    current_line = ""
            elif token == '{':
                if current_line.strip().split() and (current_line.strip().split()[-1] in control_keywords or current_line.strip().endswith(')')):
                    current_line += " {"
                    formatted_lines.append(" " * (indent_level * 4) + current_line.strip())
                    indent_level += 1
                    current_line = ""
                else:
                    if current_line.strip():
                        formatted_lines.append(" " * (indent_level * 4) + current_line.strip())
                    formatted_lines.append(" " * (indent_level * 4) + "{")
                    indent_level += 1
                    current_line = ""
            elif token == '}':
                if current_line.strip():
                    formatted_lines.append(" " * (indent_level * 4) + current_line.strip())
                indent_level = max(0, indent_level - 1)  # 음수 들여쓰기 방지
                formatted_lines.append(" " * (indent_level * 4) + "}")
                current_line = ""
            elif token == ',':
                current_line += ', '
            else:
                if current_line and not current_line.endswith(('(', '[', '=', '+', '-', '*', '/', '%', '<', '>', '&', '|', '!', '{', ' ')):
                    current_line += " "
                current_line += token
            i += 1
            
        if current_line.strip():
            formatted_lines.append(" " * (indent_level * 4) + current_line.strip())
    
    # 결과 문자열에 남아있을 수 있는 문자열 플레이스홀더 복원
    result = "\n".join(formatted_lines)
    
    # 모든 문자열 플레이스홀더 복원
    for i, string in enumerate(string_literals):
        result = result.replace(f"__STRING_LITERAL_{i}__", string)
    
    return result

def join_broken_string_literals(code_lines):
    """
    깨진 문자열 리터럴 줄을 다시 붙인다.
    예: '    printf("%d', '", t);' → '    printf("%d\n", t);'
    """
    new_lines = []
    skip = False
    for i in range(len(code_lines)):
        if skip:
            skip = False
            continue

        line = code_lines[i]
        if '"' in line and line.count('"') % 2 != 0 and i + 1 < len(code_lines):
            # 다음 줄과 붙이기
            merged = line + code_lines[i + 1]
            new_lines.append(merged)
            skip = True
        else:
            new_lines.append(line)
    return new_lines

def escape_newlines_in_string_literals(line):
    """
    한 줄 안에서 문자열 리터럴 안의 \n만 \\n으로 바꾼다.
    """
    result = ''
    in_string = False
    i = 0
    while i < len(line):
        c = line[i]
        if c == '"':
            result += c
            i += 1
            while i < len(line):
                c = line[i]
                if c == '\\':
                    result += '\\'
                    i += 1
                    if i < len(line):
                        result += line[i]
                        i += 1
                elif c == '"':
                    result += c
                    i += 1
                    break
                elif c == '\n':
                    result += '\\n'
                    i += 1
                else:
                    result += c
                    i += 1
        else:
            result += c
            i += 1
    return result

def tag_c_code(code_lines):
    tagged_lines = []
    code_depth = 0
    block_stack = []
    
    collecting_stmt = False
    multi_line_stmt = []

    array_init = False       # 배열 초기화 중인지 여부
    array_init_stmt = []     # 배열 초기화 문장 버퍼
    array_decl = ""          # 배열 선언부 (= 앞부분)

    one_line_block_type = ""   # 한 줄 블록의 타입 (cond, rep)
    one_line_block_depth = 0   # 한 줄 블록의 깊이

    in_one_line_block = False

    multi_line_cond = []  # 여러 줄 조건문 저장용
    waiting_for_cond_end = False  # 조건문 끝나는지 추적

    pending_struct_close = False
    def flush_multiline_self():
        nonlocal multi_line_stmt
        if multi_line_stmt:
            tagged_lines.append(f"[self_start({code_depth})]")
            
            
            tagged_lines.extend(multi_line_stmt)
                
            tagged_lines.append(f"[self_end({code_depth})]")
            multi_line_stmt = []
    
    # 여러 줄의 조건문
    def flush_multiline_cond():
        nonlocal multi_line_cond
        if multi_line_cond:
            tagged_lines.append(f"[cond_start({code_depth})]")
            
            
            tagged_lines.extend(multi_line_cond)
                
            multi_line_cond = []

    # 배열 초기화 문장을 하나의 압축된 실행문으로 처리
    def flush_array_init():
        nonlocal array_init_stmt, array_init, array_decl
        if array_init_stmt:
            # 여러 줄의 배열 초기화를 하나의 문자열로 합치기
            init_text = " ".join([l.strip() for l in array_init_stmt])
            # 중괄호 내부의 내용 추출
            if '{' in init_text and '}' in init_text:
                inside_braces = init_text[init_text.find('{')+1:init_text.find('}')].strip()
                # 배열 내용을 깔끔하게 포맷팅
                formatted_init = f"{array_decl} = {{ {inside_braces} }};"
            else:
                # 중괄호가 없는 경우 원래 형식 유지
                formatted_init = f"{array_decl} = {init_text}"
            
            # 중복된 공백 제거
            formatted_init = re.sub(r'\s+', ' ', formatted_init).strip()
            # 결과 리스트에 추가
            tagged_lines.append(f"[self_start({code_depth})]")
            tagged_lines.append(formatted_init)
            tagged_lines.append(f"[self_end({code_depth})]")

            # 초기화
            array_init_stmt = []
            array_init = False
            array_decl = ""

    for line in code_lines:
        stripped = line.strip()

        # 배열 초기화 처리 중일 때
        if array_init:
            array_init_stmt.append(line)
            # 배열 초기화 종료 조건: 세미콜론이 나타나면
            if stripped.endswith(';'):
                flush_array_init()
                # 바로 여러 실행문 처리하지 않고 다음 로직으로 넘김
                continue
            # 아직 초기화 진행 중이면 다음 줄로
            continue

        # 빈 줄 처리
        if not stripped:
            tagged_lines.append(line)
            continue
        
        # 배열 초기화 감지: 변수 선언이 = 로 끝나고 다음 줄에 { 가 나타날 수 있음
        if stripped.endswith('='):
            # 배열 선언 패턴 확인 (예: int arr[100] = 또는 char *week[7] =)
            if re.search(r'\w[\w\s\*]*\s+\w+(\[\d*\])+\s*=$', stripped):
                array_init = True
                array_decl = stripped[:-1].strip()  # '=' 제외한 선언부 저장
                array_init_stmt = []  # 초기화 줄 저장할 리스트 초기화
                continue
        
        # 현재 줄이 배열 선언과 초기화를 포함하는 경우 (예: int arr[100] = {0};)
        elif '=' in stripped and '{' in stripped and '}' in stripped and ';' in stripped:
            if re.search(r'\w+\s+\w+\s*\[\d*\]\s*=\s*{', stripped):
                tagged_lines.append(line)
                continue
        




        # 멀티라인 실행문 수집 중일 때
        if collecting_stmt:
            multi_line_stmt.append(line)
            # 닫는 괄호가 더 많아지거나, ; 혹은 중괄호로 끝날 경우 종료로 판단
            if (stripped.endswith(';') or stripped.endswith('{') or stripped.endswith('}')) and \
               (multi_line_stmt[-1].count('(') <= multi_line_stmt[-1].count(')')):
                flush_multiline_self()
                collecting_stmt = False
            continue

        # 여러 줄의 조건문 처리 시
        elif waiting_for_cond_end:
            multi_line_cond.append(line)
            block_stack.append(('cond', code_depth))
            # 괄호 열고 닫힌 수 맞춰야 함
            open_parens = sum(l.count('(') for l in multi_line_cond)
            close_parens = sum(l.count(')') for l in multi_line_cond)

            if open_parens == close_parens:
                flush_multiline_cond()
                code_depth += 1
                waiting_for_cond_end = False
            continue

        # 배열 초기화 감지: 변수 선언이 = 로 끝나고 다음 줄에 { 가 나타날 수 있음
        elif stripped.endswith('='):
            # 배열 선언 패턴 확인 (예: int arr[100] = 또는 char *week[7] =)
            if re.search(r'\w[\w\s\*]*\[\d*\]\s*=$', stripped):
                array_init = True
                array_decl = stripped[:-1].strip()  # '=' 제외한 선언부 저장
                array_init_stmt = []  # 초기화 줄 저장할 리스트 초기화
                continue
        
        # 현재 줄이 배열 선언과 초기화를 포함하는 경우 (예: int arr[100] = {0};)
        elif '=' in stripped and '{' in stripped and '}' in stripped and ';' in stripped:
            if re.search(r'\w+\s+\w+\s*\[\d*\]\s*=\s*{', stripped):
                tagged_lines.append(f"[self_start({code_depth})]")
                tagged_lines.append(line)
                tagged_lines.append(f"[self_end({code_depth})]")
                continue

        # 한 줄 if/for/while 구문 감지 (예: if (n < 2) return 0;)
        elif (re.match(r'if\s*\(.*\)', stripped) or re.match(r'else if\s*\(.*\)', stripped) or 
            stripped == 'else' or re.match(r'for\s*\(.*\)', stripped) or 
            re.match(r'while\s*\(.*\)', stripped)) and not stripped.endswith('{'):
            
            # 세미콜론이 있고 중괄호가 없으면 한 줄 블록으로 간주
            if ';' in stripped and '{' not in stripped:
                # 조건문/반복문 부분과 실행문 부분 분리
                if 'if ' in stripped or 'else if ' in stripped:
                    cond_part = stripped[:stripped.find(')')+1]
                    stmt_part = stripped[stripped.find(')')+1:].strip()
                    block_type = 'cond'
                elif stripped == 'else':
                    cond_part = 'else'
                    stmt_part = stripped[4:].strip()
                    block_type = 'cond'
                elif 'for ' in stripped or 'while ' in stripped:
                    cond_part = stripped[:stripped.find(')')+1]
                    stmt_part = stripped[stripped.find(')')+1:].strip()
                    block_type = 'rep'
                    

                
                cond_part += '{'
                
                # 조건문/반복문 시작 태그 추가
                tagged_lines.append(f"[{block_type}_start({code_depth})]")
                tagged_lines.append(cond_part)
                
                # 실행문 부분 태그 처리
                if stmt_part:
                    tagged_lines.append(f"[self_start({code_depth+1})]")
                    tagged_lines.append(stmt_part)
                    tagged_lines.append(f"[self_end({code_depth+1})]")
                
                # 조건문/반복문 종료 태그 추가
                tagged_lines.append(f"[{block_type}_end({code_depth})]")
                tagged_lines.append('}')
                
                continue
            else:
                # 중괄호가 없고 세미콜론도 없는 경우: 다음 줄의 한 문장만 블록으로 처리하기 위해 플래그 설정
                if '{' not in stripped and ';' not in stripped:
                    in_one_line_block = True
                    if 'if ' in stripped or 'else if ' in stripped or stripped == 'else':
                        one_line_block_type = 'cond'
                    else:
                        one_line_block_type = 'rep'
                    code_depth += 1
                    one_line_block_depth = code_depth
                    

                    tagged_lines.append(f"[{one_line_block_type}_start({one_line_block_depth})]")
                    tagged_lines.append(line)
                    continue

        # 한 줄 블록 처리 중이고 처리할 문장을 만났을 때
        elif in_one_line_block and stripped.endswith(';'):
            tagged_lines.append(f"[self_start({one_line_block_depth + 1})]")
            tagged_lines.append(line)
            tagged_lines.append(f"[self_end({one_line_block_depth + 1})]")
            tagged_lines.append(f"[{one_line_block_type}_end({one_line_block_depth})]")
            code_depth = max(code_depth - 1, 0)
            in_one_line_block = False
            continue

    # 구조체 정의 시작
        if re.match(r'struct\s+\w+\s*{?', stripped):
            block_stack.append(('struct', 0))
            tagged_lines.append(f"[struct_def_start({code_depth})]")
            tagged_lines.append(line)
            pending_struct_close = False
            code_depth += 1
            continue

        # 한 줄에 정의된 구조체 (예: struct Foo { int x; int y; } bar;)
        elif re.match(r'struct\s+\w+\s*{.*}[^;]*;', stripped):
            tagged_lines.append(f"[struct_def_start({code_depth})]")
            struct_name = re.findall(r'struct\s+(\w+)', stripped)[0]
            content = stripped[stripped.find('{')+1:stripped.find('}')]
            tagged_lines.append(f"Struct {struct_name} {{")
            code_depth += 1

            for field in content.split(';'):
                field = field.strip()
                if field:
                    tagged_lines.append(f"[self_start({code_depth})]")
                    tagged_lines.append(f"   {field.strip()};")
                    tagged_lines.append(f"[self_end({code_depth})]")
            tagged_lines.append(f"[struct_def_end({code_depth})]}};")
            continue



        # 구조체 닫힘 마무리 줄 (;) 또는 이름;
        elif pending_struct_close and re.match(r'^\s*\w*\s*;', stripped):
            tagged_lines.append(f"[struct_def_end({code_depth})]")
            tagged_lines.append(line)
            block_stack.pop()
            pending_struct_close = False
            code_depth -= 1
            continue

        # 기존 구조체 닫힘 처리 유지
        elif re.match(r'}\s*\w+\s*;', stripped) and block_stack and block_stack[-1][0] == 'struct':
            tagged_lines.append(f"[struct_def_end({code_depth})]")
            block_stack.pop()
            tagged_lines.append(line)
            code_depth -= 1
            continue

        # 구조체 끝 (ex: };
        elif stripped == "};" and block_stack and block_stack[-1][0] == 'struct':
            tagged_lines.append(f"[struct_def_end({code_depth})]")
            block_stack.pop()
            tagged_lines.append(line)
            code_depth -= 1
            continue

        # 반복문 시작 감지 (예: while(), for())
        elif re.match(r'(while|for)\s*\(.*', stripped) and '{' in stripped:
            block_stack.append(('rep', code_depth))
            tagged_lines.append(f"[rep_start({code_depth})]")
            tagged_lines.append(line)
            code_depth += 1
            continue

        

        # 조건문 시작 감지
        elif re.match(r'if\s*\(.*', stripped) or re.match(r'else if\s*\(.*', stripped) or re.match(r'else\b', stripped):
            open_parens = stripped.count('(')
            close_parens = stripped.count(')')
            
            # 괄호 열고 닫힌 수가 다르면 멀티라인 조건문
            if open_parens > close_parens:
                waiting_for_cond_end = True
                multi_line_cond.append(line)
            else:
                block_stack.append(('cond', code_depth))
                tagged_lines.append(f"[cond_start({code_depth})]")
                tagged_lines.append(line)
                code_depth += 1
            continue

        # 함수 정의
        elif re.match(r'\w[\w\s\*]*\s+\w+\s*\([^)]*\)\s*{?', stripped):
            block_stack.append(('func', 0))
            tagged_lines.append(f"[func_def_start({code_depth})]")
            code_depth += 1
            tagged_lines.append(line)
            continue

        # 블록 종료 (ex: })
        elif stripped == "}":
            if block_stack:
                block_type, depth = block_stack.pop()
                code_depth = max(code_depth - 1, 0)
                if block_type == 'rep':
                    tagged_lines.append(f"[rep_end({code_depth})]")
                elif block_type == 'cond':
                    tagged_lines.append(f"[cond_end({code_depth})]")
                elif block_type == 'func':
                    tagged_lines.append(f"[func_def_end({code_depth})]")
                elif block_type == 'struct':
                    tagged_lines.append(f"[struct_def_end({code_depth})]")
                
            tagged_lines.append(line)
            continue
        #전처리기,주석
        elif stripped.startswith("#"):
            flush_multiline_self()
            tagged_lines.append(line)
            continue
        elif stripped.startswith("//"):
            continue

        # 실행문 감지
        elif stripped and not stripped.startswith("#") and not stripped.startswith("//") and not stripped.endswith("{") and not stripped == "}" and not stripped.endswith(":"):
            if not stripped.endswith(';') and '(' in stripped and not ')' in stripped:
                collecting_stmt = True
                multi_line_stmt.append(line)
                continue
            tagged_lines.append(f"[self_start({code_depth})]")
            tagged_lines.append(line)
            tagged_lines.append(f"[self_end({code_depth})]")
            continue

        # 그 외 라인
        tagged_lines.append(line)

    # 수집 중이던 문장이 남아있으면 flush
    flush_array_init()
    flush_multiline_self()

    return tagged_lines

def extract_code_from_tagged_list(tagged_list):
    # 리스트를 줄바꿈 문자열로 변환
    tagged_str = '\n'.join(tagged_list)

    # 줄 단위로 나누고 태그 제거
    lines = tagged_str.split('\n')
    clean_lines = [
        line for line in lines
        if not re.fullmatch(r'\[[^\[\]]+\]', line.strip())
    ]

    # 최종 문자열 반환
    return '\n'.join(clean_lines)

# LLM에게 줄 프롬프트
prompt = """
아래 문제 설명과 코드를 읽고, 문제 해결 과정을 다음과 같이 나누어주세요:

1. 과정별로 ===과정 이름===을 정하고, 
2. ===과정 이름=== 안에 해당하는 코드를 묶어서 보여주세요.
3. 코드의 작성 순서(위->아래)에 따라 진행하면 돼.
4. 함수 정의 역시 main문위에 있어도 하면 돼.
5. main함수를 제외한 나머지 함수들은 각각 ==="함수 이름" 함수 정의=== <- 이렇게 출력하면 돼.
6. 함수 정의 마다 각각의 과정으로 생각해.
7. #include <stdio.h>같은 헤더파일은 포함하지 말 것.

포맷은 다음을 따라주세요:


===과정 이름===
(해당 코드)

-----

===과정 이름===
(해당 코드)

마크업하지마세요.
아래의 예시를 참고하세요.

예시:

코드:
#include<stdio.h>

int print(int arr[][],int N) {

    for (int i = 0; i < N; i++) {
        for (int j = 0; j < N; j++) {
            printf("%d ", arr[i][j]);
        }
        printf("\n");
    }    
}

void inputArray(int A[][], int N)
{
    int i;
    int j;

    for (i = 0; i <= N; i = i + 1)
    {
        for (j = 0; j <= N; j = j + 1)
        {
            scanf("%d", &A[i][j]);
        }
    }
}

int main() {
    int N;
    scanf("%d", &N);

    int A[100][100]; 
    int B[100][100];
    int sum[100][100];

    inputArray(A,N)
    inputArray(B,N)
 
    for (int i = 0; i < N; i++) {
        for (int j = 0; j < N; j++) {
            sum[i][j] = A[i][j] + B[i][j];
        }
    }

    print(sum, N);

    return 0;
}

출력 예시:

===print 함수 정의===
int print(int arr[][], int N) {
    for (int i = 0; i < N; i++) {
        for (int j = 0; j < N; j++) {
            printf("%d ", arr[i][j]);
        }
    printf("\n");
    }
}

-----

===inputArray 입력 함수 정의===
void inputArray(int A[][], int N)
{
    int i;
    int j;

    for (i = 0; i <= N; i = i + 1)
    {
        for (j = 0; j <= N; j = j + 1)
        {
            scanf("%d", &A[i][j]);
        }
    }
}

===입력 받기===
int N;
scanf("%d", &N);

int A[100][100]; 
int B[100][100];

inputArray(A,N)
inputArray(B,N)

-----

===행렬 덧셈 수행===
int sum[100][100];

for (int i = 0; i < N; i++) {
    for (int j = 0; j < N; j++) {
        sum[i][j] = A[i][j] + B[i][j];
    }
}

-----

===결과 출력===
print(sum, N);

-----

===실행 종료===
return 0;

-----

"""


code_text = format_c_code(code)
code_lines = code_text.splitlines()
code_lines = join_broken_string_literals(code_lines)
code_lines = [escape_newlines_in_string_literals(line) for line in code_lines]
tagged = tag_c_code(code_lines)
normalized_code = extract_code_from_tagged_list(tagged)


print(normalized_code)
print("--------------")
# 요청 보내기
response = client.responses.create(
    model="gpt-4o-mini-2024-07-18",
    input=prompt + "\n\n" + problem_description + "\n\n" + normalized_code
)

print(response.output_text)

start_numbers = []
end_numbers = []

def number_tagged_blocks_verbose(tagged_code: str) -> str:
    lines = tagged_code.splitlines()
    tagged_blocks = []

    current_tag = None
    current_block = []

    for line in lines:
        stripped = line.strip()
        if stripped.startswith('===') and stripped.endswith('==='):
            if current_tag is not None:
                tagged_blocks.append((current_tag, current_block))
            current_tag = stripped
            current_block = []
        elif stripped == '' or stripped == '}' or stripped.startswith('#include') or stripped.startswith('#define'):
            continue
        else:
            current_block.append(line)

    if current_tag is not None:
        tagged_blocks.append((current_tag, current_block))

    output = []
    line_number = 1
    main_started = False  # main 함수 내부 코드 진입 여부

    for index, (tag, block) in enumerate(tagged_blocks):
        is_function_def = tag.endswith('함수 정의===')

        if not is_function_def and not main_started:
            line_number += 1  # main 함수 블록 시작 시 한 줄 건너뜀
            main_started = True

        start_line = line_number
        if index == len(tagged_blocks) - 1:
            line_number += 1    
        end_line = line_number + len(block) - 2
        output.append(f"{tag}\n시작번호 : {start_line}\n끝 번호 : {end_line}")
        start_numbers.append(start_line)
        end_numbers.append(end_line)
        line_number = end_line + 1

    return '\n\n'.join(output)

print("=================")
print(number_tagged_blocks_verbose(response.output_text))
print("=================")

import re
import os
from graphviz import Digraph

# 텍스트에 담긴 내용 (예시 response.output_text)
text = response.output_text

# [태그] 추출
tags = re.findall(r"\===(.*?)\===", text)

# 함수 정의와 메인 태그로 분리
func_tags = [tag for tag in tags if "함수 정의" in tag]
main_tags = [tag for tag in tags if "함수 정의" not in tag]

# 모든 노드들을 식별
func_nodes = [f"func{i}" for i in range(len(func_tags))]
main_nodes = [f"main{i}" for i in range(len(main_tags))]
all_nodes = func_nodes + main_nodes  # 강조 대상으로 사용


# 디렉토리 생성
output_dir = "flowcharts"
os.makedirs(output_dir, exist_ok=True)

# MySQL 연결 설정
conn = mysql.connector.connect(
    host="localhost",
    user="hustoj",
    password="JGqRe4pltka5e5II4Di3YZdmxv7SGt",
    database="jol"
)
cursor = conn.cursor()

# 흐름도 생성 및 DB 저장
for index, highlight_node in enumerate(all_nodes):
    dot = Digraph(comment=f"Highlight: {highlight_node}", format="png")
    dot.attr(fontname="Malgun Gothic", rankdir="TB")  # 위에서 아래

    print(start_numbers[index])
    print(end_numbers[index])

    with dot.subgraph(name="cluster_all") as g:
        g.attr(label="", color="white", fontname="Malgun Gothic", rankdir="TB")  # 큰 클러스터

        with g.subgraph(name="cluster_func") as f:
            f.attr(label="Function Definition", style="dashed,filled", color="blue",
                fontname="Malgun Gothic", fillcolor="lightyellow")
            
            previous_func = None
            for i, tag in enumerate(func_tags):
                node_id = f"func{i}"
                print(tag)
                print(node_id)
                print(i)
                style = 'bold' if node_id == highlight_node else ''
                color = 'red' if node_id == highlight_node else 'black'
                f.node(node_id, f"{tag}", shape="box", fontname="Malgun Gothic",
                    color=color, penwidth='3' if node_id == highlight_node else '1', style=style)
                if previous_func:
                    f.edge(previous_func, node_id, style="invis")
                previous_func = node_id

        with g.subgraph(name="cluster_main") as m:
            m.attr(label="Main Flow", style="dashed,filled", color="black", fontname="Malgun Gothic", fillcolor="white")

            m.node("start", "Start", shape="ellipse", fillcolor="lightblue", fontname="Malgun Gothic")
            prev = "start"
            for i, tag in enumerate(main_tags):
                node_id = f"main{i}"
                style = 'bold' if node_id == highlight_node else ''
                color = 'red' if node_id == highlight_node else 'black'
                m.node(node_id, f"{tag}", shape="box", fontname="Malgun Gothic",
                    color=color, penwidth='3' if node_id == highlight_node else '1', style=style)
                m.edge(prev, node_id)
                prev = node_id

            m.node("end", "End", shape="ellipse", fillcolor="lightblue", fontname="Malgun Gothic")
            m.edge(prev, "end")

    # (선택) func 마지막 노드 → start 에 invisible edge 추가로 더 명확히 수직 정렬
    dot.edge(f"func{len(func_tags)-1}", "start", style="invis", weight="100")

    filename = os.path.join(output_dir, f"{problem_id}_{index + 1}")
    output_path = dot.render(filename, cleanup=True)

    # DB에 INSERT
    cursor.execute("""
        INSERT INTO flowchart (problem_id, png_address, png_number, start_num, end_num)
        VALUES (%s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE png_address = VALUES(png_address)
    """, (problem_id, f"/home/Capstone_Design_Troy/test/flowcharts/{problem_id}_{index+1}", index+1, start_numbers[index], end_numbers[index]))
    conn.commit()

# # 마무리
cursor.close()
conn.close()