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

// 2. test.txt 파일 읽기
$file_path = "/home/troy0012/test/test.txt";  // test.txt 파일 경로
if (!file_exists($file_path)) {
    echo "❌ test.txt 파일을 찾을 수 없습니다.";
    exit;
}

// 파일 내용을 읽어옴
$feedback_code = file_get_contents($file_path);

// 3. feedback 테이블에 피드백 삽입
$sql = "INSERT INTO feedback (problem_id, feedback_code) VALUES (?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $problem_id, $feedback_code);
$stmt->execute();
$stmt->close();

// 4. feedback 테이블에서 problem_id 기준으로 피드백 가져오기
echo "<h3>해당 제출(problem_id = $problem_id)에 대한 피드백</h3>";

$sql = "SELECT feedback_code, line_number FROM feedback WHERE problem_id = ? ORDER BY line_number ASC";
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
