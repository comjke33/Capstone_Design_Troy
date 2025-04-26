<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>목적 번호: <?= htmlspecialchars(\$OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
        <?php
        function render_guideline_and_code(\$guideline_blocks, \$code_blocks) {
            \$html = "";
            \$guideline_index = 0;
            \$code_index = 0;

            while (\$guideline_index < count(\$guideline_blocks) && \$code_index < count(\$code_blocks)) {
                \$guide_block = \$guideline_blocks[\$guideline_index];
                \$code_block = \$code_blocks[\$code_index];

                // 설명 텍스트 처리
                if (\$guide_block['type'] === 'text') {
                    \$raw = trim(\$guide_block['content']);

                    // 태그 무시
                    if (\$raw === '' || preg_match("/^\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]$/", \$raw)) {
                        \$guideline_index++;
                        continue;
                    }

                    \$indent_px = 10 * (\$guide_block['depth'] ?? 0);
                    \$line = htmlspecialchars(\$raw);
                    \$html .= "<div class='code-line' style='margin-left: {\$indent_px}px;'> {\$line} </div>";

                    // 이어서 코드 입력 영역
                    if (isset(\$code_block['content'])) {
                        \$code_raw = preg_replace("/\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]/", "", \$code_block['content']);
                        \$code_clean = htmlspecialchars(trim(\$code_raw));

                        \$html .= "<div class='submission-line' style='padding-left: {\$indent_px}px;'>";
                        \$html .= "<div style='flex:1'>";
                        \$html .= "<textarea id='ta_{$code_index}' class='styled-textarea' data-index='{$code_index}'>{$code_clean}</textarea>";
                        \$html .= "<button onclick='submitAnswer({$code_index})' id='btn_{$code_index}' class='submit-button'>제출</button>";
                        \$html .= "<span id='check_{$code_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
                        \$html .= "<span id='wrong_{$code_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
                        \$html .= "</div></div>";

                        \$code_index++;
                    }

                    \$guideline_index++;
                } else {
                    // self, func_def 등은 children 순회
                    if (isset(\$guide_block['children'])) {
                        \$html .= render_guideline_and_code(\$guide_block['children'], \$code_blocks);
                    }
                    \$guideline_index++;
                }
            }

            return \$html;
        }

        echo render_guideline_and_code(\$OJ_BLOCK_TREE, \$OJ_CORRECT_ANSWERS);
        ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;"></div>
</div>

<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").replace(/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/g, '').trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        if (wrong) wrong.style.display = "none";

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
        if (wrong) wrong.style.display = "inline";
    }
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
