<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test3.txt";
$file_contents = file_get_contents($file_path);

// 정답 배열 (문제에 맞게 수정)
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

$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$block_tree = parse_blocks_with_loose_text($file_contents);

// 👉 변수 전달
$answer_index = 0;
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = $correct_answers;

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
