<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. problem_id 가져오기 및 검증
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
if ($problem_id <= 0) {
    echo "❌ 잘못된 요청입니다. problem_id 필요합니다.";
    exit;
}

$file_path = "/test/test.txt";

if (!file_exists($file_path)) {
  echo "파일이 존재하지 않습니다.";
} elseif (!is_readable($file_path)) {
  echo "파일에 읽기 권한이 없습니다.";
} else {
  $file_contents = file_get_contents($file_path);
  echo nl2br($file_contents); // 파일 내용 출력
}

?>
