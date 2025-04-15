<?php
// ✅ 헤더 및 DB 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 문제 파일 (문제 설명 + 태그 포함 구조)
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// ✅ 정답 코드 파일 (정답 코드만)
$txt_path = "/home/Capstone_Design_Troy/test/tagged_code.txt";
$txt_contents = file_get_contents($txt_path);

// ✅ 정답 줄별 처리
$answer_lines = explode("\n", $txt_contents);
$filtered_lines = [];

foreach ($answer_lines as $line) {
    $trimmed = trim($line);
    if ($trimmed !== "" && strpos($trimmed, "#include") !== 0) {
        $filtered_lines[] = $trimmed;
    }
}

// ✅ 정답 트리 구조로 변환
$answer_code_string = implode("\n", $filtered_lines);
$correct_answer_tree = parse_blocks_with_loose_text($answer_code_string);

// ✅ 문제 트리 파싱
$block_tree = parse_blocks_with_loose_text($file_contents);

// ✅ 트리 파싱 함수 정의
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

// ✅ URL 파라미터
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';

// ✅ 출력용 변수 설정
$answer_index = 0;
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = $correct_answer_tree; // ✅ 정답도 트리 구조로 전달

// ✅ 템플릿 렌더링
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
