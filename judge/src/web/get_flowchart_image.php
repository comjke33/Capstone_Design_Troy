<?php
require_once "include/db_info.inc.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

header("Content-Type: application/json");

if ($problem_id <= 0 || $index < 0) {
    echo json_encode(['success' => false, 'message' => 'invalid input']);
    exit;
}

// DB에서 png_address 조회
$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

// 브라우저에서 접근 가능한 기본 이미지 경로
$default_img = "/image/default.jpg";

// 변환 처리
if (count($res) > 0 && !empty($res[0]['png_address'])) {
    $raw_path = $res[0]['png_address'];  // 예: /home/.../flowcharts/9944_1
    $filename = basename($raw_path) . ".png";  // 9944_1.png
    $url = "/flowcharts/" . $filename;         // 웹 접근 경로
} else {
    $url = $default_img;
}

echo json_encode(['success' => true, 'url' => $url]);
