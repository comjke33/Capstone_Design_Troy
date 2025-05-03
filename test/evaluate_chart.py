from clang import cindex
import sys

# libclang 경로 수동 지정 필요 시 (필요한 경우만)
cindex.Config.set_library_file('/usr/lib/llvm-17/lib/libclang-17.so.17')

def extract_code_block(extent):
    """extent 범위를 기준으로 코드 블록 문자열 추출"""
    with open(extent.start.file.name, 'r') as f:
        lines = f.readlines()
    return ''.join(lines[extent.start.line - 1:extent.end.line]).strip()


def classify_block(nodes):
    """노드 리스트를 받아 의미 태그 및 코드 블록을 리턴"""
    if all(n.kind == cindex.CursorKind.CALL_EXPR and n.spelling == 'scanf' for n in nodes):
        return "===입력 블록===", extract_code_block(nodes[0].extent)
    elif all(n.kind == cindex.CursorKind.CALL_EXPR and n.spelling == 'printf' for n in nodes):
        return "===출력 블록===", extract_code_block(nodes[0].extent)
    elif any(n.kind == cindex.CursorKind.IF_STMT for n in nodes):
        return "===조건 기반 계산 블록===", extract_code_block(nodes[0].extent)
    elif any(n.kind == cindex.CursorKind.BINARY_OPERATOR for n in nodes):
        return "===계산 블록===", extract_code_block(nodes[0].extent)
    elif any(n.kind == cindex.CursorKind.RETURN_STMT for n in nodes):
        return "===종료 블록===", extract_code_block(nodes[0].extent)
    elif any(n.kind == cindex.CursorKind.DECL_STMT for n in nodes):
        return "===변수 선언 블록===", extract_code_block(nodes[0].extent)
    else:
        return "===기타 블록===", extract_code_block(nodes[0].extent)


def analyze_semantic_blocks(filename):
    """C 파일을 의미 블록으로 분석"""
    index = cindex.Index.create()
    tu = index.parse(filename)

    semantic_blocks = []

    for node in tu.cursor.get_children():
        if node.kind == cindex.CursorKind.FUNCTION_DECL and node.spelling == "main":
            for c in node.get_children():
                if c.kind == cindex.CursorKind.COMPOUND_STMT:
                    buffer = []
                    for stmt in c.get_children():
                        if stmt.kind in [
                            cindex.CursorKind.CALL_EXPR,
                            cindex.CursorKind.BINARY_OPERATOR,
                            cindex.CursorKind.IF_STMT,
                            cindex.CursorKind.DECL_STMT,
                            cindex.CursorKind.RETURN_STMT
                        ]:
                            buffer.append(stmt)
                        else:
                            if buffer:
                                tag, code = classify_block(buffer)
                                semantic_blocks.append((tag, code))
                                buffer = []
                    if buffer:
                        tag, code = classify_block(buffer)
                        semantic_blocks.append((tag, code))
    return semantic_blocks

print("시작됨됨")

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("사용법: python analyze_semantic_blocks.py example.c")
        sys.exit(1)

    print("여기까진 됨됨")

    filename = sys.argv[1]
    results = analyze_semantic_blocks(filename)

    for tag, code in results:
        print(tag)
        print(code)
        print()