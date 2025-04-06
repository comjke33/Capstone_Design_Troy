<?php
// 피드백 페이지 처리 코드

// DB 연결 설정
require_once 'db_config.php'; // DB 설정 파일 (DB 연결 포함)

// submission_id가 전달되었는지 확인
$submission_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : null;

if ($submission_id) {
    // DB에서 해당 submission_id에 대한 피드백 가져오기
    $sql = "SELECT feedback FROM submissions WHERE submission_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$submission_id]);
    $feedback = $stmt->fetchColumn();  // 피드백을 가져옴
    
    if ($feedback) {
        echo "<h1>제출 피드백</h1>";
        echo "<p>$feedback</p>";
    } else {
        echo "<p>해당 제출에 대한 피드백이 없습니다.</p>";
    }
} else {
    echo "<p>잘못된 요청입니다. submission_id가 필요합니다.</p>";
}
?>
