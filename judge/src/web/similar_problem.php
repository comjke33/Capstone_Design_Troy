<?php
require_once('./include/db_info.inc.php');

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    die("❌ 유효하지 않은 요청입니다.");
}

// 문제 ID 조회
$sql = "SELECT problem_id FROM solution WHERE solution_id = ?";
$res = pdo_query($sql, $solution_id);
if (empty($res)) {
    die("❌ 해당 제출이 존재하지 않습니다.");
}
$problem_id = $res[0][0];

// 태그 목록 가져오기
$sql = "SELECT t.name FROM tag t
        INNER JOIN problem_tag pt ON t.tag_id = pt.tag_id
        WHERE pt.problem_id = ?";
$res = pdo_query($sql, $problem_id);
$tags = array_column($res, "name");

if (empty($tags)) {
    echo "❌ 태그 정보가 없습니다.";
    exit;
}

// Python recommend.py 실행
$escaped_tags = array_map('escapeshellarg', $tags);
$cmd = "python3 ./py/recommend.py " . implode(" ", $escaped_tags);
exec($cmd, $output, $retval);

// 출력
echo "<h3>🔖 현재 문제의 태그</h3><ul>";
foreach ($tags as $t) {
    echo "<li>" . htmlspecialchars($t) . "</li>";
}
echo "</ul><br><h3>🔍 유사한 Codeup 문제 추천 결과</h3><ul>";

// Python 스크립트의 결과 줄마다 파싱
foreach ($output as $line) {
    $parts = explode("||", $line);
    if (count($parts) !== 5) continue;
    list($pid, $title, $score, $link, $tag_str) = $parts;
    echo "<li><a href='$link' target='_blank'>[$pid] $title (유사도: $score)</a><br>";
    echo "📎 태그: " . htmlspecialchars($tag_str) . "</li><br>";
}
echo "</ul>";
?>
