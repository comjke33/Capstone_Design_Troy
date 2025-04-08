<?php
// 기본 설정 및 DB 연결
require_once('./include/db_info.inc.php');

// GET 파라미터로 solution_id 받기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// 유효성 검사
if ($solution_id <= 0) {
    echo "❌ 유효하지 않은 solution_id입니다.";
    exit;
}

// solution 테이블에서 문제 정보 조회
$sql = "SELECT problem_id, user_id FROM solution WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($problem_id, $user_id);
$stmt->fetch();
$stmt->close();

if (!$problem_id || !$user_id) {
    echo "❌ 해당 solution_id에 대한 정보가 존재하지 않습니다.";
    exit;
}

// 해당 문제에 대한 피드백 조회
$sql = "SELECT feedback_text, line_number FROM feedback WHERE problem_id = ? ORDER BY line_number ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $problem_id);
$stmt->execute();
$stmt->bind_result($feedback_text, $line_number);

// 출력
echo "<h2>📝 Solution ID: $solution_id에 대한 피드백</h2>";
echo "<h4>📘 문제 ID: $problem_id | 👤 사용자: $user_id</h4>";

$has_feedback = false;
echo "<ul>";
while ($stmt->fetch()) {
    $has_feedback = true;
    echo "<li><strong>Line $line_number:</strong> $feedback_text</li>";
}
echo "</ul>";

if (!$has_feedback) {
    echo "<p>✅ 이 제출에 대한 피드백이 없습니다. 잘 하셨어요!</p>";
}

$stmt->close();
?>
