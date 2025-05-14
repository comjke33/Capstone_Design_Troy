<?php
header("Content-Type: application/json");

// 입력 파라미터 가져오기
$data = json_decode(file_get_contents("php://input"), true);
$problem_id = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$block_code = isset($data["block_code"]) && $data["block_code"] !== "" ? $data["block_code"] : "작성못함";
$step = $data["step"] ?? "0";  // 수정

// 디버그: 입력 데이터 로그
file_put_contents("/tmp/php_debug.log", "Received Data: " . json_encode($data) . "\n", FILE_APPEND);

// 파라미터 유효성 검사
if (!is_numeric($problem_id) || !is_numeric($index) || empty($block_code) || !is_numeric($step)) {
    echo json_encode(["result" => "error", "message" => "잘못된 파라미터"]);
    exit;
}

// 입력 안전 처리
$escapedProblemId = escapeshellarg($problem_id);
$escapedIndex = escapeshellarg($index);
$escapedBlockCode = escapeshellarg($block_code);
$escapedStep = escapeshellarg($step);  // step 인자 추가

// Python 스크립트 호출 명령어
$cmd = "python3 /home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py $escapedProblemId $escapedIndex \"$escapedBlockCode\" $escapedStep";

// 디버그: Python 명령어 확인
file_put_contents("/tmp/php_debug.log", "Python Command: $cmd\n", FILE_APPEND);

// Python 스크립트 실행 및 결과 수신
exec($cmd . " 2>&1", $output, $return_var);

// 디버그: Python 출력 로그
file_put_contents("/tmp/php_debug.log", "Python Output: " . implode("\n", $output) . "\n", FILE_APPEND);
file_put_contents("/tmp/php_debug.log", "Return Code: $return_var\n", FILE_APPEND);

// 결과 처리
if ($return_var !== 0) {
    echo json_encode(["result" => "error", "message" => "Python 스크립트 실행 오류", "details" => implode("\n", $output)]);
} else {
    $feedback = implode("\n", $output);
    echo json_encode(["result" => "success", "feedback" => trim($feedback)]);
}
?>