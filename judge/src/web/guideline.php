<?php
include("include/db_info.inc.php");  // DB 및 전역변수 (e.g. $OJ_CORRECT_ANSWERS)

// 정답 데이터를 JS에 넘기기 위해 json_encode
$correctAnswersJS = json_encode($OJ_CORRECT_ANSWERS);
?>

<script>
const correctAnswers = <?= $correctAnswersJS ?>;

function initDynamicFeatures() {
    document.querySelectorAll('.submit-button').forEach((btn, index) => {
        btn.addEventListener("click", function () {
            submitAnswer(index);
        });
    });

    document.querySelectorAll('.view-button').forEach((btn, index) => {
        btn.addEventListener("click", function () {
            showAnswer(index);
        });
    });

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
        const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${correctCode}</pre>`;
        answerArea.innerHTML = answerHtml;
        answerArea.style.display = 'block';
    }

    function autoResize(ta) {
        ta.style.height = 'auto';
        ta.style.height = ta.scrollHeight + 'px';
    }

    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
}
</script>
