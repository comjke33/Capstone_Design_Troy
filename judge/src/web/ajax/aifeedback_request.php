<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 절대 경로로 JSON 파일 생성 (Python 스크립트와 동일한 디렉토리)
$tmpFile = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/input_params.json";

// 파라미터를 파일에 기록 (JSON 형식)
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "block_code" => $blockCode,
    "step" => $step
], JSON_UNESCAPED_UNICODE));

// 파일 권한 설정
chmod($tmpFile, 0666);

// 파일 존재 여부 확인 로그
if (file_exists($tmpFile)) {
    file_put_contents("/tmp/php_debug.log", "JSON 파일 생성 성공: $tmpFile\n", FILE_APPEND);
} else {
    file_put_contents("/tmp/php_debug.log", "JSON 파일 생성 실패: $tmpFile\n", FILE_APPEND);
}

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";

// 파이썬 명령어 구성 (절대 경로 사용)
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile);

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