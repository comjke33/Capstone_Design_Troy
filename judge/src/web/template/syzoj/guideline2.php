<div class='problem-id' style='font-weight:bold; font-size:18px; margin-bottom: 20px;'>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<div class='code-container' style='font-family: monospace; line-height: 1.5; max-width: 1000px; margin: 0 auto;'>
    <style>
        .styled-textarea {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px 10px;
            font-family: monospace;
            font-size: 14px;
            resize: vertical;
            background-color: #ffffff;
            transition: border-color 0.2s;
        }

        .styled-textarea:focus {
            outline: none;
            border-color: #4a90e2;
            background-color: #fff;
        }

        .submit-button {
            height: 30px;
            width: 60px;
            padding: 0 12px;
            font-size: 13px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .submit-button:hover {
            background-color: #357ab7;
        }

        .checkmark {
            color: green;
            font-size: 18px;
            margin-left: 5px;
        }

        .submission-line {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }
    </style>

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
                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' rows='2'></textarea>";
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
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

<!-- ✅ JavaScript -->
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
        ta.style.backgroundColor = "#e6e6e6";
        ta.style.border = "1px solid #ccc";
        btn.style.display = "none";
        check.style.display = "inline";
    } else {
        alert("틀렸습니다. 다시 시도해보세요!");
    }
}
</script>
