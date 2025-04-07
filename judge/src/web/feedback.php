<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// user_id 값 가져오기 (URL 파라미터에서)
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : 0;  // URL에서 user_id를 받아옵니다.

// 디버깅: user_id 값 확인
echo "User ID: " . $user_id;  // 이 값을 확인하여 실제로 URL에서 user_id가 전달되는지 확인

if ($user_id) {
    // user_id에 해당하는 피드백 조회 (solution 테이블에서 user_id 기준으로 피드백 가져오기)
    $sql = "SELECT feedback FROM solution WHERE user_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $user_id);  // user_id는 문자열이므로 "s"로 바인딩
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
    // user_id가 없을 경우 오류 메시지
    $feedback = "잘못된 요청입니다. user_id가 필요합니다.";
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
