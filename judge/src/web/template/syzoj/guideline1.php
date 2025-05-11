<?php
include("template/$OJ_TEMPLATE/header.php");
include("../../guideline_common.php");
?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'></div>
<link rel="stylesheet" href="/template/syzoj/css/guideline.css">



<!-- 상단 툴바 -->
<div class="top-toolbar">
  <!-- 뒤로가기 및 리셋 버튼 -->
  <div class="action-buttons">
        <div class="back-button">
            <button class="ui button back" id="view-problem-button">↩</button>
        </div>
  </div>
    
  <!-- Step1,2,3 buttons -->
  <div class="step-buttons">
    <button class="ui button" data-step="1" data-problem-id="<?= htmlspecialchars($problem_id) ?>">Step 1</button>
    <button class="ui button" data-step="2" data-problem-id="<?= htmlspecialchars($problem_id) ?>">Step 2</button>
    <button class="ui button" data-step="3" data-problem-id="<?= htmlspecialchars($problem_id) ?>">Step 3</button>
  </div>

  <div class="action-buttons">
    <div class="reset-button">
        <button class="ui button again" id="reset-button">↻</button>
    </div>
  </div>
</div>


<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
        <img id="flowchart_image" src="../../image/basic.png">
    </div>


    <!-- 가운데 패널 -->
    <div class="center-panel">
        <h1>한 줄씩 풀기</h1>
        <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>

        <?php      
        function render_tree_plain($blocks, &$answer_index = 0) {
            $html = "";
        
            foreach ($blocks as $block) {
                $depth = $block['depth'];
                $margin_left = $depth * 30;
        
                if ($block['type'] === 'text') {
                    $raw = trim($block['content']);
                    if ($raw === '') continue;
        
                    $line = htmlspecialchars($block['content']);
                    $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
                    $disabled = $has_correct_answer ? "" : "disabled";
        
                    $html .= "<div class='submission-line' style='margin-left: {$margin_left}px;'>";
                    $html .= "<div class='code-line'>{$line}</div>";
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
        
                    // 🔥 버튼 항상 표시
                    $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                    $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>";
        
                    $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                    $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                    $html .= "</div>";
        
                    $answer_index++;
                } else if (isset($block['children']) && is_array($block['children'])) {
                    $html .= render_tree_plain($block['children'], $answer_index);
                }
            }
        
            return $html;
        }     

        $answer_index = 0;
        echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <!-- 오른쪽 패널 -->
    <div class="right-panel">
        <h2>📋 피드백 창</h2>
    </div>
</div>

<script>

//뒤로가기 & 다시 풀기 버튼
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = urlParams.get("step") || "1";
    const problemId = urlParams.get("problem_id") || "0";

    // 문제 가기 버튼
    document.getElementById("view-problem-button")?.addEventListener("click", () => {
        window.location.href = `/problem.php?id=${problemId}`;
    });

    // 다시 풀기 버튼
    document.getElementById("reset-button")?.addEventListener("click", () => {
        if (confirm("모든 입력을 초기화하고 다시 푸시겠습니까?")) {
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                // localStorage에서 삭제
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                const statusKey = `answer_status_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.removeItem(key);
                localStorage.removeItem(statusKey);

                // 시각적 스타일 리셋
                textarea.value = "";
                textarea.readOnly = false;
                textarea.disabled = false;
                textarea.style.backgroundColor = "white";
                textarea.style.border = "1px solid #ccc";
                textarea.style.color = "black";

                // 버튼/체크 아이콘 리셋
                const check = document.getElementById(`check_${index}`);
                const btn = document.getElementById(`btn_${index}`);
                const viewBtn = document.getElementById(`view_btn_${index}`);
                const answerArea = document.getElementById(`answer_area_${index}`);

                if (check) check.style.display = "none";
                if (btn) {
                    btn.style.display = "inline-block";
                    btn.disabled = false;
                }
                if (viewBtn) viewBtn.disabled = false;
                if (answerArea) answerArea.style.display = "none";
            });
        }
    });
});


//Step1, 2, 3버튼 부분
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = urlParams.get("step") || "1";
    const problemId = urlParams.get("problem_id") || "0";

    const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

    document.querySelectorAll("textarea").forEach((textarea, index) => {
        const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
        const statusKey = `answer_status_step${currentStep}_q${index}_pid${problemId}`;
        const savedValue = localStorage.getItem(key);
        const savedStatus = localStorage.getItem(statusKey);

        if (savedValue !== null) {
            textarea.value = savedValue;
        }

        if (savedStatus === "correct") {
            // ✅ 이전에 정답 제출한 경우 스타일 복원
            textarea.readOnly = true;
            textarea.style.backgroundColor = "#d4edda";
            textarea.style.border = "1px solid #d4edda";
            textarea.style.color = "#155724";
            const checkMark = document.getElementById(`check_${index}`);
            if (checkMark) checkMark.style.display = "inline";
        }

        textarea.addEventListener("input", () => {
            localStorage.setItem(key, textarea.value);
        });
    });

    // 버튼 클릭 시 저장 후 이동
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const nextStep = btn.getAttribute("data-step");
            const nextProblemId = btn.getAttribute("data-problem-id") || problemId;

            document.querySelectorAll("textarea").forEach((textarea, index) => {
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.setItem(key, textarea.value);
            });

            const baseUrl = window.location.pathname;
            window.location.href = `${baseUrl}?step=${nextStep}&problem_id=${nextProblemId}`;
        });
    });
});

//문제 맞았는지 여부 확인
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($problem_id) ?>

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();
    const step = new URLSearchParams(window.location.search).get("step") || "1";
    const problemId = new URLSearchParams(window.location.search).get("problem_id") || "0";
    const key = `answer_status_step${step}_q${index}_pid${problemId}`;

    if (input === correct) {
        // ✅ 저장
        localStorage.setItem(key, "correct");

        ta.readOnly = true;
        ta.style.backgroundColor = "#d4edda";
        ta.style.border = "1px solid #d4edda";
        ta.style.color = "#155724";
        btn.style.display = "none";
        check.style.display = "inline";

        const nextIndex = index + 1;
        const nextTa = document.getElementById(`ta_${nextIndex}`);
        const nextBtn = document.getElementById(`btn_${nextIndex}`);
        if (nextTa && nextBtn) {
            nextTa.disabled = false;
            nextBtn.disabled = false;
            nextTa.focus();
        }
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
    }
}

//답안 보여주기
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();  // 정답 추출
    if (!correctCode) return;

    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${correctCode}</pre>`;
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';
}


//화면 크기 재조절
function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}

let currentTextarea = null;
let animationRunning = false;

//flowchart렌더링 및 매끄러운 이동
function updateImageForTextarea(index, ta) {
    currentTextarea = ta;
    fetch(`../../get_flowchart_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            let img = document.getElementById("flowchart_image");
            if (!img) {
                // img = document.createElement("img");
                // img.id = "flowchart_image";
                // document.body.appendChild(img);
            }

            img.src = data.url;
            console.log("서버 디버그 데이터:", data.debug);

            if (!animationRunning) {
                animationRunning = true;
                smoothFollowImage(); // 따라오기 시작
            }
        });
}

//줄번호에 맞춰서 이미지 fetch
// 줄 번호에 따라 이미지 fetch
function fetchImageByLineNumber(lineNumber) {
    const problemId = <?= json_encode($problem_id) ?>;
    const ta = document.querySelectorAll("textarea[id^='ta_']")[lineNumber];
    if (!ta) return;

    currentTextarea = ta; // ⭐️ 반드시 설정
    const img = document.getElementById("flowchart_image");

    fetch(`/get_flowchart_image.php?problem_id=${problemId}&index=${lineNumber}`)
        .then(res => res.json())
        .then(data => {
            if (data.url && data.url.trim() !== "") {
                img.src = data.url;
                img.style.display = "block";

                if (!animationRunning) {
                    animationRunning = true;
                    smoothFollowImage();
                }
            } else {
                img.style.display = "none";
                animationRunning = false;
            }
        })
        .catch(err => {
            console.error("이미지 로딩 실패:", err);
            img.style.display = "none";
            animationRunning = false;
        });
}

// 이미지 따라다니기
function smoothFollowImage() {
    const img = document.getElementById("flowchart_image");
    if (!img || !currentTextarea) {
        animationRunning = false;
        return;
    }

    const taRect = currentTextarea.getBoundingClientRect();
    const scrollY = window.scrollY || document.documentElement.scrollTop;
    const taTop = taRect.top + scrollY;

    const targetTop = taRect.top + scrollY + 100;
    const currentTop = parseFloat(img.style.top) || (img.getBoundingClientRect().top + scrollY);
    const nextTop = currentTop + (targetTop - currentTop) * 0.2;

    img.style.top = `${nextTop}px`;

    requestAnimationFrame(smoothFollowImage);
}

// textarea 클릭 시 트리거
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
        ta.addEventListener("focus", () => fetchImageByLineNumber(idx));
    });
});

</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>