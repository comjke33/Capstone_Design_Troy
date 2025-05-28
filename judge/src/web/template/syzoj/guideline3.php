<?php
include("template/$OJ_TEMPLATE/header.php");
include("../../guideline_common.php");
?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'></div>
<link rel="stylesheet" href="/template/syzoj/css/guideline3.css">

<!-- 상단 툴바 -->
<div class="top-toolbar">
  <div class="action-buttons">
    <div class="back-button">
      <button class="ui button back" id="view-problem-button">↩</button>
    </div>
  </div>

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
    <h1>심화 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
    <br><br>

    <?php
    function render_guideline_only($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                $raw = trim($block['content']);
                if ($raw === '') continue;
                $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
                $disabled = $has_correct_answer ? "" : "disabled";
                $html .= "<div class='submission-line'>";
                $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
                $html .= "<button onclick='showAnswer({$answer_index})' id='answer_btn_{$answer_index}' class='answer-button'>답안 확인</button>";
                $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                $html .= "</div>\n";
                $answer_index++;
            } elseif (isset($block['children']) && is_array($block['children'])) {
                $html .= render_guideline_only($block['children'], $answer_index);
            }
        }
        return $html;
    }

    echo render_guideline_only($OJ_BLOCK_TREE);
    ?>
  </div>

  <!-- 오른쪽 패널 (미사용 시 display:none) -->
  <div class="right-panel" style="display:none;">
    <?php
    function render_code_only($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                $raw = trim($block['content']);
                if ($raw === '') continue;
                $escaped_line = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
                $html .= "<div class='code-line'>{$escaped_line}</div>\n";
                $answer_index++;
            } elseif (isset($block['children']) && is_array($block['children'])) {
                $html .= render_code_only($block['children'], $answer_index);
            }
        }
        return $html;
    }
    ?>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = urlParams.get("step") || "1";
    const problemId = urlParams.get("problem_id") || "0";

    // 문제 보기 버튼
    document.getElementById("view-problem-button")?.addEventListener("click", () => {
        window.location.href = `/problem.php?id=${problemId}`;
    });

    // 초기화 버튼
    document.getElementById("reset-button")?.addEventListener("click", () => {
        if (confirm("모든 입력을 초기화하고 다시 푸시겠습니까?")) {
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                const statusKey = `answer_status_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.removeItem(key);
                localStorage.removeItem(statusKey);
                textarea.value = "";
                textarea.readOnly = false;
                textarea.disabled = false;
                textarea.style.backgroundColor = "white";
                textarea.style.border = "1px solid #ccc";
                textarea.style.color = "black";
                document.getElementById(`check_${index}`)?.style.display = "none";
                document.getElementById(`answer_area_${index}`)?.style.display = "none";
            });
        }
    });

    // 입력 자동 저장
    document.querySelectorAll("textarea").forEach((textarea, index) => {
        const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
        const savedValue = localStorage.getItem(key);
        if (savedValue !== null) {
            textarea.value = savedValue;
        }
        textarea.addEventListener("input", () => {
            localStorage.setItem(key, textarea.value);
        });
    });

    // Step 버튼 동작
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const nextStep = btn.getAttribute("data-step");
            const nextProblemId = btn.getAttribute("data-problem-id") || problemId;
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.setItem(key, textarea.value);
            });
            window.location.href = `${window.location.pathname}?step=${nextStep}&problem_id=${nextProblemId}`;
        });
    });

    // step 버튼 초기 강조
    buttons.forEach(btn => {
        if (btn.getAttribute("data-step") === currentStep) {
            btn.classList.add("active");
        }
    });

    // 탭 키로 들여쓰기
    document.querySelectorAll('textarea').forEach((textarea) => {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = this.selectionStart;
                const end = this.selectionEnd;
                this.value = this.value.substring(0, start) + '\t' + this.value.substring(end);
                this.selectionStart = this.selectionEnd = start + 1;
            }
        });
    });

    // textarea 자동 높이
    document.querySelectorAll(".styled-textarea").forEach((ta) => {
        autoResize(ta);
        ta.addEventListener("input", () => autoResize(ta));
    });

    function autoResize(textarea) {
        textarea.style.height = "auto";
        textarea.style.height = textarea.scrollHeight + "px";
    }
});

// 정답 보기
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
function escapeHtml(text) {
    return text.replace(/&/g, "&amp;")
               .replace(/</g, "&lt;")
               .replace(/>/g, "&gt;")
               .replace(/"/g, "&quot;")
               .replace(/'/g, "&#039;");
}

function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content?.trim();
    const answerArea = document.getElementById(`answer_area_${index}`);
    if (!correctCode) {
        answerArea.innerHTML = "<em>정답이 등록되지 않았습니다.</em>";
        answerArea.style.display = 'block';
        return;
    }

    if (answerArea.style.display === 'block') {
        answerArea.style.display = 'none';
        return;
    }

    const escapedCode = escapeHtml(correctCode);
    const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${escapedCode}</pre>`;
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';
}
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
