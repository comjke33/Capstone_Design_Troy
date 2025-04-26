<?php
// ✅ 파일 경로 설정
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

switch ($step) {
    case 1:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
        break;
    case 2:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline2.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code2.txt";
        break;
    case 3:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline3.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code3.txt";
        break;
    default:
        die("Invalid step.");
}

// ✅ 파일 읽기
$guideline_contents = file_get_contents($guideline_file);
$tagged_contents = file_get_contents($tagged_file);

// ✅ 파싱 함수
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

function extract_tagged_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\d+)\)\]|\[(func_def|rep|cond|self|struct|construct)_end\\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
    $stack = [];
    $blocks = [];
    foreach ($matches[0] as $i => $match) {
        $full = $match[0];
        $pos = $match[1];
        if (strpos($full, '_start(') !== false) {
            preg_match("/\[(\w+)_start\((\d+)\)\]/", $full, $m);
            $stack[] = [
                'type' => $m[1],
                'index' => intval($m[2]),
                'start' => $pos + strlen($full),
                'token_pos' => $pos
            ];
        } elseif (strpos($full, '_end(') !== false) {
            preg_match("/\[(\w+)_end\((\d+)\)\]/", $full, $m);
            $type = $m[1];
            $index = intval($m[2]);
            for ($j = count($stack) - 1; $j >= 0; $j--) {
                if ($stack[$j]['type'] === $type && $stack[$j]['index'] === $index) {
                    $start = $stack[$j]['start'];
                    $token_pos = $stack[$j]['token_pos'];
                    $end = $pos;
                    $content = substr($text, $start, $end - $start);
                    $blocks[] = [
                        'type' => $type,
                        'index' => $index,
                        'content' => trim($content),
                        'pos' => $token_pos
                    ];
                    array_splice($stack, $j, 1);
                    break;
                }
            }
        }
    }
    usort($blocks, fn($a, $b) => $a['pos'] <=> $b['pos']);
    return array_map(fn($b) => [
        'type' => $b['type'],
        'index' => $b['index'],
        'content' => $b['content']
    ], $blocks);
}

// ✅ 최종 데이터
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_blocks($tagged_contents);
$OJ_SID = "STEP {$step}";

// ✅ 이제 아까 너가 준 본문 출력 부분 (left-panel, right-panel, submit 기능) 이어서 사용
?>
