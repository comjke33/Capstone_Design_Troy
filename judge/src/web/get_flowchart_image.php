<?php
require_once "include/db_info.inc.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

header("Content-Type: application/json");

if ($problem_id <= 0 || $index < 0) {
    echo json_encode(['success' => false, 'message' => 'invalid input']);
    exit;
}

// DB에서 이미지 주소 가져오기
$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

// 기본 이미지 (웹에서 접근 가능한 경로)
$default_img = "/image/default.jpg";

// 주소가 있으면 변환
if (count($res) > 0 && !empty($res[0]['png_address'])) {
    $filename = basename($res[0]['png_address']);  // 내부 경로에서 파일명만 추출
    $url = "/flowcharts/" . $filename . ".png";     // 웹 경로로 조립
} else {
    $url = $default_img;
}

echo json_encode(['success' => true, 'url' => $url]);
