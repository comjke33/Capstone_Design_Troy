<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// solution_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// 디버깅: solution_id 값 확인
echo "Solution ID: " . $solution_id;  // 이 값을 확인하여 실제로 URL에서 solution_id가 전달되는지 확인

if ($solution_id > 0) {
    // solution_id에 해당하는 피드백 조회
    $sql = "SELECT feedback FROM solution WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->bind_result($feedback);
    $stmt->fetch();
    $stmt->close();

    // 디버깅: feedback 값 확인
    echo "Feedback: " . $feedback; // 이 값을 확인하여 피드백이 잘 가져와졌는지 확인

    if (!$feedback) {
        // 피드백이 없다면 메시지 출력
        $feedback = "피드백을 찾을 수 없습니다.";
    }
} else {
    // solution_id가 없을 경우 오류 메시지
    $feedback = "잘못된 요청입니다. solution_id가 필요합니다.";
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>피드백</title>
</head>
<body>
    <h1>제출 피드백</h1>
    <p><?php echo $feedback; ?></p>
</body>
</html>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>