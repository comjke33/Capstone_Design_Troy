<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ STEP 번호 받기
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// ✅ 파일 경로 설정
$base_path = "/home/Capstone_Design_Troy/test/step{$step}/";
$guideline_contents = file_get_contents($base_path . "guideline.txt");
$txt_contents = file_get_contents($base_path . "tagged_code.txt");

// ✅ 설명 파일 파서
function parse_blocks_with_loose_text($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line),
                    'depth' => $depth + $indent_level
                ];
            }
        }

        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];

        $start_tag = "[{$type}_start({$idx})]";
        $end_tag = "[{$type}_end({$idx})]";

        $children = parse_blocks_with_loose_text($content, $depth + 1);
        array_unshift($children, ['type' => 'text', 'content' => $start_tag, 'depth' => $depth + 1]);
        array_push($children, ['type' => 'text', 'content' => $end_tag, 'depth' => $depth + 1]);

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => $children
        ];

        $offset = $end_pos;
    }

    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
            $blocks[] = [
                'type' => 'text',
                'content' => rtrim($line),
                'depth' => $depth + $indent_level
            ];
        }
    }

    return $blocks;
}

// ✅ 정답 코드 라인 추출
function extract_tagged_code_lines($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $positions = [];
    foreach ($matches[0] as $i => $match) {
        $positions[] = [
            'pos' => $match[1],
            'end' => $match[1] + strlen($match[0])
        ];
    }

    $lines = [];
    for ($i = 0; $i < count($positions); $i++) {
        $start_pos = $positions[$i]['end'];
        $end_pos = isset($positions[$i + 1]) ? $positions[$i + 1]['pos'] : strlen($text);
        $code_block = substr($text, $start_pos, $end_pos - $start_pos);

        foreach (explode("\n", $code_block) as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                if ($trimmed === '}') {
                    $lines[] = ['content' => $trimmed, 'readonly' => true, 'info' => '닫는 괄호'];
                    $lines[] = ['content' => '', 'readonly' => false, 'info' => ''];
                } else {
                    $lines[] = ['content' => $trimmed, 'readonly' => false, 'info' => ''];
                }
            }
        }
    }

    return $lines;
}

// ✅ 데이터 준비
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = "STEP $step";

// ✅ 탭 버튼
echo "<div class='ui large buttons' style='margin-bottom:2em;'>";
for ($i = 1; $i <= 3; $i++) {
    $active = ($i === $step) ? "style='background-color:#1678c2; color:white;'" : "";
    echo "<a href='guideline.php?step=$i' class='ui blue button' $active>Step $i</a>";
}
echo "</div>";

// ✅ 렌더링 함수
function render_tree_plain($blocks, &$answer_index = 0) {
    $html = "";

    foreach ($blocks as $block) {
        $indent_px = 10 * ($block['depth'] ?? 0);

        if (isset($block['children'])) {
            $desc_lines = [];
            $is_closing_brace = false;
            $code_data = $GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index] ?? null;
            $readonly = $code_data['readonly'] ?? false;
            $info = $code_data['info'] ?? '';

            if ($readonly && $info === '닫는 괄호') {
                $html .= "<div class='code-line' style='margin-left: {$indent_px}px; color: #666; font-style: italic;'>※ {$info}</div>";
                $html .= "<div class='code-line' style='margin-left: {$indent_px}px;'>{$code_data['content']}</div>";
                $answer_index++;
                $is_closing_brace = true;
            }

            if (!$is_closing_brace) {
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

                if ($code_data) {
                    $code_line = htmlspecialchars(trim($code_data['content']));
                    $readonly_attr = $readonly ? 'readonly' : '';
                    $disabled = (!$readonly && $answer_index !== 0) ? 'disabled' : '';

                    $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                    $html .= "<div style='flex: 1'>";
                    if ($info !== '' && !$readonly) {
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
            }

            $html .= render_tree_plain($block['children'], $answer_index);
        }
    }

    return $html;
}

// ✅ 출력
echo "<div class='main-layout'>";
echo "<div class='left-panel'>";
$answer_index = 0;
echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
echo "</div>";
echo "<div class='right-panel' id='feedback-panel'><h4>📝 피드백</h4></div>";
echo "</div>";
?>

<script>
function initializeGuideline(correctAnswers) {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });

    window.submitAnswer = function(index) {
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
}

initializeGuideline(<?= json_encode($OJ_CORRECT_ANSWERS) ?>);
</script>

<?php include("template/syzoj/footer.php"); ?>
