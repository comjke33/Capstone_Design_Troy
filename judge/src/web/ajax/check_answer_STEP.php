<?php
// JSON 데이터 수신
$data = json_decode(file_get_contents("php://input"), true);
$answer = $data["answer"] ?? "";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // 단계 정보를 변수로 처리

// 안전한 인자 처리
$escapedAnswer = escapeshellarg($answer);
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);
$escapedStep = escapeshellarg($step);

// 파라미터를 JSON 형식으로 임시 파일에 저장
$param = [
    "problem_id" => $problemId,
    "step" => $step,
    "index" => $index,
    "answer" => $answer
];
$paramFile = tempnam(sys_get_temp_dir(), 'param_') . '.json';
file_put_contents($paramFile, json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

// 파이썬 실행 명령어 (출력과 오류 모두 캡처)
$cmd = "cd ../check_STEP && python3 check_STEP.py $paramFile 2>&1";

// 디버그 로그 작성
file_put_contents("/tmp/php_debug.log", "Command: $cmd\n", FILE_APPEND);
$result = shell_exec($cmd);
file_put_contents("/tmp/php_debug.log", "Python Output: $result\n", FILE_APPEND);

// 결과 반환
$response = ["result" => trim($result)];
header("Content-Type: application/json");
echo json_encode($response);

// 임시 파일 삭제
unlink($paramFile);
?>