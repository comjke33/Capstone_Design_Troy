<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

function parse_blocks_with_loose_text($text, $depth = 0, $parent_type = 'text') {
    $pattern = "/\[(func_def|rep|cond|self|struct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $blocks[] = [
                    'type' => 'text',
                    'content' => $line,
                    'depth' => $depth
                ];
            }
        }

        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];
        $start_tag = "[{$type}_start({$idx})]";
        $end_tag = "[{$type}_end({$idx})]";

        $children = [];
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $children[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth + 1
            ];
        }

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => array_merge(
                [[
                    'type' => 'text',
                    'content' => $start_tag,
                    'depth' => $depth + 1
                ]],
                $children,
                [[
                    'type' => 'text',
                    'content' => $end_tag,
                    'depth' => $depth + 1
                ]]
            )
        ];

        $offset = $end_pos;
    }

    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $blocks[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth
            ];
        }
    }

    return $blocks;
}

function render_tree_plain($blocks) {
    $html = "";
    foreach ($blocks as $block) {
        $indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $block['depth']);
        if (isset($block['children'])) {
            $title = strtoupper($block['type']) . " 블록 (ID: {$block['index']})";
            $html .= "<div style='margin-bottom:8px;'>$indent<b>$title</b></div>";
            $html .= render_tree_plain($block['children']);
        } else {
            $line = htmlspecialchars(trim($block['content']));
            if ($line !== '') {
                $html .= "<div style='margin-bottom:4px;'>$indent$line</div>";
                $html .= "<textarea rows='2' style='width: 100%; margin-bottom: 10px;'></textarea>";
            }
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
