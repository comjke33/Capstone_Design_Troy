<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리

// 안전한 임시 파일 생성
$unique_id = uniqid("check_step_");
$tmpFile = "/tmp/" . $unique_id . ".json";

// JSON 형식으로 파라미터를 파일에 저장
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "step" => $step,
    "index" => $index,
    "answer" => $answer
], JSON_UNESCAPED_UNICODE));

// 파이썬 실행 명령어 (출력과 오류 모두 캡처)
$cmd = "cd ../check_STEP && python3 check_STEP.py " . escapeshellarg($tmpFile) . " 2>&1";

// 디버그 로그 작성
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);

// 임시 파일 삭제
if (file_exists($tmpFile)) {
    unlink($tmpFile);
}
?>