<?php
// 피드백 페이지 처리 코드

// DB 연결 설정
require_once '../../include/db_info.inc.php'; // DB 설정 파일 (DB 연결 포함)

// solution_id가 전달되었는지 확인
$solution_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : null;

if ($solution_id) {
    // DB에서 해당 solution_id에 대한 피드백 가져오기
    $sql = "SELECT feedback FROM solution WHERE solution_id = ?"; // solution 테이블에서 solution_id로 피드백 조회
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id);  // solution_id를 바인딩하여 전달
    $stmt->execute();
    $stmt->bind_result($feedback);
    $stmt->fetch();
    $stmt->close();  // 연결 종료
    
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
