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
    <button class="ui button" data-step="1" data-problem-id="<?= htmlspecialchars($problem_id) ?>">기초</button>
    <button class="ui button" data-step="2" data-problem-id="<?= htmlspecialchars($problem_id) ?>">실전</button>
    <button class="ui button" data-step="3" data-problem-id="<?= htmlspecialchars($problem_id) ?>">심화</button>
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
        <!-- <img id="flowchart_image"> -->
    </div>


    <!-- 가운데 패널 -->
    <div class="center-panel">
        <h1>심화 풀기</h1>

        <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
        <br>
        <br>

        <?php      
             function render_tree_plain($blocks, &$answer_index = 0) {
            $html = "";

            foreach ($blocks as $block) {
                $depth = $block['depth'];
                // $margin_left = $depth * 50;
                $isCorrect = false;

                if ($block['type'] === 'text') {
                    $raw = trim($block['content']);
                    if ($raw === '') continue;

                    // 디버깅용 주석 추가 (View Source에서 확인)
                    $html .= "<!-- DEBUG raw line [{$answer_index}]: " . htmlentities($raw) . " -->\n";

                    // 출력 시 안전하게 이스케이프 처리 (중복 방지)
                    $escaped_line = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');

                    $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
                    $disabled = $has_correct_answer ? "" : "disabled";

                    // 출력 영역
                    $html .= "<div class='submission-line' style='margin-left: {$margin_left}px;'>";

                    // 코드 출력 라인
                    $html .= "<div class='code-line'>{$escaped_line}</div>";

                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$default_value}</textarea>";


                    // 버튼 출력
                    if(!$isCorrect){
                        $html .= "<button onclick='showAnswer({$answer_index})' id='answer_btn_{$answer_index}' class='answer-button'>답안 확인</button>";
                    }

                    // 피드백 영역 + 정답 표시
                    $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                    $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                    $html .= "</div>";

                    $answer_index++;
                } 
                else if (isset($block['children']) && is_array($block['children'])) {
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
    <div class="right-panel" style="display:none;">

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

//textarea 입력 줄에 따라 높이 조절
document.addEventListener("DOMContentLoaded", function () {
    const textareas = document.querySelectorAll(".styled-textarea");

    textareas.forEach((ta) => {
        autoResize(ta); // 초기 렌더링 시 높이 조정

        // 입력할 때마다 높이 자동 조정
        ta.addEventListener("input", () => autoResize(ta));
    });

    function autoResize(textarea) {
        textarea.style.height = "auto"; // 초기화
        textarea.style.height = textarea.scrollHeight + "px"; // 내용에 따라 높이 설정
    }
});

// 문제 맞았는지 여부 확인
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($problem_id) ?>

//문제가 되는 특수문자 치환
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

//답안 보여주기
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content?.trim();
    const answerArea = document.getElementById(`answer_area_${index}`);

    if (!correctCode) {
        answerArea.innerHTML = "<em>정답이 등록되지 않았습니다.</em>";
        answerArea.style.display = 'block';
        return;
    }

    // 이미 표시 중이면 숨기기 (toggle 기능)
    if (answerArea.style.display === 'block') {
        answerArea.style.display = 'none';
        return;
    }

    const escapedCode = escapeHtml(correctCode);
    const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${escapedCode}</pre>`;
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

</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>