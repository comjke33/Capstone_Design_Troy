<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);

function extract_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
    
    $blocks = [];
    for ($i = 0; $i < count($matches[0]); $i++) {
        $start_tag = $matches[1][$i][0];
        $index = $matches[2][$i][0];
        $inner_content = $matches[3][$i][0];

        $desc = trim($inner_content);
        $desc = preg_split("/\n+/", $desc);
        $blocks[] = [
            'type' => $start_tag,
            'index' => (int)$index,
            'desc' => array_map('trim', $desc),
        ];
    }
    return $blocks;
}

function extract_tagged_code_lines($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\\((\\d+)\\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $positions = [];
    foreach ($matches[0] as $match) {
        $positions[] = [
            'pos' => $match[1],
            'end' => $match[1] + strlen($match[0])
        ];
    }

    $lines = [];
    for ($i = 0; $i < count($positions); $i++) {
        $start = $positions[$i]['end'];
        $end = isset($positions[$i + 1]) ? $positions[$i + 1]['pos'] : strlen($text);
        $block = substr($text, $start, $end - $start);

        foreach (explode("\n", $block) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $lines[] = ['content' => $line];
            }
        }
    }
    return $lines;
}

$OJ_SID = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$OJ_BLOCKS = extract_blocks($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);

include("template/$OJ_TEMPLATE/guideline1.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
