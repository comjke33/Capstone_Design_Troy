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
if (!file_exists($file_path)) {
    echo "❌ test.txt 파일을 찾을 수 없습니다.";
    exit;
}

// 파일 내용을 읽어옴
$feedback_code = file_get_contents($file_path);

// 3. feedback 테이블에 피드백 삽입
$sql = "INSERT INTO feedback (problem_id, feedback_code) VALUES (?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $problem_id, $feedback_code);
$stmt->execute();
$stmt->close();
?>
