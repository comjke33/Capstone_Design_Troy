<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 설명 파일 및 정답 코드 파일 읽기
$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/tagged_code.txt";
$txt_contents = file_get_contents($txt_path);

// ✅ 설명 파일 파싱 함수
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

function extract_tagged_code_lines($text) {
    $blocks = extract_tagged_blocks($text);
    $lines = [];

    foreach ($blocks as $block) {
        foreach (explode("\n", $block['content']) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            $lines[] = ['content' => $trimmed]; // ✅ 객체 형식으로 감싸기
        }
    }

    return $lines;
}



// ✅ 변수 설정
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$block_tree = parse_blocks_with_loose_text($guideline_contents);
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);

// ✅ 출력
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
