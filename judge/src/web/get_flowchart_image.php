<?php
require_once "include/db_info.inc.php";
header("Content-Type: application/json");

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

$default_img = "../../image/default.jpg";

if ($problem_id <= 0 || $index < 0) {
    echo json_encode([
        'success' => true,
        'url' => $default_img  // 유효하지 않은 요청도 default 반환
    ]);
    exit;
}

// DB에서 이미지 경로 조회
$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

// fallback 이미지 경로
$default_img = "../../image/default.jpg";

// 결과가 있으면 주소 반환, 없으면 default 이미지
if (!empty($res) && !empty($res[0]['png_address'])) {
    $url = $res[0]['png_address'];
} else {
    $url = $default_img;
}

echo json_encode([
    'success' => true,
    'url' => $url
]);
