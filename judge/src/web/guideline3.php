<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");
include("guideline_common.php"); // ✨ 공통 파일 사용

$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$tagged_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";

$guideline_contents = file_get_contents($file_path);
$tagged_contents = file_get_contents($tagged_path);

$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_blocks($tagged_contents);
$OJ_SID = "STEP 1";

include("template/syzoj/guideline2.php");
?>
