<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 설명 텍스트 및 정답 태그 코드 파일
$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);

// ✅ 설명 파일 트리 구조 파싱
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

        $children = parse_blocks_with_loose_text($content, $depth + 1);
        array_unshift($children, ['type' => 'text', 'content' => "[{$type}_start({$idx})]", 'depth' => $depth + 1]);
        array_push($children, ['type' => 'text', 'content' => "[{$type}_end({$idx})]", 'depth' => $depth + 1]);

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

// ✅ 태그 블록 추출
function extract_tagged_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\d+)\)\]|\[(func_def|rep|cond|self|struct|construct)_end\\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $stack = [];
    $blocks = [];

    foreach ($matches[0] as $match) {
        $full = $match[0];
        $pos = $match[1];

        if (strpos($full, '_start(') !== false) {
            preg_match("/\[(\w+)_start\((\d+)\)\]/", $full, $m);
            $stack[] = ['type' => $m[1], 'index' => (int)$m[2], 'start' => $pos + strlen($full), 'pos' => $pos];
        } elseif (strpos($full, '_end(') !== false) {
            preg_match("/\[(\w+)_end\((\d+)\)\]/", $full, $m);
            $type = $m[1];
            $index = (int)$m[2];

            for ($j = count($stack) - 1; $j >= 0; $j--) {
                if ($stack[$j]['type'] === $type && $stack[$j]['index'] === $index) {
                    $start = $stack[$j]['start'];
                    $content = substr($text, $start, $pos - $start);
                    $blocks[] = ['type' => $type, 'index' => $index, 'content' => trim($content)];
                    array_splice($stack, $j, 1);
                    break;
                }
            }
        }
    }

    usort($blocks, fn($a, $b) => $a['index'] <=> $b['index']);
    return $blocks;
}

//라인 태그 마주치면 그 안에 내용 추출(빈 내용의 경우 무시)
function extract_tagged_code_lines($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $blocks = [];
    $positions = [];

    // 태그 위치 수집
    foreach ($matches[0] as $i => $match) {
        $full_tag = $match[0];
        $pos = $match[1];
        $type = $matches[1][$i][0];
        $kind = $matches[2][$i][0];
        $index = (int)$matches[3][$i][0];

        $positions[] = [
            'type' => $type,
            'kind' => $kind,
            'index' => $index,
            'pos' => $pos,
            'end' => $pos + strlen($full_tag)
        ];
    }

    // 태그 간 영역 추출
    $lines = [];
    for ($i = 0; $i < count($positions) - 1; $i++) {
        $start_pos = $positions[$i]['end'];
        $end_pos = $positions[$i + 1]['pos'];
        $code_block = substr($text, $start_pos, $end_pos - $start_pos);

        foreach (explode("\n", $code_block) as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                $lines[] = ['content' => $trimmed];
            }
        }
    }

    return $lines;
}


// ✅ 환경변수
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = $sid;

// ✅ 출력
include("template/$OJ_TEMPLATE/guideline1.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
