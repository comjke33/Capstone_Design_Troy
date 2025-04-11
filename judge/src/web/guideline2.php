<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. problem_id 가져오기 및 검증
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
if ($problem_id <= 0) {
    echo "❌ 잘못된 요청입니다. problem_id 필요합니다.";
    exit;
}

$file_path = "/home/Capstone_Design_Troy/test/test.txt";

if (!file_exists($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일이 존재하지 않습니다.</p>";
} elseif (!is_readable($file_path)) {
  echo "<p style='color:red; text-align:center;'>파일에 읽기 권한이 없습니다.</p>";
} else {
  $file_contents = file_get_contents($file_path);
  
  // Split the content into sections based on the square bracketed markers ([ ])
  preg_match_all('/\[(.*?)\](.*?)(?=\[|$)/s', $file_contents, $matches);
  
  // Check if matches were found
  if (isset($matches[1])) {
    echo "<div class='content-box'>";
    
    // Loop through each matched section
    foreach ($matches[1] as $index => $header) {
        $content = nl2br(trim($matches[2][$index]));  // Get content and convert newlines
        echo "<div class='section'>";
        echo "<div class='section-header'>[$header]</div>";
        echo "<div class='section-content'>$content</div>";
        echo "</div>";
    }

    echo "</div>"; // End content box
  } else {
    echo "<p style='color:red; text-align:center;'>파일 내용에서 적절한 형식의 섹션을 찾을 수 없습니다.</p>";
  }
}
?>