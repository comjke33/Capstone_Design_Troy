<?php
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리

$escapedAnswer = escapeshellarg($answer);
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);
$escapedStep = escapeshellarg($step);  // 추가

$cmd = "cd ../check_STEP && python3 check_STEP.py $escapedProblemId $escapedStep $escapedIndex $escapedAnswer";
$result = trim(shell_exec($cmd));

$response = ["result" => $result];
header("Content-Type: application/json");
echo json_encode($response);
?>