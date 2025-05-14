<?php
// AI 피드백 요청 처리

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // Step 추가

// 디버그 로그
file_put_contents("/tmp/php_debug.log", "Received Data: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

// 입력 안전 처리 (쉘 인젝션 방지)
$escapedBlockCode = escapeshellarg($blockCode);  
$escapedProblemId = escapeshellarg($problemId);
$escapedIndex = escapeshellarg($index);
$escapedStep = escapeshellarg($step);  

// 특수 문자 이스케이프 처리 (역슬래시와 큰따옴표)
$escapedBlockCode = str_replace('"', '\"', $escapedBlockCode);
$escapedBlockCode = str_replace("\\", "\\\\", $escapedBlockCode);

// 파이썬 피드백 스크립트 경로
$scriptPath = "../aifeedback/aifeedback.py";

// 파이썬 명령어 구성
$cmd = "python3 $scriptPath $escapedProblemId $escapedIndex \"$escapedBlockCode\" $escapedStep";

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd . " 2>&1", $output, $return_var);

// 파이썬 출력 데이터를 JSON으로 변환
$feedback = implode("\n", $output);
$feedback = htmlspecialchars($feedback, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401);  // 특수 문자 처리

// 디버그: 파이썬 출력 로그
file_put_contents("/tmp/php_debug.log", "Python Output: " . $feedback . "\n", FILE_APPEND);

// 결과 처리
$response = [
    "result" => $feedback,
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json; charset=UTF-8");
echo json_encode($response, JSON_UNESCAPED_UNICODE);  // 한글 깨짐 방지
?>