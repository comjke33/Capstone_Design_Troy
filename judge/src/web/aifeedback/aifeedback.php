<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 임시 파일 경로 지정 (고정 경로 사용)
$tmpFile = "/tmp/code_block.txt";

// 파일 작성 시도
if (file_put_contents($tmpFile, $blockCode) === false) {
    file_put_contents("/tmp/php_debug.log", "파일 작성 실패: $tmpFile\n", FILE_APPEND);
    die(json_encode(["status" => "error", "message" => "파일 작성 실패"]));
}

// 파일이 실제로 존재하는지 확인
if (!file_exists($tmpFile)) {
    file_put_contents("/tmp/php_debug.log", "임시 파일이 존재하지 않음: $tmpFile\n", FILE_APPEND);
    die(json_encode(["status" => "error", "message" => "파일이 존재하지 않음"]));
}

// 파이썬 피드백 스크립트 경로
$scriptPath = "../aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = "python3 $scriptPath $problemId $index $tmpFile $step";

// 디버깅 로그
file_put_contents("/tmp/php_debug.log", "Python Command: $cmd\n", FILE_APPEND);

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);

// 디버깅 로그
file_put_contents("/tmp/php_debug.log", "Python Output: " . implode("\n", $output) . "\n", FILE_APPEND);

// 결과 처리
$response = [
    "result" => implode("\n", $output),
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response);
?>