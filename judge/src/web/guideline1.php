<?php
// ✅ 헤더 및 DB 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 가이드라인 및 정답 파일 경로
$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);

// ✅ 모든 태그 블록을 파싱하는 함수
function parse_blocks_with_loose_text($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[\\1_end\\(\\2\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

        $type = $m[1][0];
        $idx = (int)$m[2][0];
        $content = $m[3][0];

        // 설명 줄 추출
        $description_lines = [];
        foreach (explode("\n", $content) as $line) {
            $trimmed = trim($line);
            if (
                $trimmed !== '' &&
                $trimmed !== '}' &&
                !preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $trimmed)
            ) {
                $description_lines[] = [
                    'type' => 'text',
                    'content' => rtrim($trimmed),
                    'depth' => $depth + 1
                ];
            }
        }

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => $description_lines
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

// ✅ 정답 코드 파싱 함수 (라인 기준)
function extract_tagged_code_lines($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $positions = [];
    foreach ($matches[0] as $i => $match) {
        $positions[] = [
            'pos' => $match[1],
            'end' => $match[1] + strlen($match[0])
        ];
    }

    $lines = [];
    $code_index = 0;
    for ($i = 0; $i < count($positions); $i += 2) {
        $start_pos = $positions[$i]['end'];
        $end_pos = $positions[$i + 1]['pos'] ?? strlen($text);
        $code_block = substr($text, $start_pos, $end_pos - $start_pos);

        foreach (explode("\n", $code_block) as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                if ($trimmed === '}') {
                    $lines[] = [
                        'content' => $trimmed,
                        'readonly' => true,
                        'info' => '닫는 괄호'
                    ];
                } else {
                    $lines[] = ['content' => $trimmed];
                }
                $code_index++;
            }
        }
    }

    return $lines;
}

// ✅ 문제 ID 파라미터
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = $sid;

// ✅ 템플릿 렌더링
include("template/$OJ_TEMPLATE/guideline1.php");
include("template/$OJ_TEMPLATE/footer.php");
