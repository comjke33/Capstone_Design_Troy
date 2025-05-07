import sys
from transformers import AutoTokenizer, AutoModel
import torch
from sklearn.metrics.pairwise import cosine_similarity

# CodeBERT 모델 로딩
tokenizer = AutoTokenizer.from_pretrained("microsoft/codebert-base")
model = AutoModel.from_pretrained("microsoft/codebert-base")

# 의미 태그 후보 정의
semantic_tags = {
    "입력 처리": "Read input from user using scanf.",
    "출력 처리": "Print results using printf.",
    "조건 처리": "Use if statements for conditional logic.",
    "계산 처리": "Perform arithmetic operations or updates.",
    "종료 처리": "Return from the main function.",
    "변수 선언": "Declare and initialize variables.",
    "기타": "Other uncategorized logic."
}

def embed_text(text):
    tokens = tokenizer(text, return_tensors="pt", truncation=True, max_length=512)
    with torch.no_grad():
        output = model(**tokens)
    return output.last_hidden_state.mean(dim=1).numpy()  # 전체 시퀀스 평균값

def classify_block_with_bert(block_code):
    block_vec = embed_text(block_code)
    best_tag = "기타"
    best_score = -1

    for tag, desc in semantic_tags.items():
        desc_vec = embed_text(desc)
        score = cosine_similarity(block_vec, desc_vec)[0][0]
        if score > best_score:
            best_score = score
            best_tag = tag

    return f"==={best_tag}===", block_code

# 예제 코드 블록들
example_blocks = [
    "scanf(\"%d %d\", &h, &m);",
    "printf(\"%d %d\\n\", h, m);",
    "if (m >= 60) { h += m / 60; m = m % 60; }",
    "int h, m;",
    "return 0;"
]

# 처리
for block in example_blocks:
    tag, code = classify_block_with_bert(block)
    print(tag)
    print(code)
    print()