<?php
// ✅ 공통 헤더 및 DB 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 문제 설명 파일
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// ✅ 정답 코드 파일
$txt_path = "/home/Capstone_Design_Troy/test/tagged_code.txt";
$txt_contents = file_get_contents($txt_path);
$answer_lines = explode("\n", $txt_contents);

// ✅ 정답 트리 빌더 함수
function build_correct_answer_tree_from_lines($lines) {
    $stack = [];
    $root = [];
    $current = &$root;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // 🔽 헤더 또는 빈 줄은 제외
        if ($trimmed === "" || strpos($trimmed, "#include") === 0) {
            continue;
        }

        // 🔽 시작 태그
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
        // 🔽 끝 태그
        elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]$/", $trimmed)) {
            $current = &$stack[count($stack) - 1];
            array_pop($stack);
        }
        // 🔽 일반 코드 줄
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

// ✅ 문제 설명 텍스트 파서
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

// ✅ 트리 파싱
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($file_contents);                     // 문제 설명
$OJ_CORRECT_ANSWERS = build_correct_answer_tree_from_lines($answer_lines);        // 정답 트리

// ✅ 기타 전달 변수
$OJ_SID = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$answer_index = 0;

// ✅ 템플릿 출력
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
