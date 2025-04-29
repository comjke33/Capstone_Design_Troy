<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout" style="display: flex; justify-content: space-between;">
    <!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
    <div class="left-panel" style="flex: 1; padding-right: 10px;" id="guideline-content">
        <!-- 동적으로 가이드라인 내용이 여기 들어갑니다. -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 동적으로 생성된 요소에 이벤트 리스너를 추가하는 함수
    function addEventListeners() {
        // 동적으로 생성된 submit 버튼에 대해 이벤트 리스너 추가
        document.querySelectorAll('.submit-button').forEach(button => {
            button.addEventListener('click', function(event) {
                const index = event.target.getAttribute('data-index');
                submitAnswer(index);
            });
        });

        // 동적으로 생성된 showAnswer 버튼에 대해 이벤트 리스너 추가
        document.querySelectorAll('.view-button').forEach(button => {
            button.addEventListener('click', function(event) {
                const index = event.target.getAttribute('data-index');
                showAnswer(index);
            });
        });
    }

    // 파일 로딩 함수
    function loadStep(step) {
        fetch(`guideline${step}.php`)  // guideline1.php, guideline2.php, guideline3.php를 동적으로 불러옴
            .then(res => res.text())
            .then(html => {
                const content = document.getElementById("guideline-content");
                content.innerHTML = html;
                addEventListeners(); // 동적으로 추가된 요소에 대해 이벤트 리스너 추가
                window.history.pushState(null, "", `?step=${step}`);  // URL에 step 파라미터 추가
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                console.error("가이드라인 로딩 오류:", error);
            });
    }

    // 버튼 클릭 이벤트
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const step = btn.dataset.step;
            loadStep(step);
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

// 정답 확인 및 제출 기능
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

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
</script>

<?php include("template/syzoj/footer.php"); ?>
