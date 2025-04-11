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

// 2. test.txt 파일 읽기
$file_path = "/home/troy0012/test/test.txt";  // test.txt 파일 경로

// 경로 출력 (디버깅용)
echo "파일 경로: $file_path";  // 경로 출력

// 파일이 존재하는지 확인
if (!file_exists($file_path)) {
    echo "❌ test.txt 파일을 찾을 수 없습니다.";
    exit;
}

// 파일 내용을 읽어오기
$feedback_code = file_get_contents($file_path);

// 디버깅: 에러가 발생할 경우, 이유를 확인
if ($feedback_code === false) {
    echo "❌ test.txt 파일을 읽을 수 없습니다. 오류: " . error_get_last()['message'];
    exit;
}

// 파일을 읽을 수 있으면 출력
echo "파일 내용: $feedback_code";

?>
