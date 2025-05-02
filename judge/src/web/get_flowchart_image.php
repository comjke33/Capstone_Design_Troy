<?php
require_once "include/db_info.inc.php";
header("Content-Type: application/json");

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

$default_img = "../../image/default.jpg";

if ($problem_id <= 0 || $index < 0) {
    echo json_encode([
        'success' => true,
        'url' => $default_img
    ]);
    exit;
}

$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

if (!empty($res) && !empty($res[0]['png_address'])) {
    $url = $res[0]['png_address'];
} else {
    $url = $default_img; // ✅ 무조건 이 이미지로 대체
}

echo json_encode([
    'success' => true,
    'url' => $url
]);
