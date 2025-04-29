<?php include("template/syzoj/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content">
        <!-- 여기에 동적으로 guideline1/2/3.php의 결과가 삽입됩니다 -->
    </div>
</div>

<script>
// JS 코드 직접 삽입

// JS 코드에서 fetch를 사용하여 PHP 파일로부터 데이터를 가져옵니다.
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    // 파일 로딩 함수 (step에 해당하는 guideline1.php, guideline2.php, guideline3.php를 불러옴)
    function loadStep(step) {
        fetch(`guideline1.php?step=${step}`)  // PHP 파일에서 데이터를 가져옵니다.
            .then(res => res.json())  // JSON 형식으로 응답을 받습니다.
            .then(data => {
                content.innerHTML = data.content;  // 반환된 content를 HTML에 삽입
                window.history.pushState(null, "", `?step=${step}`);  // URL에 step 파라미터 추가
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                console.error("가이드라인 로딩 오류:", error);
            });
    }

    // 버튼 클릭 이벤트
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active")); // 기존 활성화된 버튼 비활성화
            btn.classList.add("active"); // 클릭된 버튼을 활성화

            const step = btn.dataset.step; // 클릭된 버튼의 data-step 값
            loadStep(step); // 해당 step에 맞는 가이드라인 로드
        });
    });

    // URL에 step이 이미 있으면 그 값을 사용하여 초기화, 없으면 기본 1로 설정
    const urlParams = new URLSearchParams(window.location.search);
    const initialStep = urlParams.get('step') || 1;
    loadStep(initialStep); // 초기 step 로드

    // 버튼 활성화도 초기 상태 반영
    buttons.forEach(btn => {
        if (btn.dataset.step == initialStep) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
});


// 제출 및 답안 확인 함수

const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>; // 정답 코드 배열 (PHP에서 제공)

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

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

function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return; // 정답 없으면 리턴

    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `
        <strong>정답:</strong><br>
        <pre class='code-line'>${correctCode}</pre>
    `;

    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';
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
</script>

<?php include("template/syzoj/footer.php"); ?>
