<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 임시 파일 경로 고정
$tmpFile = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/code_block.txt";

// 디렉토리가 없다면 생성
if (!is_dir(dirname($tmpFile))) {
    mkdir(dirname($tmpFile), 0777, true);
}

// 임시 파일에 코드 블럭 저장
file_put_contents($tmpFile, $blockCode);

// 파이썬 피드백 스크립트 경로
$scriptPath = "../aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = escapeshellcmd("python3 $scriptPath $problemId $index $tmpFile $step");

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