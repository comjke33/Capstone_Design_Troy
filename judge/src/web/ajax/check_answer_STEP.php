<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "0";

// 임시 디렉토리 설정
$tempDir = "/tmp/";
$codeFile = $tempDir . "code_" . uniqid() . ".c";
$answerFile = $tempDir . "answer_" . uniqid() . ".txt";
$paramFilePath = $tempDir . "params_" . uniqid() . ".json";

// 코드 내용을 파일로 저장
file_put_contents($codeFile, $answer);
file_put_contents($answerFile, $answer);  // answer 내용을 별도로 파일에 저장

// JSON 파라미터 파일 생성
$params = array(
    "problem_id" => $problemId,
    "step" => $step,
    "index" => $index,
    "answer_file" => $answerFile,  // 수정: 파일 경로를 넘김
    "code_file" => $codeFile
);
file_put_contents($paramFilePath, json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

// 파이썬 실행 명령어
$cmd = "cd ../check_STEP && python3 check_STEP.py " . escapeshellarg($paramFilePath) . " 2>&1";
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);
?>