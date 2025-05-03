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

<script>let currentTextarea = null;

function updateImageForTextarea(index, ta) {
    currentTextarea = ta;

    fetch(`../../get_flowchart_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("flowchart-images");
            container.innerHTML = "";

            const img = document.createElement("img");
            img.src = data.url;
            img.id = "floating-img";

            // 이미지 스타일
            img.style.position = "absolute";
            img.style.width = "100%";
            img.style.maxHeight = "300px";
            img.style.border = "2px solid #ccc";
            img.style.zIndex = "999";

            container.appendChild(img);

            // 위치 설정
            positionImageRelativeToTextarea();

            // 스크롤 이벤트 중복 제거 + 재등록
            const centerPanel = document.querySelector(".center-panel");
            centerPanel.removeEventListener("scroll", handleScroll);
            centerPanel.addEventListener("scroll", handleScroll);
        });
}

function handleScroll() {
    positionImageRelativeToTextarea();
}

function positionImageRelativeToTextarea() {
    if (!currentTextarea) return;

    const img = document.getElementById("floating-img");
    const centerPanel = document.querySelector(".center-panel");

    const taRect = currentTextarea.getBoundingClientRect();
    const panelRect = centerPanel.getBoundingClientRect();

    const relativeTop = taRect.top - panelRect.top + centerPanel.scrollTop;
    const relativeLeft = taRect.left - panelRect.left + centerPanel.scrollLeft;

    if (img) {
        // 위에서 띄우되, 너무 위로는 가지 않게 (짤림 방지)
        const imageHeight = img.offsetHeight || 250;
        const offset = 10;

        let finalTop = relativeTop - imageHeight - offset;

        // 이미지가 너무 위로 올라가지 않게 제한
        finalTop = Math.max(0, finalTop);

        img.style.top = `${finalTop}px`;
        img.style.left = `${relativeLeft}px`;
    }
}

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
        ta.addEventListener("focus", () => updateImageForTextarea(idx, ta));
    });
});

</script>
