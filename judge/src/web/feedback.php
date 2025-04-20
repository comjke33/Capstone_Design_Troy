<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php
require_once("include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "❌ 유효하지 않은 요청입니다.";
    exit;
}

// 1. solution_id를 기준으로 feedback 조회
$sql = "SELECT problem_id, feedback_code FROM feedback WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($problem_id, $feedback_code);

$has_result = false;

echo "<h2>📋 피드백 정보</h2><ul>";

while ($stmt->fetch()) {
    $has_result = true;
    echo "<li><strong>문제 ID:</strong> $problem_id<br>";
    echo "<strong>피드백:</strong> " . nl2br(htmlspecialchars($feedback_code)) . "</li>";
}

echo "</ul>";

if (!$has_result) {
    echo "<p>📭 피드백이 존재하지 않습니다.</p>";
}

$stmt->close();
?>

<?php include("template/$OJ_TEMPLATE/footer.php");