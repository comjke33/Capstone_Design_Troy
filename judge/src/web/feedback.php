<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// solution_id와 user_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
$user_id = isset($_GET['user_id']) ? htmlspecialchars($_GET['user_id'], ENT_QUOTES) : '';

// 디버깅: solution_id와 user_id 값 확인
echo "Solution ID: " . $solution_id;
echo "User ID: " . $user_id;

// solution_id와 user_id에 해당하는 피드백 조회
if ($solution_id > 0 && !empty($user_id)) {
    // prepared statement로 쿼리 실행
    $sql = "SELECT feedback FROM solution WHERE solution_id = ? AND user_id = ?";
    
    // 데이터베이스 연결을 확인하고 쿼리 실행
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("is", $solution_id, $user_id);  // "is" -> solution_id는 int, user_id는 string
        $stmt->execute();
        $stmt->bind_result($feedback);
        $stmt->fetch();
        $stmt->close();

        // 디버깅: feedback 값 확인
        // echo "Feedback: " . $feedback;

        // 피드백이 없다면 기본 메시지 설정
        if (!$feedback) {
            $feedback = "피드백을 찾을 수 없습니다.";
        }
    } else {
        // 쿼리 준비 실패 시 오류 처리
        $feedback = "피드백 조회 중 오류가 발생했습니다.";
    }
} else {
    // solution_id 또는 user_id가 유효하지 않으면 오류 메시지 출력
    $feedback = "잘못된 요청입니다. solution_id와 user_id가 필요합니다.";
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
