<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<!-- ✅ CSS 외부 파일 연결 -->
<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
        <?php
        function render_tree_plain($blocks, &$answer_index = 0) {
            $html = "";

            foreach ($blocks as $block) {
                $indent_px = 10 * ($block['depth'] ?? 0);

                if ($block['type'] === 'text') {
                    $raw = trim($block['content']);

                    // 🔵 1. 태그 무시
                    if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                        continue;
                    }

                    // 🔵 2. 설명줄(주석 등) 처리
                    if (preg_match("/^\/\//", $raw) || preg_match("/^\/\*/", $raw)) {
                        $line = htmlspecialchars($raw);
                        $html .= "<div class='code-line' style='margin-left: {$indent_px}px;'>{$line}</div>";
                    } 
                    // 🔵 3. 코드줄 textarea 생성
                    else {
                        $correct_code = htmlspecialchars($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '');
                        $disabled = $answer_index > 0 ? "disabled" : "";

                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<div style='flex: 1'>";
                        $html .= "<div class='code-line'>코드 작성:</div>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$correct_code}</textarea>";
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                        $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                        $html .= "</div></div>";

                        $answer_index++;
                    }
                }

                // 🔵 4. children 있으면 재귀
                if (isset($block['children'])) {
                    $html .= render_tree_plain($block['children'], $answer_index);
                }
            }

            return $html;
        }

        // 🔵 실제 실행하는 부분
        $answer_index = 0;
        echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <div class="right-panel" id="feedback-panel">
        <h4>📝 피드백</h4>
    </div>
</div>

<script>
// 정답 리스트
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

// 제출 버튼 클릭시 호출
function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        updateFeedback(index, true);

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
        updateFeedback(index, false);
    }
}

// 피드백 업데이트
function updateFeedback(index, isCorrect) {
    const panel = document.getElementById('feedback-panel');
    const existing = document.getElementById(`feedback_${index}`);
    const result = isCorrect ? "✔️ 정답" : "❌ 오답";
    const line = `<div id="feedback_${index}" class="feedback-line ${isCorrect ? 'feedback-correct' : 'feedback-wrong'}">Line ${index + 1}: ${result}</div>`;
    if (existing) existing.outerHTML = line;
    else panel.insertAdjacentHTML('beforeend', line);
}

// textarea 자동 리사이즈
function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}

// 초기화
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});
</script>
