<?php
include("include/db_info.inc.php");
include("guideline_common.php"); // ✨ 공통 파일 사용

// $problem_id = 1295; 
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;

$file_path = "/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{$problem_id}_step2.txt";
$tagged_path = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/{$problem_id}_step2.txt";

$guideline_contents = file_get_contents($file_path);
$tagged_contents = file_get_contents($tagged_path);

$OJ_BLOCK_TREE = guidelineFilter($guideline_contents);
$OJ_CORRECT_ANSWERS = codeFilter($tagged_contents);
$OJ_SID = "STEP 2";
//태그가 제거된 가이드라인
// $OJ_NoTagGuideline = guidelineOnlyTagFilter($guideline_contents);

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;
$default_img = "./image/basic.png";
$index_start = 0;
$index_end = 0;

include("template/syzoj/guideline2.php");//렌더링 파일 불러오기
?>
