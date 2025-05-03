<?php
require_once "include/db_info.inc.php";
require_once "src/web/common.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$raw_code = ""; // 문제 텍스트 또는 가이드라인이 저장된 위치에서 불러오기

// 예시: 파일에서 로드 (실제 환경에 맞게 수정 필요)
$code_path = __DIR__ . "/../../test/guideline_texts/{$problem_id}.txt";
if (file_exists($code_path)) {
    $raw_code = file_get_contents($code_path);
}

// 트리 파싱
$OJ_BLOCK_TREE = parse_blocks($raw_code);

// 정답 로딩 (예시)
$OJ_CORRECT_ANSWERS = []; // 이 부분은 시스템에서 불러온 정답 배열로 채워야 함

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
        <div id="flowchart-images"></div>
    </div>

    <!-- 가운데 패널 -->
    <div class="center-panel">
        <h1>한줄씩 풀기</h1>
        <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
        <?php
        function render_tree_plain($blocks, &$answer_index = 0, $indent = 0) {
            $html = "";
            $pad = str_repeat("  ", $indent); // 들여쓰기 가시화

            foreach ($blocks as $block) {
                $depth = $block['depth'] ?? $indent;

                if (isset($block['children'])) {
                    $html .= "{$pad}<div class='block-wrap block-{$block['type']} depth-{$depth}'>\n";
                    $html .= render_tree_plain($block['children'], $answer_index, $depth + 1);
                    $html .= "{$pad}</div>\n";
                    continue;
                }

                if ($block['type'] === 'text') {
                    $raw = trim($block['content']);
                    if ($raw === '' || preg_match("/^\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]$/", $raw)) {
                        continue;
                    }

                    $line = htmlspecialchars($block['content']);
                    $line = preg_replace('/\\[(.*?)\\]/', '', $line);
                    $line = trim($line);

                    $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
                    $disabled = $has_correct_answer ? "" : "disabled";

                    $html .= "{$pad}<div class='submission-line depth-{$depth}'>\n";
                    $html .= "{$pad}  <div class='code-line'>{$line}</div>\n";
                    $html .= "{$pad}  <textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>\n";

                    if ($has_correct_answer) {
                        $html .= "{$pad}  <button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>\n";
                        $html .= "{$pad}  <button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>\n";
                    }

                    $html .= "{$pad}  <div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>\n";
                    $html .= "{$pad}  <div style='width: 50px; text-align: center; margin-top: 10px;'>\n";
                    $html .= "{$pad}    <span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span>\n";
                    $html .= "{$pad}  </div>\n";
                    $html .= "{$pad}</div>\n";

                    $answer_index++;
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
    
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($OJ_SID) ?>;

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

    let targetTop = taRect.top + scrollY - img.offsetHeight + 200;

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
