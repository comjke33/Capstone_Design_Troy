<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "❌ 잘못된 요청입니다. solution_id가 필요합니다.";
    exit;
}

// 2. source_code 테이블에서 해당 solution_id 존재 여부 확인
$sql = "SELECT solution_id FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo "❌ 해당 solution_id($solution_id)를 source_code에서 찾을 수 없습니다.";
    $stmt->close();
    exit;
}

$stmt->bind_result($existing_solution_id);
$stmt->fetch();
$stmt->close();

// solution 테이블에 solution_id 삽입 또는 업데이트
$sql = "INSERT INTO solution (solution_id) VALUES (?) ON DUPLICATE KEY UPDATE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $solution_id, $solution_id);
$stmt->execute();
$stmt->close();

echo "<p>✅ solution 테이블에 solution_id <strong>$solution_id</strong> 가 삽입되었습니다.</p>";


// 5. 같은 구간의 feedback 출력 (같은 problem_id를 구간으로 정의)
echo "<h3>💬 관련 피드백 목록 (problem_id = $problem_id)</h3>";

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
    echo "<p>📭 관련 피드백이 없습니다.</p>";
}
?>
