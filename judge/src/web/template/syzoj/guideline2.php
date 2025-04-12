<style>
    /* 전체 컨테이너 스타일 */
    .ui.container {
        margin-top: 20px;
    }

    /* 그리드 컨테이너 */
    .ui.two.column.stackable.grid {
        display: flex;
        gap: 20px;
    }

    /* 왼쪽 코드 작성 영역 스타일 */
    .column.ten.wide {
        width: 65%;
    }

    /* 오른쪽 피드백 영역 스타일 */
    .column.six.wide {
        width: 30%;
        background-color: #d3e6f9;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* 각 코드 블록 스타일 */
    .code-block {
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f4f4f9;
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

    /* 텍스트 영역 스타일 */
    textarea {
        width: 100%;
        height: 120px;
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

    .ui.segment h5 {
        font-weight: bold;
        font-size: 18px;
        color: #1e88e5;
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
