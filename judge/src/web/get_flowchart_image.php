<?php
require_once "include/db_info.inc.php";

// $problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$problem_id=9944;
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

$res = pdo_query($sql, $problem_id, $index_start, $index_end);

// 디버깅 용 default 이미지 설정
// $res = pdo_query($sql, $problem_id);
$default_img = "/flowcharts/default.png";

if (count($res) > 0 && !empty($res['png_address'])) {
    $filename =($res['png_address'] . ".png");
    $url = ($res['png_address'] . ".png");
    $url = "/flowcharts/1000_1.png";
} else {
    $url = $default_img;
}

// 디버깅 용 default 이미지 설정
// $url = $default_img;
$url = "/flowcharts/1000_1.png";
echo json_encode(['success' => true, 'url' => $url]);
