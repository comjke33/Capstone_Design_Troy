<?php
// 피드백 페이지 처리 코드
require_once '../feedback.php'; // 실제 PHP 파일을 불러옴

// solution_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// solution_id가 유효하지 않으면 기본 메시지 설정
if ($solution_id <= 0) {
    $feedback = "잘못된 요청입니다. solution_id가 필요합니다.";
} else {
    // solution_id에 해당하는 피드백을 가져오는 로직을 수행
    // feedback.php에서 피드백을 조회하고 그 값을 $feedback에 저장해놓음
    if (isset($feedback) && !empty($feedback)) {
        // 피드백이 정상적으로 조회된 경우
        $feedback = $feedback;
    } else {
        // 피드백이 없다면 기본 메시지
        $feedback = "피드백을 찾을 수 없습니다.";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>피드백</title>
</head>
<body>
    <h1>제출 피드백</h1>
    <p>
        <?php
        // 피드백 데이터 출력
        if (isset($feedback)) {
            echo $feedback;
        } else {
            echo "피드백을 찾을 수 없습니다.";
        }
        ?>
    </p>
</body>
</html>
