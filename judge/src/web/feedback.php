<?php
require_once('include/db_info.inc.php');
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

$feedback_error = null;
$link_result = null;

if ($solution_id <= 0) {
    $feedback_error = "❌ 유효하지 않은 요청입니다.";
} else {
    $sql = "SELECT link FROM hyperlink WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $solution_id);
        $stmt->execute();
        $stmt->bind_result($link);

        if ($stmt->fetch()) {
            // ✅ 정상적으로 link를 가져옴
            $link_result = $link;
        } else {
            // ❌ 해당 solution_id에 대한 링크 없음
            $feedback_error = "⚠️ 해당 풀이에 연결된 피드백 링크가 없습니다.";
        }

        $stmt->close();
    } else {
        $feedback_error = "❌ 데이터베이스 오류: 쿼리 준비 실패.";
    }
}
?>