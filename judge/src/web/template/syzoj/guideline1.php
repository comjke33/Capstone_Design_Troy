<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
        <div id="flowchart-images"></div>
    </div>

    <!-- 가운데 패널 -->
    <div class="center-panel">
        <?php
        function render_tree_plain($blocks, &$answer_index = 0) {
            $html = "";
            foreach ($blocks as $block) {
                $depth = $block['depth'] ?? 0;
                if (isset($block['children'])) {
                    $html .= "<div class='block-wrap block-{$block['type']} depth-{$depth}'>";
                    $html .= render_tree_plain($block['children'], $answer_index);
                    $html .= "</div>";
                } elseif ($block['type'] === 'text') {
                    $raw = trim($block['content']);
                    if ($raw === '' || preg_match("/^\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]$/", $raw)) continue;
        
                    $line = htmlspecialchars($block['content']);
                    $line = preg_replace('/\\[(.*?)\\]/', '', $line);
                    $line = trim($line);
        
                    $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);            
                    $disabled = $has_correct_answer ? "" : "disabled";
        
                    $html .= "<div class='submission-line depth-{$depth}'>";
                    $html .= "<div class='code-line'>{$line}</div>";
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
        
                    if ($has_correct_answer) {
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                        $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>";
                    }
        
                    $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                    $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                    $html .= "</div>"; // .submission-line
        
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

function updateImageForTextarea(index, ta) {
    fetch(`../../get_flowchart_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("flowchart-images");
            container.innerHTML = "";
            const img = document.createElement("img");
            img.src = data.success && data.url ? data.url + "?t=" + new Date().getTime() : "../../image/default.jpg";

            const taRect = ta.getBoundingClientRect();
            const centerPanel = ta.closest(".center-panel");
            const centerRect = centerPanel.getBoundingClientRect();
            const topOffset = taRect.top - centerRect.top;

            img.style.position = "absolute";
            img.style.top = `${topOffset}px`;
            img.style.width = "100%";
            img.style.maxHeight = "300px";
            img.style.border = "2px solid #ccc";

            container.appendChild(img);
        });
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
        ta.addEventListener("focus", () => updateImageForTextarea(idx, ta));
    });
});

</script>
