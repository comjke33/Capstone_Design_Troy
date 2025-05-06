<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php include("../../guideline_common.php");?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<!-- 상단 툴바 -->
<div class="top-toolbar">
    <div class="step-buttons">
        <button class="ui button" data-step="1" data-problem-id="123">Step 1</button>
        <button class="ui button" data-step="2" data-problem-id="123">Step 2</button>
        <button class="ui button" data-step="3" data-problem-id="123">Step 3</button>
    </div>
</div>

<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
        <div id="flowchart-images"></div>
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
        
                // text 블록은 직접 렌더링
                if ($block['type'] === 'text') {
                    $raw = trim($block['content']);
                    if ($raw === '') continue;
        
                    //특수문자 처리
                    $line = htmlspecialchars($block['content']);
                    //현재 줄에 정답 여부 확인하여 정답 여부 처리 정답이면 입력가능, 아니라면 입력창 비활성화
                    $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
                    $disabled = $has_correct_answer ? "" : "disabled";
        
                    //들여쓰기 적용 부분 & 가이드라인, 코드 영역
                    $html .= "<div class='submission-line' style='margin-left: {$margin_left}px;'>";
                    $html .= "<div class='code-line'>{$line}</div>";
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
        
                    //답이 맞은 경우 
                    if ($has_correct_answer) {
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                        $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>";
                    }
        
                    //체크 마크 표시
                    $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                    $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                    $html .= "</div>";
        
                    $answer_index++;
                }
        
                // block 블록: 자식만 출력 (자신은 출력 X)
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
    <div class="right-panel">
        <h2>📋 피드백 창</h2>
    </div>
</div>

<script>

//버튼 부분
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const step = btn.getAttribute("data-step");
            const problemId = btn.getAttribute("data-problem-id");

            // 현재 guideline1.php 안이라면, 자기 자신으로 이동
            const baseUrl = window.location.pathname;  // 현재 경로 유지 (/guideline1.php)

            // 주소 이동
            window.location.href = `${baseUrl}?step=${step}&problem_id=${problemId}`;
        });
    });
});
    
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($problem_id) ?>

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

let currentTextarea = null;
let animationRunning = false;

function updateImageForTextarea(index, ta) {
    currentTextarea = ta;
    fetch(`../../get_flowchart_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            let img = document.getElementById("floating-img");
            if (!img) {
                img = document.createElement("img");
                img.id = "floating-img";
                document.body.appendChild(img);
            }

            img.src = data.url;
            console.log("서버 디버그 데이터:", data.debug);

            if (!animationRunning) {
                animationRunning = true;
                smoothFollowImage(); // 따라오기 시작
            }
        });
}

function smoothFollowImage() {
    const img = document.getElementById("floating-img");
    if (!img || !currentTextarea) {
        animationRunning = false;
        return;
    }

    const taRect = currentTextarea.getBoundingClientRect();
    const scrollY = window.scrollY || document.documentElement.scrollTop;

    let targetTop = taRect.top + scrollY - img.offsetHeight + 100;

    // 화면 기준 제한
    const minTop = scrollY + 10; // 화면 상단 + 여백
    const maxTop = scrollY + window.innerHeight - img.offsetHeight - 10; // 화면 하단 - 이미지 높이

    // 제한된 위치로 보정
    targetTop = Math.max(minTop, Math.min(targetTop, maxTop));

    const currentTop = parseFloat(img.style.top) || 0;
    const nextTop = currentTop + (targetTop - currentTop) * 0.1;

    img.style.top = `${nextTop}px`;

    requestAnimationFrame(smoothFollowImage);
}


// textarea 클릭 시 이미지 로드
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
        ta.addEventListener("focus", () => updateImageForTextarea(idx, ta));
    });

});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>