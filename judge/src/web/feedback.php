<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. solution_id 가져오기 및 검증
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "❌ 잘못된 요청입니다. solution_id가 필요합니다.";
    exit;
}

// 2. solution 테이블에서 solution_id 유효성 확인
$sql = "SELECT 1 FROM solution WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    echo "<p>❌ solution_id <strong>$solution_id</strong>에 해당하는 제출을 찾을 수 없습니다.</p>";
    $stmt->close();
    exit;
}
$stmt->close();

// 3. feedback 테이블에서 solution_id 기준으로 피드백 가져오기
echo "<h3>해당 제출(solution_id = $solution_id)에 대한 피드백</h3>";

$sql = "SELECT feedback_text, line_number FROM feedback WHERE solution_id = ? ORDER BY line_number ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
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
