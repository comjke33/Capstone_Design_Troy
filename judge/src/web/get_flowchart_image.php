<?php
require_once "include/db_info.inc.php";

header("Content-Type: application/json");

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
//$problem_id=9944;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;
// 디버깅 용 default 이미지 설정
// $res = pdo_query($sql, $problem_id);


if ($problem_id <= 0) {
    echo json_encode([
        'success' => true,
        'url' => $url,
        'debug' => [
            'problem_id' => $problem_id,
            'index' => $index,
            'res' => $res
        ]
    ]);
    exit;
}

//DB에서 링크를 가져와서 링크를 구현
// $sql = "SELECT png_address FROM flowchart 
//         WHERE problem_id = ? 
//         AND start_num <= ?
//         AND end_num >= ?
//         LIMIT 1";

// 테스트용 쿼리
$problem_id = 1256;  // 테스트할 problem_id
$index = 4;  // 테스트할 index (start_num과 end_num에 맞는 번호)

$sql = "SELECT png_address FROM flowchart 
        WHERE problem_id = ? 
        AND start_num <= ? 
        AND end_num >= ? 
        LIMIT 1";


$res = pdo_query($sql, $problem_id, $index, $index);


if (count($res) > 0) {
    $filename =($res[0]['png_address'] . ".png");
    $url = ($res[0]['png_address'] . ".png");

} else {
    $url = "";
}

// 디버깅 용 default 이미지 설정
// $url = $default_img;

echo json_encode([
    'success' => true,
    'url' => $url,
    'debug' => [
        'problem_id' => $problem_id,
        'index' => $index,
        'res' => $res
    ]
]);