<?php
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
if (!$sid) {
    echo "<p style='color:red; text-align:center;'>problem_id가가 전달되지 않았습니다.</p>";
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

        .container .btn {
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

        .container .btn:hover {
            background-color: #45a049;
        }

        h2 {
            text-align: center;
            margin-bottom: 40px;
        }

        .back {
            display: inline-block;
            background-color: red; /* 배경 빨간색 */
            color: white; /* 텍스트(화살표) 하얀색 */
            padding: 12px 24px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            font-size: 20px; /* 글자 크기 */
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back:hover {
            background-color: #d32f2f; /* hover 시 어두운 빨간색 */
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

    <a class="back" href="problem.php?id=<?php echo $sid; ?>">⬅<</a>

</body>
</html>
