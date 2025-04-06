<?php
// 피드백 데이터 불러오기
require_once '../feedback.php'; // 실제 PHP 파일을 불러옴
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
