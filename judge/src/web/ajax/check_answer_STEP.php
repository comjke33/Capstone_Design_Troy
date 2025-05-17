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

$cmd = "cd ../check_STEP && python3 check_STEP.py $escapedProblemId $escapedStep $escapedIndex $escapedAnswer 2>&1";
$result = trim(shell_exec($cmd));

// 결과에서 중복된 correct 제거
$cleaned_result = implode("\n", array_unique(explode("\n", $result)));

$response = ["result" => $cleaned_result];
header("Content-Type: application/json");
echo json_encode($response);
?>