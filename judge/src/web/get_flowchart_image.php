<?php
require_once "include/db_info.inc.php";

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

header("Content-Type: application/json");

if ($problem_id <= 0 || $index < 0) {
    echo json_encode(['success' => false, 'message' => 'invalid input']);
    exit;
}

// DB에서 이미지 경로 조회
$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";

$res = pdo_query($sql, $problem_id, $index, $index);

// 기본 이미지 (웹 기준)
$default_img = "/image/default.jpg";

// 이미지 경로 설정
if (count($res) > 0 && !empty($res[0]['png_address'])) {
    $raw_path = $res[0]['png_address'];  // 예: /home/Capstone_Design_Troy/test/flowcharts/9944_1
    $filename = basename($raw_path) . ".png";  // 9944_1.png
    $web_url = "/flowcharts/" . $filename;     // 브라우저 접근 경로
    $server_file = $_SERVER['DOCUMENT_ROOT'] . $web_url;  // 서버 내부 실제 경로

    // 파일이 실제 존재하면 정상 URL, 없으면 default로 대체
    $url = file_exists($server_file) ? $web_url : $default_img;
} else {
    $url = $default_img;
}

echo json_encode(['success' => true, 'url' => $url]);
