<?php
include("include/db_info.inc.php");
include("guideline_common.php"); // ✨ 공통 파일 사용

$file_path = "home/Capstone_Design_Troy/test/total_test/step1_guideline.txt";
$tagged_path = "home/Capstone_Design_Troy/test/total_test/step1_tagged_code.txt";

$guideline_contents = file_get_contents($file_path);
$tagged_contents = file_get_contents($tagged_path);

$OJ_BLOCK_TREE = parse_blocks($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_blocks($tagged_contents);
$OJ_SID = "STEP 1";

include("template/syzoj/guideline1.php");//렌더링 파일 불러오기
?>
