<?php
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";

$problemId = 1292;

// 사용자 입력을 safe하게 처리
$escapedAnswer = escapeshellarg($answer);
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);

// 파이썬 실행
$cmd = "cd ../check_STEP1 && python3 check_STEP1.py $escapedProblemId $escapedIndex $escapedAnswer";
$result = trim(shell_exec($cmd));

// 결과 반환
$response = ["result" => $result];
header("Content-Type: application/json");
echo json_encode($result);