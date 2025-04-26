<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";

        foreach ($blocks as $block) {
            $indent_px = 10 * ($block['depth'] ?? 0);

            if (in_array($block['type'], ['func_def', 'rep', 'cond', 'struct', 'construct'])) {
                // [func_def_start] 같은 블록 안
                foreach ($block['children'] as $child) {
                    if ($child['type'] === 'text') {
                        $raw = trim($child['content']);
                        if ($raw === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                            continue;
                        }

                        // 🧩 코드 입력 (블럭 안 직접 코드)
                        $code_content = $GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '';
                        $code_clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $code_content);
                        $code_clean = htmlspecialchars(trim($code_clean));
                        $disabled = $answer_index > 0 ? "disabled" : "";

                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<div style='flex: 1'>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$code_clean}</textarea>";
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
                        $html .= "<span id='wrong_{$answer_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
                        $html .= "</div></div>";

                        $answer_index++;
                    }
                    elseif (in_array($child['type'], ['self'])) {
                        // ✨ self 블럭(설명+코드)
                        foreach ($child['children'] as $gchild) {
                            if ($gchild['type'] === 'text') {
                                $desc = trim($gchild['content']);
                                if ($desc === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $desc)) {
                                    continue;
                                }

                                // 설명 출력
                                $html .= "<div class='code-line' style='margin-left: {$indent_px}px;'>".htmlspecialchars($desc)."</div>";

                                // 이어서 코드 입력
                                $code_content = $GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '';
                                $code_clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $code_content);
                                $code_clean = htmlspecialchars(trim($code_clean));
                                $disabled = $answer_index > 0 ? "disabled" : "";

                                $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                                $html .= "<div style='flex: 1'>";
                                $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$code_clean}</textarea>";
                                $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                                $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
                                $html .= "<span id='wrong_{$answer_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
                                $html .= "</div></div>";

                                $answer_index++;
                            }
                        }
                    }
                }
            }
            elseif ($block['type'] === 'text') {
                // 최상위 text (일반 코드)
                $raw = trim($block['content']);
                if ($raw === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                    continue;
                }

                $html .= "<div class='code-line' style='margin-left: {$indent_px}px;'>".htmlspecialchars($raw)."</div>";

                $code_content = $GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '';
                $code_clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $code_content);
                $code_clean = htmlspecialchars(trim($code_clean));
                $disabled = $answer_index > 0 ? "disabled" : "";

                $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                $html .= "<div style='flex: 1'>";
                $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$code_clean}</textarea>";
                $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
                $html .= "<span id='wrong_{$answer_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
                $html .= "</div></div>";

                $answer_index++;
            }
        }

        return $html;
    }

    $answer_index = 0;
    echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="min-height: 400px;">
        <!-- 오른쪽 패널은 비워둠, 높이 제한 -->
    </div>
</div>

<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

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
