<?php
header('Content-Type: application/json');

$problem_id = $_GET['problem_id'] ?? '';

if (!$problem_id) {
    echo json_encode(["status" => "error", "message" => "문제 번호가 없습니다."]);
    exit;
}

$pattern = "/tmp/complete_code_{$problem_id}_step*_*.c";  // step 없이 전체 매칭
$files = glob($pattern);

if (!$files || count($files) === 0) {
    echo json_encode(["status" => "error", "message" => "결함 코드가 존재하지 않습니다."]);
    exit;
}

$random_file = $files[array_rand($files)];
$code = file_get_contents($random_file);

echo json_encode(["status" => "ok", "code" => $code]);
?>