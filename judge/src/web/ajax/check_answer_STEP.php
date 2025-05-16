<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리

// 디버깅 로그
file_put_contents("/tmp/php_debug.log", "Received Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// 세션 시작
session_start();
$user_id = $_SESSION['user_id'] ?? uniqid();

// 고유 파일명 생성
$unique_id = uniqid("check_step_");
$tmpFile = "/tmp/" . $unique_id . ".json";
$feedbackFile = "/tmp/" . $unique_id . "_feedback.txt";

// 파라미터를 JSON으로 임시 파일에 저장
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "answer" => $answer,
    "step" => $step
], JSON_UNESCAPED_UNICODE));

// 파이썬 스크립트 경로 (동적으로 구성)
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/check_STEP/check_STEP.py";

// 파이썬 명령어 구성
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile);
file_put_contents("/tmp/php_debug.log", "Python Command: $cmd\n", FILE_APPEND);

// 파이썬 스크립트 실행
exec($cmd . " 2>&1", $output, $return_var);

// 명령어 실행 결과 로그 기록
file_put_contents("/tmp/php_debug.log", "Command Output: " . implode("\n", $output) . "\n", FILE_APPEND);

// 명령어 실행 상태 체크
if ($return_var === 0) {
    $result = implode("\n", $output);
} else {
    $result = "Error: " . implode("\n", $output);
}

// 결과 반환
$response = ["result" => $result];
header("Content-Type: application/json");
echo json_encode($response, JSON_UNESCAPED_UNICODE);

// 임시 파일 삭제
if (file_exists($tmpFile)) {
    unlink($tmpFile);
}
?>