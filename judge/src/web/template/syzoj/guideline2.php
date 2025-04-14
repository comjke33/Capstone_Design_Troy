<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<div class='code-container' style='font-family: monospace; line-height: 1.8; max-width: 1000px; margin: 0 auto;'>
    <style>
        .code-line {
            background-color: #f8f8fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 10px;
            display: block;
            font-size: 15px;
            color: #333;
            font-family: monospace;
            white-space: pre-wrap;
        }

        .styled-textarea {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 10px 14px;
            font-family: monospace;
            font-size: 15px;
            background-color: #fff;
            transition: all 0.2s ease-in-out;
            line-height: 1.6;
            resize: none;
            width: 100%;
            box-sizing: border-box;
            min-height: 40px;
        }

        .styled-textarea:focus {
            border-color: #4a90e2;
            background-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.15);
            outline: none;
        }

        .submit-button {
            height: 38px;
            padding: 0 16px;
            font-size: 14px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
        }

        .submit-button:hover {
            background-color: #357ab7;
            transform: scale(1.03);
        }

        .checkmark {
            color: #2ecc71;
            font-size: 18px;
            margin-left: 6px;
        }

        .submission-line {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 28px;
        }

        .block-wrap {
            border-radius: 20px;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid transparent;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
        }

        .block-func_def {
            background-color: #ffe599;
            border-color: #f6b26b;
        }

        .block-rep {
            background-color: #f4cccc;
            border-color: #ea9999;
        }

        .block-cond {
            background-color: #d9ead3;
            border-color: #93c47d;
        }

        .block-self {
            background-color: #cfe2f3;
            border-color: #6fa8dc;
        }

        .block-struct {
            background-color: #ead1dc;
            border-color: #c27ba0;
        }

        .block-construct {
            background-color: #d9d2e9;
            border-color: #8e7cc3;
        }

        .block-wrap .submission-line {
            margin-top: 12px;
        }
    </style>

    <?php
    function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            $indent_px = 40 * $block['depth'];

            if (isset($block['children'])) {
                $type_class = 'block-' . $block['type'];
                $html .= "<div class='block-wrap {$type_class}' style='margin-left: {$indent_px}px;'>";
                $html .= render_tree_plain($block['children'], $answer_index);
                $html .= "</div>";
            } else {
                $line = htmlspecialchars($block['content']);
                if ($line !== '') {
                    if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $line)) {
                        $html .= "<div style='margin-bottom:8px; padding-left: {$indent_px}px; color:red; font-size: 18px;'>‍‍‍‍️️️️ </div>";
                    } else {
                        $html .= "<div style='padding-left: {$indent_px}px;'><div class='code-line'>{$line}</div></div>";
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
        ta.style.backgroundColor = "#eef1f4";
        ta.style.border = "1px solid #ccc";
        btn.style.display = "none";
        check.style.display = "inline";
        ta.style.color = "#000";
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
    }
}

// ✅ 자동 높이 조절
function autoResize(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(textarea => {
        textarea.addEventListener('input', () => autoResize(textarea));
    });
});
</script>
