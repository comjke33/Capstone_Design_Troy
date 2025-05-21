<?php
$problem_id = $_GET['problem_id'] ?? '';
$step = $_GET['step'] ?? '';

$pattern = "/tmp/complete_code_{$problem_id}_step{$step}_*.c";
$files = glob($pattern);

if (!$files || count($files) === 0) {
    echo json_encode(["status" => "error", "message" => "결함 코드 없음"]);
    exit;
}

$random_file = $files[array_rand($files)];
$code = file_get_contents($random_file);

echo json_encode([
    "status" => "ok",
    "code" => $code,
    "filename" => basename($random_file)
]);