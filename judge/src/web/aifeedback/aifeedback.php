<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 임시 파일 경로 설정
$tmpFile = tempnam(sys_get_temp_dir(), 'code_') . '.txt';

// 코드 블럭을 임시 파일에 저장 (UTF-8 인코딩)
file_put_contents($tmpFile, $blockCode);

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";

// 파이썬 명령어 구성 (임시 파일 경로 전달)
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($problemId) . " " . escapeshellarg($index) . " " . escapeshellarg($tmpFile) . " " . escapeshellarg($step);

// 디버그: 명령어 확인
file_put_contents("/tmp/php_debug.log", "Python Command: $cmd\n", FILE_APPEND);

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd . " 2>&1", $output, $return_var);

// 임시 파일 삭제
unlink($tmpFile);

// 결과 처리
$response = [
    "result" => implode("\n", $output),
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response);
?>