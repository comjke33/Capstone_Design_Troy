document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});

// 피드백 이미지를 문제 영역 위치에 따라 이동하는 함수
function moveFeedbackImage(index) {
    const feedbackImage = document.getElementById('feedback-img');
    const targetTextarea = document.getElementById(`ta_${index}`);
    if (!targetTextarea) return;

    // 문제 영역의 위치를 기준으로 이동
    const targetRect = targetTextarea.getBoundingClientRect();
    
    // 피드백 이미지를 해당 문제의 위치로 이동
    feedbackImage.style.left = `${targetRect.left + window.scrollX - 100}px`;  // 100px은 이미지의 너비
    feedbackImage.style.top = `${targetRect.top + window.scrollY - 100}px`;   // 100px은 이미지의 높이
}

// 답안 제출 함수
function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#d4edda";
        ta.style.border = "1px solid #d4edda";
        ta.style.color = "#155724";
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

        // 피드백 이미지 위치 이동
        moveFeedbackImage(index);
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
        updateFeedback(index, false, input);
    }
}

function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return;

    const answerArea = document.getElementById(`answer_area_${index}`);
    const feedbackArea = document.getElementById(`feedback_area_${index}`);
    const answerHtml = `
        <strong>정답:</strong><br>
        <pre class='code-line'>${correctCode}</pre>
    `;

    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';

    // 피드백을 textarea 아래로 표시
    feedbackArea.innerHTML = `
        <strong>피드백:</strong><br>
        <pre class='feedback-line'>정답을 제출하셨습니다!</pre>
    `;
    feedbackArea.style.display = 'block';
}
