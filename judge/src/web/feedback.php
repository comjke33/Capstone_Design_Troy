<?php
require_once("include/db_info.inc.php");

$feedback = ""; // 렌더링용 메시지
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

if ($solution_id <= 0) {
    $feedback = "❌ 잘못된 요청입니다. solution_id가 필요합니다.";
    return;
}

// problem_id 가져오기
$sql = "SELECT problem_id FROM solution WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($problem_id);
if ($stmt->fetch()) {
    $stmt->close();

    // 피드백 불러오기
    $sql = "SELECT feedback_text, line_number FROM feedback WHERE problem_id = ? ORDER BY line_number ASC";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $problem_id);
    $stmt->execute();
    $stmt->bind_result($feedback_text, $line_number);

    $has_feedback = false;
    ob_start();
    echo "<h3>해당 제출(problem_id = $problem_id)에 대한 피드백</h3><ul>";
    while ($stmt->fetch()) {
        $has_feedback = true;
        echo "<li><strong>Line $line_number:</strong> $feedback_text</li>";
    }
    echo "</ul>";
    $feedback = $has_feedback ? ob_get_clean() : "📭 이 제출에는 피드백이 없습니다.";
    $stmt->close();
} else {
    $feedback = "📭 해당 solution_id에 대한 문제를 찾을 수 없습니다.";
    $stmt->close();
}
?>
