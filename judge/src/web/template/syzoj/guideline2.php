<style>
   /* 전체 코드 컨테이너 스타일 */
.code-container {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    padding: 20px;
    background-color: #f9f9f9;
    margin: 0 auto;
    max-width: 1000px;
}

/* 각 코드 블록의 기본 스타일 */
.code-block {
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-sizing: border-box;
}

/* 함수 블록 스타일 */
.code-block.function {
    background-color: #e0f7fa; /* 연한 파란색 */
}

/* 반복문 블록 스타일 */
.code-block.loop {
    background-color: #fce4ec; /* 연한 분홍색 */
}

/* 조건문 블록 스타일 */
.code-block.conditional {
    background-color: #e8f5e9; /* 연한 초록색 */
}

/* self-contained 블록 스타일 */
.code-block.self-block {
    background-color: #fff9c4; /* 연한 노란색 */
}

/* 구조체 블록 스타일 */
.code-block.struct {
    background-color: #ffecb3; /* 연한 황토색 */
}

/* 코드 제목 스타일 */
h3 {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}

/* 텍스트 영역 스타일 (textarea) */
textarea {
    width: 100%;
    height: 150px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fafafa;
    font-family: monospace;
    font-size: 14px;
    color: #333;
    box-sizing: border-box;
}

/* 제출 버튼 스타일 */
.ui.blue.button {
    margin-top: 20px;
}

/* 피드백 안내 영역 스타일 */
.ui.info.message {
    background-color: #e3f2fd;
    padding: 15px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* 피드백 설명 */
.ui.segment p {
    font-size: 14px;
    color: #555;
}

/* 반응형 디자인 */
@media screen and (max-width: 768px) {
    .column.ten.wide, .column.six.wide {
        width: 100% !important;
    }
}

</style>
