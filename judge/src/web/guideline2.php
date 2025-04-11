<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. problem_id 가져오기 및 검증
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
if ($problem_id <= 0) {
    echo "<p style='color:red; text-align:center;'>❌ 잘못된 요청입니다. problem_id 필요합니다.</p>";
    exit;
}

$file_path = "/home/Capstone_Design_Troy/test/test.txt";

if (!file_exists($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일이 존재하지 않습니다.</p>";
} elseif (!is_readable($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일에 읽기 권한이 없습니다.</p>";
} else {
  $file_contents = file_get_contents($file_path);
  $file_contents = nl2br($file_contents); // 줄바꿈 처리
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>파일 내용 출력</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
        }

        .content-box {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 80%;
            max-width: 900px;
            overflow-y: auto;
        }

        h1 {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9f7fe;
            border-left: 4px solid #007bff;
        }

        .section h3 {
            margin-top: 0;
            font-size: 20px;
            color: #007bff;
        }

        .feedback-box {
            background-color: #d1ecf1;
            padding: 10px;
            border-left: 4px solid #17a2b8;
            margin-top: 10px;
        }

        .feedback-box p {
            margin: 0;
            font-size: 16px;
            color: #0c5460;
        }

        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="content-box">
            <h1>출력 결과</h1>

            <?php if (isset($file_contents)): ?>
                <div class="section">
                    <h3>파일 내용:</h3>
                    <div class="feedback-box">
                        <p><?php echo $file_contents; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section">
                <h3>피드백</h3>
                <div class="feedback-box">
                    <p>이 구간은 파일을 처리하는 과정에서 발생한 피드백을 보여주는 부분입니다. 파일을 성공적으로 읽었을 때, 그 내용이 이곳에 표시됩니다.</p>
                </div>
            </div>

            <div style="text-align: center;">
                <a class="btn" href="problem.php?id=<?php echo $problem_id; ?>">🔙 이전으로 돌아가기</a>
            </div>
        </div>
    </div>
</body>
</html>
