<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// 중첩 블록 트리 파싱 + 블록 외 문장도 포함
function parse_blocks_with_loose_text($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];

        // 블록 앞의 일반 텍스트도 따로 저장
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            $blocks[] = [
                'type' => 'text',
                'index' => null,
                'content' => $before_text,
                'children' => []
            ];
        }

        $full = $m[0][0];
        $end_pos = $start_pos + strlen($full);

        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];
        $children = parse_blocks_with_loose_text($content);

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'content' => $content,
            'children' => $children
        ];

        $offset = $end_pos;
    }

    // 마지막에 남은 일반 텍스트도 추가
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        $blocks[] = [
            'type' => 'text',
            'index' => null,
            'content' => $tail,
            'children' => []
        ];
    }

    return $blocks;
}

// 블록 구조를 HTML로 렌더링
function render_tree($blocks, $parent_color = '', $depth = 0) {
    $html = "";

    foreach ($blocks as $block) {
        $color_map = [
            'func_def' => '#e0f7fa',
            'rep' => '#fce4ec',
            'cond' => '#e8f5e9',
            'self' => '#fff9c4',
            'struct' => '#ffecb3',
            'text' => '#eeeeee'  // 블록 외 문장용 색상
        ];

        if ($block['type'] === 'text') $depth = 0;

        $color = $color_map[$block['type']];
        $indent = 50 * $depth;

        if (empty($block['children'])) {
            // 태그 제거 후 문장 분해
            $cleaned = preg_replace("/\\[(func_def|rep|cond|self|struct)_(start|end)\\(\\d+\\)\\]/", "", $block['content']);
            $sentences = preg_split('/(?<=\.)\s*/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sentences as $s) {
                $s = trim($s);
                if ($s === '') continue;
                $html .= "<div class='block-wrapper depth-$depth type-{$block['type']}' style='margin-left: {$indent}px; border-left: 4px solid $color; padding-left: 12px; margin-bottom: 10px;'>";
                $html .= "<div style='margin-bottom: 10px; padding: 10px; background: $color; border-radius: 4px;'>" . htmlspecialchars($s) . "</div>";
                $html .= "<textarea rows='3' style='width: 100%; margin-bottom: 10px;'></textarea>";
                $html .= "</div>";
            }
        } else {
            $html .= "<div class='block-wrapper depth-$depth type-{$block['type']}' style='margin-left: {$indent}px; border-left: 4px solid $color; padding-left: 12px; margin-bottom: 10px;'>";
            $html .= render_tree($block['children'], $color, $depth + 1);
            $html .= "</div>";
        }
    }

    return $html;
}

//문제 번호 표시
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo '<div class="problem-id">문제 번호: ' . htmlspecialchars($sid) . '</div>';

$block_tree = parse_blocks_with_loose_text($file_contents);
$html_output = render_tree($block_tree);

echo "<div class='code-container' style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
