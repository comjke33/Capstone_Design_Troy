<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// 1. 중첩 블록 구조를 트리로 파싱하기 위한 재귀 파서 정의
function parse_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct)_start\\((\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct)_end\\(\\2\\)\]/s";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $full = $m[0][0];
        $start_pos = $m[0][1];
        $end_pos = $start_pos + strlen($full);

        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];

        $children = parse_blocks($content); // 재귀 분석

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'content' => $content,
            'children' => $children
        ];

        $offset = $end_pos;
    }

    return $blocks;
}

function render_tree($blocks) {
    $html = "";

    foreach ($blocks as $block) {
        $color_map = [
            'func_def' => '#e0f7fa',
            'rep' => '#fce4ec',
            'cond' => '#e8f5e9',
            'self' => '#fff9c4',
            'struct' => '#ffecb3'
        ];

        $color = $color_map[$block['type']];
        $title = strtoupper($block['type']) . " 블록: " . $block['index'];

        $html .= "<div style='background-color: $color; padding: 15px; margin-bottom: 20px; border-radius: 8px;'>";
        $html .= "<h4>$title</h4>";

        // 문장 출력 (children이 없다면 내부를 문장 단위로 출력)
        if (empty($block['children'])) {
            $sentences = preg_split('/(?<=\.)\s*/u', trim($block['content']), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sentences as $s) {
                $s = trim($s);
                if ($s === '') continue;
                $html .= "<div style='margin-bottom: 10px;'>" . htmlspecialchars($s) . "</div><textarea rows='3' style='width: 100%;'></textarea>";
            }
        } else {
            // 자식 블록 재귀 출력
            $html .= render_tree($block['children']);
        }

        $html .= "</div>";
    }

    return $html;
}

// 2. 트리로 파싱
$block_tree = parse_blocks($file_contents);

// 3. 렌더링
$html_output = render_tree($block_tree);

echo "<div class='code-container' style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
