<?php
// 피드백 페이지 처리 코드

// DB 연결 설정
require_once '../feedback.php'; // 실제 PHP 파일을 불러옴

?>

<!-- jQuery 로드 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap 로드 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Semantic UI 로드 -->
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.1/dist/semantic.min.js"></script>


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
