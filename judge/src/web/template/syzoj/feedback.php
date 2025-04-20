<?php
// solution_id 값 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// 유효성 검사
if ($solution_id <= 0) {
    $feedback = "❌ 잘못된 요청입니다. solution_id가 필요합니다.";
} else {
    // 기능 모듈 호출 (변수 $feedback을 내부에서 셋팅)
    include_once "../showfeedback.php";  // 기능 파일 (기본 경로 기준)
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>제출 피드백</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 3em auto;
            max-width: 700px;
            padding: 1em;
            line-height: 1.6;
            background: #f9f9f9;
        }
        h1 {
            font-weight: 600;
            border-bottom: 2px solid #333;
            padding-bottom: 0.3em;
        }
        .feedback-box {
            margin-top: 1.5em;
            background: #fff;
            border: 1px solid #ddd;
            border-left: 4px solid #2185d0;
            padding: 1em;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>제출 피드백</h1>
    <div class="feedback-box">
        <?php
        if (isset($feedback) && !empty($feedback)) {
            echo $feedback;
        } else {
            echo "📭 피드백을 찾을 수 없습니다.";
        }
        ?>
    </div>
</body>
</html>
