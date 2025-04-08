<?php

$sid = isset($_GET['solution_id']) ? urlencode($_GET['solution_id']) : '';
if (!$sid) {
    echo "<p style='color:red; text-align:center;'>solution_id가 전달되지 않았습니다.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>출력 방식 선택</title>
    <style>
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 70vh;
        }

        .btn {
            padding: 12px 24px;
            margin: 10px;
            font-size: 18px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            min-width: 200px;
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #45a049;
        }

        h2 {
            text-align: center;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>출력 방식을 선택하세요</h2>
        <a class="btn" href="linefeedback.php?solution_id=<?= $sid ?>">한 줄씩</a>
        <a class="btn" href="paragraphfeedback.php?solution_id=<?= $sid ?>">한 문단씩</a>
        <a class="btn" href="showfeedback.php?solution_id=<?= $sid ?>">전체</a>
    </div>
</body>
</html>

