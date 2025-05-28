<?php
include("template/$OJ_TEMPLATE/header.php");
include("../../guideline_common.php");
?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'></div>
<link rel="stylesheet" href="/template/syzoj/css/guideline3.css">


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
    <div class="flowchart-wrapper active" id="flowchart-wrapper">
        <div class="flowchart-title">Flowchart</div>
        <div class="flowchart-scroll">
        <img id="flowchart_image">
        </div>
    </div>
    </div>

    <!-- 가운데 패널 -->
<div class="center-panel">
    <h1>실전 풀기</h1>

    <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
    <br><br>

    <?php      
        function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            $depth = $block['depth'];
            $margin_left = $depth * 50;
            $isCorrect = false;

            if ($block['type'] === 'text') {
                $raw = trim($block['content']);
                if ($raw === '') continue;

                $html .= "<!-- DEBUG raw line [{$answer_index}]: " . htmlentities($raw) . " -->\n";
                $html .= "<script>console.log('Block index {$answer_index} - Depth: {$depth}');</script>";

                // 정답 가져오기
                $default_value = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index])
                    ? htmlspecialchars($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'], ENT_QUOTES, 'UTF-8')
                    : '';

                $has_correct_answer = !empty($default_value);
                $disabled = $has_correct_answer ? "" : "disabled";
                $readonlyStyle = "background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; font-size: 18px;";

                $html .= "<div class='submission-line' style='margin-left: {$margin_left}px;'>";

                // ✅ Depth 1: 읽기 전용 정답 표시용 블록
                if ($depth === 1) {
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' readonly style='{$readonlyStyle}'>{$default_value}</textarea>";
                } else {
                    // 일반 입력 블록
                    //$escaped_line = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
                    $escaped_line = nl2br(htmlspecialchars($raw, ENT_QUOTES, 'UTF-8'));
                    $html .= "<div class='code-line'>{$escaped_line}</div>";
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";

                    if (!$isCorrect) {
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='submit_btn_{$answer_index}' class='submit-button'>제출</button>";
                        $html .= "<button onclick='showAnswer({$answer_index})' id='answer_btn_{$answer_index}' class='answer-button'>답안 확인</button>";
                        $html .= "<button onclick='showFeedback({$answer_index})' id='feedback_btn_{$answer_index}' class='feedback-button'>피드백 보기</button>";
                    }
                }

                $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                $html .= "</div>";
                $answer_index++;
            } elseif (isset($block['children']) && is_array($block['children'])) {
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

        textarea.addEventListener("input", () => {
            localStorage.setItem(key, textarea.value);
        });
    });

    // ✅ 버튼 클릭 시 저장 후 이동 + 스타일 토글
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const nextStep = btn.getAttribute("data-step");
            const nextProblemId = btn.getAttribute("data-problem-id") || problemId;

            // 👉 모든 버튼에서 'active' 클래스 제거
            buttons.forEach(b => b.classList.remove("active"));

            // 👉 클릭한 버튼에만 'active' 클래스 추가
            btn.classList.add("active");

            // 값 저장
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.setItem(key, textarea.value);
            });

            // 페이지 이동
            const baseUrl = window.location.pathname;
            window.location.href = `${baseUrl}?step=${nextStep}&problem_id=${nextProblemId}`;
        });
    });

    // ✅ 초기 로딩 시 URL의 step 값을 기준으로 버튼 강조
    buttons.forEach(btn => {
        const step = btn.getAttribute("data-step");
        if (step === currentStep) {
            btn.classList.add("active");
        } else {
            btn.classList.remove("active");
        }
    });
});


// textarea에서 tab을 누르면 들여쓰기가 적용되게([    ])
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea').forEach((textarea) => {
      textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
          e.preventDefault(); // 기본 Tab 동작 막기

          const start = this.selectionStart;
          const end = this.selectionEnd;

          // 현재 위치에 '\t' 삽입
          this.value = this.value.substring(0, start) + '\t' + this.value.substring(end);

          // 커서 위치 조정
          this.selectionStart = this.selectionEnd = start + 1;
        }
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


//화면 크기 재조절
function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}


</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>