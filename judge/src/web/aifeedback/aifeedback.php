<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 고정 경로 사용
$tmpFile = "/tmp/code_block.txt";

// 로그 파일 경로
$logFile = "/tmp/php_debug.log";

// 로그 기록 함수
function log_message($message) {
    global $logFile;
    file_put_contents($logFile, date("[Y-m-d H:i:s] ") . $message . "\n", FILE_APPEND);
}

// 디버깅: 파일 생성 시도 로그
log_message("파일 작성 시도: $tmpFile");

// 파일 작성
if (file_put_contents($tmpFile, $blockCode) === false) {
    log_message("파일 작성 실패: $tmpFile");
    die(json_encode(["status" => "error", "message" => "파일 작성 실패"]));
}

// 디버깅: 파일 존재 여부 확인
if (!file_exists($tmpFile)) {
    log_message("파일이 존재하지 않음: $tmpFile");
    die(json_encode(["status" => "error", "message" => "파일이 존재하지 않음"]));
}

// 파일 경로 확인
$realTmpFile = realpath($tmpFile);
log_message("임시 파일 절대 경로: $realTmpFile");

// 파이썬 피드백 스크립트 경로
$scriptPath = "../aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = "python3 $scriptPath $problemId $index $realTmpFile $step";
log_message("Python Command: $cmd");

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);

// 디버깅: 파이썬 실행 결과 로그
log_message("Python Output: " . implode("\n", $output));

// 결과 처리
$response = [
    "result" => implode("\n", $output),
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response);
?>