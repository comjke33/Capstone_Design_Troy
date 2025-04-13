<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<div class='code-container' style='font-family: monospace; line-height: 1.8; max-width: 1000px; margin: 0 auto;'>
    <style>
        .code-line {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 10px;
            display: inline-block;
            font-size: 16px;
            font-family: monospace;
        }

        .styled-textarea {
            border: 1px solid #bbb;
            border-radius: 10px;
            padding: 10px 14px;
            font-family: monospace;
            font-size: 16px;
            background-color: #ffffff;
            transition: all 0.2s ease-in-out;
            height: 42px;
            resize: vertical;
            width: 100%;
        }

        .styled-textarea:focus {
            background-color: #ffffff;
            border-color: #4a90e2;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.15);
            outline: none;
        }

        .submit-button {
            width: 60px;
            height: 42px;
            padding: 0 16px;
            font-size: 16px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .submit-button:hover {
            background-color: #357ab7;
        }

        .checkmark {
            color: green;
            font-size: 20px;
            margin-left: 8px;
        }

        .submission-line {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
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
                        $html .= "<div style='margin-bottom:8px; padding-left: {$indent_px}px; color:red; font-size: 18px;'>|</div>";
                    } else {
                        // 설명 줄 스타일
                        $html .= "<div style='padding-left: {$indent_px}px;'><div class='code-line'>{$line}</div></div>";
                        
                        // 입력 줄
                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea'></textarea>";
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

<!-- ✅ 정답 검증 스크립트 -->
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
