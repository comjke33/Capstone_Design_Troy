<?php
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";

// 안전한 인자 처리
$escapedAnswer = escapeshellarg($answer);
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);
$escapedStep = escapeshellarg($step);

$cmd = "cd ../check_STEP && python3 check_STEP.py $escapedProblemId $escapedStep $escapedIndex $escapedAnswer 2>&1";

// 디버그 로그 작성
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);
?>