<div class='problem-id' style='font-weight:bold; font-size:18px; margin-bottom: 20px;'>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<div class='code-container' style='font-family: monospace; line-height: 1.5; max-width: 1000px; margin: 0 auto;'>
    <?php
    function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            $indent_px = 40 * $block['depth'];
            if (isset($block['children'])) {
                $html .= render_tree_plain($block['children'], $answer_index);
            } else {
                $line = htmlspecialchars($block['content']);
                if ($line !== '') {
                    if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $line)) {
                        $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px; color:red;'>|</div>";
                    } else {
                        $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px; white-space: pre-wrap;'>$line</div>";
                        $html .= "<div style='padding-left: {$indent_px}px; display: flex; align-items: center; gap: 6px;'>";
                        $html .= "<textarea id='ta_{$answer_index}' rows='2' style='width: calc(100% - 80px); margin-bottom: 10px;'></textarea>";
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' style='height: 30px;'>제출</button>";
                        $html .= "<span id='check_{$answer_index}' style='color: green; font-size: 20px; display:none;'>✔️</span>";
                        $html .= "</div>";
                        $answer_index++;
                    }
                }
            }
        }
        return $html;
    }

    $answer_index = 0;
    echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
    ?>
</div>

<!-- ✅ JavaScript: 정답 판별 -->
<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const input = ta.value.trim();
    const correct = correctAnswers[index].trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eee";
        btn.style.display = "none";
        check.style.display = "inline";
    } else {
        alert("틀렸습니다. 다시 시도해보세요!");
    }
}
</script>
