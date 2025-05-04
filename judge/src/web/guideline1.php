<?php
include("include/db_info.inc.php");
include("guideline_common.php"); // ✨ 공통 파일 사용

$file_path = "/home/Capstone_Design_Troy/test/total_test/step1_guideline.txt";
$tagged_path = "/home/Capstone_Design_Troy/test/total_test/step1_tagged_code.txt";

if (!file_exists($file_path) || !file_exists($tagged_path)) {
    die("🚨 가이드라인 또는 정답 파일이 존재하지 않습니다.");
}

$guideline_contents = file_get_contents($file_path);
$tagged_contents = file_get_contents($tagged_path);

if ($guideline_contents === false || $tagged_contents === false) {
    die("🚨 파일 읽기 실패: 접근 권한 또는 경로 오류");
}

$OJ_BLOCK_TREE = parse_blocks($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_blocks($tagged_contents);
$OJ_SID = "STEP 1";

include("template/syzoj/guideline1.php");
?>
