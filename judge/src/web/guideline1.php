<?php
include("include/db_info.inc.php");
include("guideline_common.php"); // ✨ 공통 파일 사용

//가이드라인 가져오기기
$file_path = "/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/1256_step1.txt";
//코드 가져오기
$tagged_path = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/1256_step1.txt";

//가이드라인 코드 추출출
$guideline_contents = file_get_contents($file_path);
//코드 추출
$tagged_contents = file_get_contents($tagged_path);

$OJ_BLOCK_TREE = guidelineFilter($guideline_contents);
$OJ_CORRECT_ANSWERS = codeFilter($tagged_contents);
$OJ_SID = "STEP 1";
//태그가 제거된 원문 추출
// $OnlyGuideline = guideline_contents($guideline_contents);

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;


include("template/syzoj/guideline1.php");//렌더링 파일 불러오기
?>
