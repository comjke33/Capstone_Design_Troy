<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";

// 역슬래시를 직접 처리
$escapedAnswer = str_replace("\\\\", "\\", $answer);

// 임시 코드 파일 경로 생성
$code_file = "/tmp/code_" . uniqid() . ".c";
file_put_contents($code_file, $escapedAnswer);

// 파라미터 파일 생성
$param_file = "/tmp/params_" . uniqid() . ".json";
$params = [
    "problem_id" => $problemId,
    "step" => $step,
    "index" => $index,
    "answer" => $escapedAnswer,  
    "code_file" => $code_file
];
file_put_contents($param_file, json_encode($params));

// 파이썬 실행 명령어 (출력과 오류 모두 캡처)
$cmd = "cd ../check_STEP && python3 check_STEP.py $param_file 2>&1";

// 디버그 로그 작성
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);
?>