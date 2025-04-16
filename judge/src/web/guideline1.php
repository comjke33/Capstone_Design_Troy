<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 설명 파일 및 정답 코드 파일 읽기
$file_path = "/home/Capstone_Design_Troy/test/guideline_code1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/tagged_code1.txt";
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
                'token_pos' => $pos  // ✨ 블록 위치 기록
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
                        'pos' => $token_pos  // ✨ 정렬 기준
                    ];
                    array_splice($stack, $j, 1);
                    break;
                }
            }
        }
    }

    // ✨ 파일 순서 기준으로 정렬
    usort($blocks, fn($a, $b) => $a['pos'] <=> $b['pos']);

    // ✨ 불필요한 필드 제거
    return array_map(fn($b) => [
        'type' => $b['type'],
        'index' => $b['index'],
        'content' => $b['content']
    ], $blocks);
}


// ✅ 변수 설정
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$block_tree = parse_blocks_with_loose_text($guideline_contents);
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = extract_tagged_blocks($txt_contents);

// ✅ 출력
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
