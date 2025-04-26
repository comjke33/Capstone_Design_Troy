<?php
// ✅ 헤더, 푸터 없음

$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline3.txt";
$tagged_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code3.txt";

// 파싱 함수 복붙
function parse_blocks_with_loose_text($text, $depth = 0) { /* ... */ }
function extract_tagged_blocks($text) { /* ... */ }

$OJ_BLOCK_TREE = parse_blocks_with_loose_text(file_get_contents($file_path));
$OJ_CORRECT_ANSWERS = extract_tagged_blocks(file_get_contents($tagged_path));
$OJ_SID = "STEP 3";

include("template/syzoj/guideline2.php");
?>
