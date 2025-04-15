<?php
// ✅ 헤더 파일 포함 (공통 레이아웃 구성 등)
include("template/syzoj/header.php");

// ✅ 데이터베이스 연결 설정 포함
include("include/db_info.inc.php");

// ✅ 문제 설명 텍스트 파일 경로
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path); // ✅ 누락된 부분 보완

// ✅ 정답 코드 줄 단위로 불러오기
$txt_path = "/home/Capstone_Design_Troy/test/tagged_code.txt";
$txt_contents = file_get_contents($txt_path);

$answer_lines = explode("\n", $txt_contents);
$correct_answers = [];

foreach ($answer_lines as $line) {
    $trimmed = trim($line);
    if ($trimmed !== "" && strpos($trimmed, "#include") !== 0) {
        $correct_answers[] = $trimmed;
    }
}

// ✅ 문제 파일 파싱 함수 정의
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

//정답 코드 파싱
function build_correct_answer_tree_from_lines($lines) {
    $stack = [];
    $root = [];
    $current = &$root;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === "" || strpos($trimmed, "#include") === 0) {
            continue;
        }

        // 🔍 시작 태그인 경우
        if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]$/", $trimmed, $m)) {
            $type = $m[1];
            $index = (int)$m[2];

            $new_block = [
                'type' => $type,
                'index' => $index,
                'depth' => count($stack),
                'children' => []
            ];

            $current[] = $new_block;
            $stack[] = &$current;
            $current = &$current[count($current) - 1]['children'];
        }
        // 🔍 끝 태그인 경우
        elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]$/", $trimmed)) {
            $current = &$stack[count($stack) - 1];
            array_pop($stack);
        }
        // 💬 일반 코드줄
        else {
            $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
            $current[] = [
                'type' => 'text',
                'content' => $trimmed,
                'depth' => count($stack) + $indent_level
            ];
        }
    }

    return $root;
}


// ✅ 파라미터에서 문제 ID 획득
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';

// ✅ 트리 구조 파싱
$block_tree = parse_blocks_with_loose_text($file_contents);

// ✅ 렌더링에 필요한 변수 설정
$answer_index = 0;
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = $correct_answers; // ✅ 줄 배열로 유지

// ✅ HTML 출력
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
