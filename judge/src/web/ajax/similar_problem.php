<?php
require_once('../include/db_info.inc.php');

$solution_id = intval($_GET['solution_id'] ?? 0);
if (!$solution_id) {
  echo "<p>❌ 잘못된 요청입니다.</p>";
  exit;
}

// 문제 ID 가져오기
$sql = "SELECT problem_id FROM solution WHERE solution_id = ?";
$res = pdo_query($sql, $solution_id);
if (!$res) {
  echo "<p>❌ 해당 제출이 존재하지 않습니다.</p>";
  exit;
}
$problem_id = $res[0][0];

// 태그 목록 가져오기
$sql = "SELECT t.name FROM tag t 
        JOIN problem_tag pt ON pt.tag_id = t.tag_id 
        WHERE pt.problem_id = ?";
$res = pdo_query($sql, $problem_id);
$tags = array_column($res, 'name');

if (empty($tags)) {
  echo "<p>❌ 태그 정보가 없습니다.</p>";
  exit;
}

// 추천 스크립트 실행
$escaped = array_map('escapeshellarg', $tags);
$cmd = "python3 /home/Capstone_Design_Troy/py/recommend.py " . implode(" ", $escaped);
exec($cmd, $output);

foreach ($output as $line) {
  list($pid, $title, $score, $link, $tag_str) = explode("||", $line);
echo "<div style='margin-bottom:5px;'>
        <a href='$link' target='_blank' class='ui mini blue basic fluid button' style='text-align:left;'>
          [$pid] $title
        </a>
      </div>";
	}
?>
