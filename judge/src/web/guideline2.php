<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

function parse_blocks_with_loose_text($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $line = trim($line);
                if (!preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\]$/", $line)) {
                    $blocks[] = [
                        'type' => 'text',
                        'content' => $line,
                        'depth' => $depth
                    ];
                }
            }
        }

        // 파이프 마커 추가 (블록 시작)
        $blocks[] = [
            'type' => 'pipe',
            'depth' => $depth + 1
        ];

        $content = $m[3][0];
        $children = parse_blocks_with_loose_text($content, $depth + 1);
        $blocks = array_merge($blocks, $children);

        // 파이프 마커 추가 (블록 종료)
        $blocks[] = [
            'type' => 'pipe',
            'depth' => $depth + 1
        ];

        $offset = $end_pos;
    }

    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $line = trim($line);
            if (!preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\]$/", $line)) {
                $blocks[] = [
                    'type' => 'text',
                    'content' => $line,
                    'depth' => $depth
                ];
            }
        }
    }

    return $blocks;
}

function render_tree_plain($blocks) {
    $html = "";
    foreach ($blocks as $block) {
        $indent_px = 40 * $block['depth'];
        $indent_bar = str_repeat("|&nbsp;&nbsp;&nbsp;&nbsp;", $block['depth']);

        if ($block['type'] === 'pipe') {
            $html .= "<div style='color: red; padding-left: {$indent_px}px;'>|</div>";
        } elseif ($block['type'] === 'text') {
            $line = htmlspecialchars($block['content']);
            $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px;'>$indent_bar $line</div>";
            $html .= "<div style='padding-left: {$indent_px}px;'><textarea rows='2' style='width: calc(100% - {$indent_px}px); margin-bottom: 10px;'></textarea></div>";
        }
    }
    return $html;
}

$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo "<div class='problem-id' style='font-weight:bold; font-size:18px; margin-bottom: 20px;'>문제 번호: " . htmlspecialchars($sid) . "</div>";

$block_tree = parse_blocks_with_loose_text($file_contents);
$html_output = render_tree_plain($block_tree);

echo "<div class='code-container' style='font-family: monospace; line-height: 1.5; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
