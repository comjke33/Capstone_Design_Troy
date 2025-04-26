<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ step 값 받기 (기본은 1)
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// ✅ step 값에 따라 파일 경로 설정
switch ($step) {
    case 1:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code1.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code1.txt";
        break;
    case 2:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code2.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code2.txt";
        break;
    case 3:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code3.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code3.txt";
        break;
    default:
        die("Invalid step.");
}

// ✅ 파일 경로를 글로벌 변수로 설정
$GLOBALS['guideline_file'] = $guideline_file;
$GLOBALS['tagged_file'] = $tagged_file;
$GLOBALS['current_step'] = $step;

// ✅ guideline2.php 호출
include("guideline2.php");

include("template/syzoj/footer.php");
?>
