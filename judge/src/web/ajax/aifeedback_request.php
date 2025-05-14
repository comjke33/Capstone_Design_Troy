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

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";

// 파이썬 명령어 구성 (절대 경로 사용)
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile);

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);

// 피드백을 하나의 문자열로 합치기
$feedback = implode("\n", $output);

// 줄바꿈을 명시적으로 변환하여 JSON 응답 처리
$feedback = str_replace("\n", "\\n", $feedback);

// 로그에 기록
file_put_contents("/tmp/php_debug.log", "Merged Feedback: " . $feedback . "\n", FILE_APPEND);

// 결과 처리
$response = [
    "result" => $feedback,
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>