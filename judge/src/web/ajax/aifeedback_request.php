// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = isset($data["block_code"]) ? base64_decode($data["block_code"]) : "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 절대 경로로 JSON 파일 생성
$tmpFile = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/input_params.json";
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "block_code" => $blockCode,
    "step" => $step
], JSON_UNESCAPED_UNICODE));
chmod($tmpFile, 0666);

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";
$cmd = "python3 $scriptPath $tmpFile";

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);
$response = [
    "result" => implode("\n", $output),
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response);
?>