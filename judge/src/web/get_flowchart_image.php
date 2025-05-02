<?php
require_once "include/db_info.inc.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

header("Content-Type: application/json");

if ($problem_id <= 0 || $index < 0) {
    echo json_encode(['success' => false, 'message' => 'invalid input']);
    exit;
}

$sql = "SELECT png_address FROM Flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

// fallback 이미지 경로
$default_img = "../../image/default.jpg";

// 결과가 존재하고 png_address가 있으면 해당 URL 반환, 없으면 fallback
if (count($res) > 0 && !empty($res[0]['png_address'])) {
    $url = $res[0]['png_address'];
} else {
    $url = $default_img;
}

echo json_encode(['success' => true, 'url' => $url]);
