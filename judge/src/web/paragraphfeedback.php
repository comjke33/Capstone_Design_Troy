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

$file_path = "/home/troy0012/aaa.txt";
$file_handle = fopen($file_path, "r");
if ($file_handle) {
    while (($line = fgets($file_handle)) !== false) {
        echo nl2br($line); // 줄바꿈을 HTML에서 보이도록 변환
    }
    fclose($file_handle);
} else {
    echo "파일을 열 수 없습니다.";
}


?>
