<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";

// 디버깅 로그
file_put_contents("/tmp/php_debug.log", "Received Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// 세션 시작
session_start();
$user_id = $_SESSION['user_id'] ?? uniqid();

// 고유 파일명 생성
$unique_id = uniqid("check_step_");
$answerFile = "/tmp/" . $unique_id . "_answer.txt";
$paramFile = "/tmp/" . $unique_id . ".json";
$feedbackFile = "/tmp/" . $unique_id . "_feedback.txt";

// 답안을 파일로 저장 (이스케이프 없이 원래 형태 그대로)
file_put_contents($answerFile, $answer);

// 파라미터를 JSON으로 임시 파일에 저장
file_put_contents($paramFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "answer_file" => $answerFile,
    "step" => $step
], JSON_UNESCAPED_UNICODE));

// 파이썬 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/check_STEP/check_STEP.py";

// 파이썬 명령어 구성
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($paramFile) . " " . escapeshellarg($feedbackFile);
exec($cmd, $output, $return_var);

// 피드백 읽기
if (file_exists($feedbackFile)) {
    $feedback = file_get_contents($feedbackFile);
} else {
    $feedback = "피드백 파일이 존재하지 않습니다.";
}

// 피드백 파일 삭제
if (file_exists($feedbackFile)) {
    unlink($feedbackFile);
}

// 임시 파일 삭제
if (file_exists($paramFile)) {
    unlink($paramFile);
}

if (file_exists($answerFile)) {
    unlink($answerFile);
}

// 결과 반환
$response = ["result" => $feedback];
header("Content-Type: application/json");
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>