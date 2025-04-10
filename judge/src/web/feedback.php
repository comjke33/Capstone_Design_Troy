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


// 3. feedback 테이블에서 problem_id 기준으로 피드백 가져오기
echo "<h3>해당 제출(problem_id = $problem_id)에 대한 피드백</h3>";

$sql = "SELECT feedback_text, line_number FROM feedback WHERE problem_id = ? ORDER BY line_number ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $problem_id);
$stmt->execute();
$stmt->bind_result($feedback_text, $line_number);

$has_feedback = false;
echo "<ul>";
while ($stmt->fetch()) {
    $has_feedback = true;
    echo "<li><strong>Line $line_number:</strong> $feedback_text</li>";
}
echo "</ul>";
$stmt->close();

if (!$has_feedback) {
    echo "<p>📭 이 제출에는 피드백이 없습니다.</p>";
}
?>
