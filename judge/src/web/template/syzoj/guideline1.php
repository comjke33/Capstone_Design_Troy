<?php
/** @var string $OJ_SID */
/** @var array $OJ_BLOCK_TREE */
/** @var array $OJ_CORRECT_ANSWERS */
?>
<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<style>
    .main-layout { display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; }
    .left-panel { flex: 2; }
    .right-panel {
        flex: 1;
        padding: 16px;
        background-color: #fafafa;
        border: 1px solid #eee;
        border-radius: 8px;
        font-family: monospace;
    }
    .code-line {
        background-color: #f8f8fa;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px 16px;
        margin-bottom: 10px;
        font-size: 15px;
        color: #333;
        white-space: pre-wrap;
    }
    .styled-textarea {
        border: 1px solid #ccc;
        border-radius: 6px;
        padding: 10px 14px;
        font-family: monospace;
        font-size: 15px;
        background-color: #fff;
        line-height: 1.6;
        resize: none;
        width: 100%;
        box-sizing: border-box;
        min-height: 40px;
    }
    .submit-button {
        margin-top: 6px;
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
    }
    .checkmark {
        font-size: 18px;
        margin-left: 6px;
        color: #2ecc71;
    }
    .submission-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 20px;
        margin-bottom: 28px;
    }
    .feedback-line { margin-bottom: 12px; font-size: 15px; }
    .feedback-correct { color: #2ecc71; }
    .feedback-wrong { color: #e74c3c; }
</style>

<div class="main-layout">
    <div class="left-panel">
    <?php
    function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";

        foreach ($blocks as $block) {
            $indent_px = 10 * ($block['depth'] ?? 0);

            if (isset($block['children'])) {
                // ✅ 설명 추출
                $desc_lines = [];
                foreach ($block['children'] as $child) {
                    if ($child['type'] === 'text') {
                        $raw = trim($child['content']);
                        if (
                            $raw !== '' &&
                            $raw !== '}' &&
                            !preg_match("/^\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]$/", $raw)
                        ) {
                            $desc_lines[] = htmlspecialchars($raw);
                        }
                    }
                }

                if (!empty($desc_lines)) {
                    $desc_html = implode("<br>", $desc_lines);
                    $html .= "<div class='code-line' style='margin-left: {$indent_px}px;'>{$desc_html}</div>";
                }

                // ✅ 정답 코드 렌더링
                if (isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index])) {
                    $code_data = $GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index];
                    $code_line = htmlspecialchars(trim($code_data['content']));
                    $readonly = $code_data['readonly'] ?? false;
                    $info = $code_data['info'] ?? '';
                    $readonly_attr = $readonly ? 'readonly' : '';
                    $disabled = (!$readonly && $answer_index !== 0) ? 'disabled' : '';

                    $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                    $html .= "<div style='flex: 1'>";
                    if ($info !== '') {
                        $html .= "<div class='code-line' style='color: #666; font-style: italic;'>※ {$info}</div>";
                    }
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$readonly_attr} {$disabled}>{$code_line}</textarea>";
                    if (!$readonly) {
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                    }
                    $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                    $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                    $html .= "</div></div>";

                    $answer_index++;
                }

                $html .= render_tree_plain($block['children'], $answer_index);
            }
        }

        return $html;
    }

    $answer_index = 0;
    echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
    ?>
    </div>

    <div class="right-panel" id="feedback-panel">
        <h4>📝 피드백</h4>
    </div>
</div>

<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

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

function updateFeedback(index, isCorrect) {
    const panel = document.getElementById('feedback-panel');
    const existing = document.getElementById(`feedback_${index}`);
    const result = isCorrect ? "✔️ 정답" : "❌ 오답";
    const line = `<div id="feedback_${index}" class="feedback-line ${isCorrect ? 'feedback-correct' : 'feedback-wrong'}">Line ${index + 1}: ${result}</div>`;
    if (existing) existing.outerHTML = line;
    else panel.insertAdjacentHTML('beforeend', line);
}

function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});
</script>
