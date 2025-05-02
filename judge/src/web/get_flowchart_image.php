<?php
require_once "include/db_info.inc.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

header("Content-Type: application/json");

if ($problem_id <= 0 || $index < 0) {
    echo json_encode(['success' => false, 'message' => 'invalid input']);
    exit;
}


//DB에서 링크를 가져와서 링크를 구현
$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);
$default_img = "../../image/default.jpg";

if (count($res) > 0 && !empty($res[0]['png_address'])) {
    $filename = basename($res[0]['png_address']);
    $url = "/flowcharts/{$filename}.png";         // 웹 접근 가능하게 변환
} else {
    $url = $default_img;
}

echo json_encode(['success' => true, 'url' => $url]);
