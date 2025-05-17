<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리

// 임시 파일 경로 설정
$tempFile = tempnam("/tmp", "oj_input_");
file_put_contents($tempFile, json_encode($data));

// 파이썬 실행 명령어 (출력과 오류 모두 캡처)
$cmd = "cd ../check_STEP && python3 check_STEP.py $tempFile 2>&1";

// 디버그 로그 작성
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 임시 파일 삭제
unlink($tempFile);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);
?>