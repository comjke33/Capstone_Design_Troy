<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 📌 Step 구분
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min($step, 3));

// 📌 경로 설정
$base_path = "/home/Capstone_Design_Troy/test/step{$step}/";
$guideline_file = $base_path . "guideline.txt";
$tagged_file = $base_path . "tagged_code.txt";

$guideline_contents = file_get_contents($guideline_file);
$txt_contents = file_get_contents($tagged_file);

// 📌 설명 파싱
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

// 📌 정답 추출 (라인 단위)
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
    for ($i = 0; $i < count($positions); $i++) {
        $start_pos = $positions[$i]['end'];
        $end_pos = isset($positions[$i + 1]) ? $positions[$i + 1]['pos'] : strlen($text);
        $code_block = substr($text, $start_pos, $end_pos - $start_pos);

        foreach (explode("\n", $code_block) as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '') {
                if ($trimmed === '}') {
                    $lines[] = ['content' => $trimmed, 'readonly' => true, 'info' => '닫는 괄호'];
                    $lines[] = ['content' => '', 'readonly' => false, 'info' => ''];
                } else {
                    $lines[] = ['content' => $trimmed, 'readonly' => false, 'info' => ''];
                }
            }
        }
    }

    return $lines;
}

// 📌 데이터 설정
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = "STEP {$step}";

// 📌 탭 버튼 출력
echo "<div class='ui large buttons' style='margin-bottom:2em;'>";
for ($i = 1; $i <= 3; $i++) {
    $active = ($i === $step) ? "style='background-color:#1678c2; color:white;'" : "";
    echo "<a href='guideline.php?step=$i' class='ui blue button' $active>Step $i</a>";
}
echo "</div>";

// 📌 렌더링 템플릿 호출
include("template/syzoj/guideline_render.php");

include("template/syzoj/footer.php");
?>
