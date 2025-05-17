<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리
$codeFile = "/tmp/code_" . uniqid() . ".c";

// 코드 내용을 파일로 저장
file_put_contents($codeFile, $answer);

// JSON 파일 생성
$paramFilePath = "/tmp/params_" . uniqid() . ".json";
$params = array(
    "problem_id" => $problemId,
    "step" => $step,
    "index" => $index,
    "answer" => $answer,
    "code_file" => $codeFile
);
file_put_contents($paramFilePath, json_encode($params, JSON_UNESCAPED_UNICODE));

// 파이썬 실행 명령어
$cmd = "cd ../check_STEP && python3 check_STEP.py $paramFilePath 2>&1";
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => $result];
header("Content-Type: application/json");
echo json_encode($response);
?>