<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);

function parse_tag_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

    $blocks = [];
    foreach ($matches as $match) {
        $type = $match[1];
        $index = (int)$match[2];
        $content = trim($match[3]);

        $lines = array_map('rtrim', explode("\n", $content));
        $blocks[] = [
            'type' => $type,
            'index' => $index,
            'lines' => $lines
        ];
    }

    usort($blocks, fn($a, $b) => $a['index'] <=> $b['index']);
    return $blocks;
}

function extract_tagged_code_lines($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\\((\\d+)\\)\]/";
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
            $lines[] = ['content' => rtrim($line)];
        }
    }

    return $lines;
}

$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$OJ_BLOCK_TREE = parse_tag_blocks($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = $sid;

include("template/$OJ_TEMPLATE/guideline1.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
