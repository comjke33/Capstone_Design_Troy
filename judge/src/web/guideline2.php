<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// 예시 정답 배열 (index에 맞춰 입력) — 서버에서 실제 비교 정답을 넘겨야 함
$correct_answers = [
    "if (x > 0) {",
    "for (int i = 0; i < 10; i++) {",
    "...", // 필요한 만큼 채워주세요
];

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

        array_unshift($children, [
            'type' => 'text',
            'content' => $start_tag,
            'depth' => $depth + 1
        ]);
        array_push($children, [
            'type' => 'text',
            'content' => $end_tag,
            'depth' => $depth + 1
        ]);

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

function render_tree_plain($blocks, &$answer_index = 0) {
    $html = "";
    foreach ($blocks as $block) {
        $indent_px = 40 * $block['depth'];
        if (isset($block['children'])) {
            // 설명은 생략
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

$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo "<div class='problem-id' style='font-weight:bold; font-size:18px; margin-bottom: 20px;'>문제 번호: " . htmlspecialchars($sid) . "</div>";

$block_tree = parse_blocks_with_loose_text($file_contents);
$answer_index = 0;
$html_output = render_tree_plain($block_tree, $answer_index);

echo "<div class='code-container' style='font-family: monospace; line-height: 1.5; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";
?>

<!-- ✅ 정답 리스트를 JS로 전달 -->
<script>
const correctAnswers = <?php echo json_encode($correct_answers); ?>;

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

<?php
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
