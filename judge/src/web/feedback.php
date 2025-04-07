<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// solution_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// solution_id가 유효하지 않으면 종료
if ($solution_id <= 0) {
    $feedback = "잘못된 요청입니다. solution_id가 필요합니다.";
    echo $feedback;
    exit;
}

// 먼저, source_code 테이블에서 해당 solution_id를 확인합니다.
$sql = "SELECT user_id FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);  // "i"는 solution_id가 정수형
$stmt->execute();
$stmt->bind_result($user_id);  // user_id를 가져옴
$stmt->fetch();
$stmt->close();

// 만약 solution_id에 해당하는 user_id가 없으면 종료
if (!$user_id) {
    $feedback = "해당 solution_id에 대한 사용자 정보를 찾을 수 없습니다.";
    echo $feedback;
    exit;
}

// solution 테이블에서 피드백 조회
$sql = "SELECT feedback FROM solution WHERE solution_id = ?";
if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $solution_id);  // "i"는 solution_id가 정수형
    $stmt->execute();
    $stmt->bind_result($feedback);
    $stmt->fetch();
    $stmt->close();

    // 피드백이 없다면 기본 메시지 설정
    if (!$feedback) {
        $feedback = "피드백을 찾을 수 없습니다.";
    }
} else {
    // 쿼리 준비 실패 시 오류 처리
    $feedback = "피드백 조회 중 오류가 발생했습니다.";
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
