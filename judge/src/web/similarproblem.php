<?php
// src/web/similarproblem.php
require_once("./include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    $error_message = "Invalid solution_id";
    require("template/$OJ_TEMPLATE/similarproblem.php");
    exit;
}

// 사용자 코드 가져오기
$sql = "SELECT source FROM source_code_user WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($user_code);
$stmt->fetch();
$stmt->close();

if (empty($user_code)) {
    $error_message = "No source found for this solution.";
    require("template/$OJ_TEMPLATE/similarproblem.php");
    exit;
}

// 미리 준비된 문제 코드와 제목 목록 불러오기 (로컬 JSON 파일 또는 DB)
$problem_data = json_decode(file_get_contents("./similar_problem_dataset.json"), true);

function calc_similarity($a, $b) {
    similar_text($a, $b, $percent);
    return $percent;
}

$results = [];
foreach ($problem_data as $item) {
    $sim = calc_similarity($user_code, $item['code']);
    $item['similarity'] = $sim;
    $results[] = $item;
}

usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
$top3 = array_slice($results, 0, 3);

require("template/$OJ_TEMPLATE/similarproblem.php");
