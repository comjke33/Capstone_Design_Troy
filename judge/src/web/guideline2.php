<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// 블록 트리 구조로 파싱 + dep_on 관계 파악
function parse_blocks_with_loose_text($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct)_end\\(\\2\\)\]/s";
    $dep_pattern = "/\[dep_on\((\\d+)\)\]/";

    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];

        // 블록 앞의 일반 텍스트
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

        // dep_on 파싱
        $dep_on = null;
        if (preg_match($dep_pattern, $content, $dep_match)) {
            $dep_on = $dep_match[1];
            $content = preg_replace($dep_pattern, '', $content);
        }

        $children = parse_blocks_with_loose_text($content);

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'dep_on' => $dep_on,
            'content' => $content,
            'children' => $children
        ];

        $offset = $end_pos;
    }

    // 마지막 남은 일반 텍스트
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

// dep_on에 따라 종속 트리로 변환
function build_dependency_tree($blocks) {
    $id_map = [];
    foreach ($blocks as &$block) {
        if (!empty($block['index'])) {
            $id_map[$block['index']] = &$block;
        }
    }

    $tree = [];
    foreach ($blocks as &$block) {
        if (!empty($block['dep_on']) && isset($id_map[$block['dep_on']])) {
            $id_map[$block['dep_on']]['children'][] = &$block;
        } else {
            $tree[] = &$block;
        }
    }

    return $tree;
}

// 블록 구조를 트리로 렌더링
function render_tree($blocks, $depth = 0) {
    $html = "";
    foreach ($blocks as $block) {
        $color_map = [
            'func_def' => '#e0f7fa',
            'rep' => '#fce4ec',
            'cond' => '#e8f5e9',
            'self' => '#fff9c4',
            'struct' => '#ffecb3',
            'text' => '#eeeeee'
        ];
        $color = $color_map[$block['type']];
        $indent = 30 * $depth;
        $prefix = str_repeat("│&nbsp;&nbsp;", $depth) . ($depth > 0 ? "└── " : "");

        if (empty($block['children'])) {
            $cleaned = preg_replace("/\\[(func_def|rep|cond|self|struct)_(start|end)\\(\\d+\\)\\]/", "", $block['content']);
            $sentences = preg_split('/(?<=\.)\s*/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($sentences as $s) {
                $s = trim($s);
                if ($s === '') continue;
                $html .= "<div style='margin-left: {$indent}px; background: $color; padding: 8px; border-radius: 4px; margin-bottom: 5px; font-family: monospace;'>$prefix" . htmlspecialchars($s) . "</div>";
                $html .= "<textarea rows='3' style='width: 100%; margin-left: {$indent}px; margin-bottom: 15px;'></textarea>";
            }
        } else {
            $title = strtoupper($block['type']) . " 블록 (ID: {$block['index']})";
            $html .= "<div style='margin-left: {$indent}px; background: $color; padding: 10px; border-radius: 6px; font-weight: bold; margin-bottom: 8px; font-family: monospace;'>$prefix" . htmlspecialchars($title) . "</div>";
            $html .= render_tree($block['children'], $depth + 1);
        }
    }
    return $html;
}

// 문제 번호 표시
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo '<div class="problem-id" style="font-weight:bold; font-size:18px; margin-bottom: 20px;">문제 번호: ' . htmlspecialchars($sid) . '</div>';

// 파싱 및 렌더링
$parsed_blocks = parse_blocks_with_loose_text($file_contents);
$block_tree = build_dependency_tree($parsed_blocks);
$html_output = render_tree($block_tree);

// 출력
echo "<div class='code-container' style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
