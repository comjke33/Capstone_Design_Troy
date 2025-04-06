<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// solution_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

if ($solution_id > 0) {
    // solution_id에 해당하는 피드백 조회
    $sql = "SELECT feedback FROM solution WHERE solution_id = ?"; // solution_id로 변경
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id); // solution_id 사용
    $stmt->execute();
    $stmt->bind_result($feedback);
    $stmt->fetch();
    $stmt->close();
} else {
    // solution_id가 없을 경우 오류 메시지
    $feedback = "피드백을 찾을 수 없습니다.";
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
    <p>
        <?php echo $feedback; ?>
    </p>
</body>
</html>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
