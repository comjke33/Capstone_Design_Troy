<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";

// 디버깅: 입력 데이터 확인
file_put_contents("/tmp/php_debug.log", "Received Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// 세션 시작
session_start();
$user_id = $_SESSION['user_id'] ?? uniqid();  // 세션에 user_id가 없으면 임시 ID 사용

// 고유 파일명 생성
$unique_id = uniqid("aifeedback_");
$tmpFile = "/tmp/" . $unique_id . ".json";
$feedbackFile = "/tmp/" . $unique_id . "_feedback.txt";

// 파라미터를 파일에 기록
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "block_code" => $blockCode,
    "step" => $step
], JSON_UNESCAPED_UNICODE));

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile) . " " . escapeshellarg($feedbackFile);

// 파이썬 스크립트 실행
exec($cmd, $output, $return_var);

// 피드백 읽기
if (file_exists($feedbackFile)) {
    $feedback = file_get_contents($feedbackFile);
} else {
    $feedback = "피드백 파일이 존재하지 않습니다.";
}

// 디버깅 로그 추가
file_put_contents("/tmp/php_debug.log", "Python Output: " . $feedback . "\n", FILE_APPEND);

// 결과 처리
$response = [
    "result" => $feedback,
    "status" => $return_var === 0 ? "success" : "error"
];

// 피드백 파일 삭제
if (file_exists($feedbackFile)) {
    unlink($feedbackFile);
}

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>