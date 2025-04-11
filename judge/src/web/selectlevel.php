<?php
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
if (!$sid) {
    echo "<p style='color:red; text-align:center;'>problem_id가 전달되지 않았습니다.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>출력 방식 선택</title>
    <style>
        /* 기본 리셋 */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif; /* 모던하고 깔끔한 글꼴 */
            background-color: #f4f4f4; /* 배경색 */
            color: #333; /* 기본 텍스트 색 */
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px; /* 상하 좌우 여백 */
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        .btn {
            padding: 12px 30px;
            margin: 8px;
            font-size: 18px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            max-width: 300px; /* 버튼의 최대 너비 */
        }

        .btn:hover {
            background-color: #45a049;
            transform: scale(1.05); /* 버튼 크기 증가 */
        }

        .back {
            display: inline-block;
            background-color: red; /* 빨간색 배경 */
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 20px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
        }

        .back:hover {
            background-color: #d32f2f; /* hover 시 어두운 빨간색 */
            transform: scale(1.05); /* 크기 증가 */
        }

        /* 모바일 화면에서 버튼 크기 조정 */
        @media (max-width: 600px) {
            .btn {
                font-size: 16px;
                padding: 10px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>출력 방식을 선택하세요</h2>
        <a class="btn" href="guideline1.php?problem_id=<?= $sid ?>">한 줄씩</a>
        <a class="btn" href="guideline2.php?problem_id=<?= $sid ?>">한 문단씩</a>
        <a class="btn" href="guideline3.php?problem_id=<?= $sid ?>">전체</a>
    </div>

    <a class="back" href="problem.php?id=<?php echo $sid; ?>">⬅</a>

</body>
</html>
