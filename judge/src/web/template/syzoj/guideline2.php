<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>코드 설명 인터페이스</title>

  <style>
    body {
      background-color: #f5f5f5;
      margin: 0;
      padding: 0;
    }

    .code-container {
      font-family: 'Segoe UI', sans-serif;
      line-height: 1.7;
      padding: 30px;
      background-color: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      max-width: 960px;
      margin: 40px auto;
    }

    .problem-id {
      font-size: 1.4em;
      font-weight: bold;
      color: #222;
      text-align: center;
      margin-top: 40px;
      margin-bottom: 10px;
    }

    .back-button {
      display: block;
      width: 100px;
      margin: 10px auto 30px auto;
      padding: 10px 15px;
      background-color: #4285f4;
      color: white;
      text-align: center;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background-color 0.2s;
    }

    .back-button:hover {
      background-color: #3367d6;
    }

    .code-container h4 {
      margin-top: 0;
      padding-bottom: 10px;
      border-bottom: 2px solid #ccc;
      font-size: 1.2em;
      color: #333;
    }

    .code-container div {
      margin-bottom: 10px;
    }

    textarea {
      font-family: 'Courier New', monospace;
      font-size: 0.95em;
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 6px;
      background-color: #fff;
      resize: vertical;
      transition: box-shadow 0.2s;
    }

    textarea:focus {
      box-shadow: 0 0 5px rgba(100, 100, 255, 0.3);
      border-color: #6666ff;
      outline: none;
    }

    .block-title {
      font-weight: bold;
      margin-bottom: 8px;
    }

    .sentence-block {
      padding: 10px;
      border-radius: 4px;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

  <!-- 🔙 뒤로 가기 버튼 -->
  <a class="back-button" href="selectlevel.php?problem_id=<?php echo urlencode($sid); ?>">⬅</a>

  <!-- 출력 내용은 상위 PHP 파일에서 echo로 삽입됨 -->
</body>
</html>
