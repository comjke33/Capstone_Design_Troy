<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// Base64 인코딩으로 안전하게 전달 (UTF-8로 명시적 변환 후 인코딩)
$encodedBlockCode = base64_encode($blockCode);
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);
$escapedStep = escapeshellarg($step);
$escapedBlockCode = escapeshellarg($encodedBlockCode);

// 파이썬 피드백 스크립트 경로
$scriptPath = "../aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = "python3 $scriptPath $escapedProblemId $escapedIndex $escapedBlockCode $escapedStep";

// 디버그: Python 명령어 확인
file_put_contents("/tmp/php_debug.log", "Python Command: $cmd\n", FILE_APPEND);

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);

// 결과 처리
$response = [
    "result" => implode("\n", $output),
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response);
?>