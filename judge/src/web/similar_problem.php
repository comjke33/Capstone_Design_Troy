<?php
require_once('./include/db_info.inc.php');

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

if ($solution_id <= 0) {
    die("❌ 유효하지 않은 요청입니다.");
}

// 1. 제출에서 problem_id 조회
$sql = "SELECT problem_id FROM solution WHERE solution_id = ?";
$result = pdo_query($sql, $solution_id);

if (empty($result)) {
    die("❌ 해당 제출을 찾을 수 없습니다.");
}

$problem_id = intval($result[0][0]);

// ✅ 수정된 외부 링크 설정
$external_url = "https://codeup.kr/problem.php?id={$problem_id}";

// 리디렉션
header("Location: $external_url");
exit;
?>
