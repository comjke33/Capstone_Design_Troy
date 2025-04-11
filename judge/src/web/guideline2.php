<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. problem_id 가져오기 및 검증
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
if ($problem_id <= 0) {
    echo "<p style='color:red; text-align:center;'>❌ 잘못된 요청입니다. problem_id가 필요합니다.</p>";
    exit;
}

$file_path = "/home/Capstone_Design_Troy/test.txt";

if (!file_exists($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일이 존재하지 않습니다.</p>";
} elseif (!is_readable($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일에 읽기 권한이 없습니다.</p>";
} else {
  $file_contents = file_get_contents($file_path);
  
  // Split the content by lines or specific markers
  $sections = explode("\n", $file_contents);  // Split content by newlines
  
  echo "<div class='content-box'>";

  // Loop through each section and display it in a styled format
  foreach ($sections as $line) {
      // Check if the line contains a specific marker like [func_def_start]
      if (strpos($line, "[func_def_start]") !== false) {
          echo "<div class='section-header'>[func_def_start]</div>";
      } elseif (strpos($line, "[self_start]") !== false) {
          echo "<div class='section-header'>[self_start]</div>";
      } elseif (strpos($line, "[rep_start]") !== false) {
          echo "<div class='section-header'>[rep_start]</div>";
      } else {
          // Display the content inside the section
          echo "<div class='section-content'>" . nl2br($line) . "</div>";
      }
  }

  echo "</div>";
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

        .content-box {
            background-color: #ffffff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .section-header {
            font-size: 20px;
            color: #007bff;
            margin-top: 20px;
            font-weight: bold;
        }

        .section-content {
            margin-left: 20px;
            padding: 10px;
            background-color: #e9f7fe;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }

        .section-content p {
            font-size: 16px;
            line-height: 1.5;
        }

    </style>
</head>
<body>
</body>
</html>
