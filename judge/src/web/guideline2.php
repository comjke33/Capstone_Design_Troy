<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ step 파라미터 받기
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// ✅ step별 파일 지정
switch ($step) {
    case 1:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline_code1.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
        break;
    case 2:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline_code2.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code2.txt";
        break;
    case 3:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline_code3.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code3.txt";
        break;
    default:
        die("Invalid step.");
}

// ✅ 경로를 글로벌 변수로 넘긴다
$GLOBALS['guideline_file'] = $guideline_file;
$GLOBALS['tagged_file'] = $tagged_file;

// ✅ guideline2.php 호출 (자동으로 파일 읽고 렌더링)
include("guideline2.php");
?>
