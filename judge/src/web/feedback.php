<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php");

// solution_id 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "잘못된 요청입니다. solution_id가 필요합니다.";
    exit;
}

// 1. source_code 테이블에서 해당 solution_id 존재 여부 확인
$sql = "SELECT solution_id FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($existing_solution_id);
$stmt->fetch();
$stmt->close();

if (!$existing_solution_id) {
    echo "해당 solution_id를 찾을 수 없습니다.";
    exit;
}

// 2. solution 테이블에 삽입 또는 업데이트
$sql = "INSERT INTO solution (solution_id) VALUES (?) ON DUPLICATE KEY UPDATE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $solution_id, $solution_id);
$stmt->execute();
$stmt->close();

echo "<p>✅ solution 테이블에 solution_id가 삽입되었습니다.</p>";

// 3. solution 테이블에서 problem_id, user_id 등 정보 가져오기
$sql = "SELECT problem_id, user_id FROM solution WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($problem_id, $user_id);
$stmt->fetch();
$stmt->close();

if (!$problem_id || !$user_id) {
    echo "solution 정보에서 problem_id 또는 user_id를 찾을 수 없습니다.";
    exit;
}

// 4. 같은 구간의 feedback 출력 (여기선 같은 problem_id를 구간으로 정의)
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
