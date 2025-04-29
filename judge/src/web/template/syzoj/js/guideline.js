// 정답 확인 및 제출 기능
const correctAnswers = [];

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    // 사용자가 제출한 코드와 정답 코드 비교
    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#d4edda";  // 연한 초록색 배경
        ta.style.border = "1px solid #d4edda";  // 연한 초록색 테두리
        ta.style.color = "#155724";             // ✅ 진한 초록색 글자 추가
        btn.style.display = "none";
        check.style.display = "inline";
        updateFeedback(index, true, input);

        const nextIndex = index + 1;
        const nextTa = document.getElementById(`ta_${nextIndex}`);
        const nextBtn = document.getElementById(`btn_${nextIndex}`);
        if (nextTa && nextBtn) {
            nextTa.disabled = false;
            nextBtn.disabled = false;
            nextTa.focus();
            nextTa.addEventListener('input', () => autoResize(nextTa));
        }
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
        updateFeedback(index, false, input);
    }
}

// 답안 확인 버튼 클릭 시 정답을 textarea 아래에 표시
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return; // 정답 없으면 리턴

    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `
        <strong>정답:</strong><br>
        <pre class='code-line'>${correctCode}</pre>
    `;

    // 정답을 표시하고, 표시된 영역을 보이도록 설정
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';  // 정답을 보이도록 설정
}

function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});
