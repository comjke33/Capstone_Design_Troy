<?php
session_start();  // 세션 시작

// JSON 데이터 수신 및 파싱
$data = json_decode(file_get_contents("php://input"), true);
$blockCode = $data["block_code"] ?? "작성못함";
$problemId = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";
$step = $data["step"] ?? "1";  // step 인자 추가

// 모범 코드 가져오기
function getModelAnswer($problemId) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=jol", "hustoj", "JGqRe4pltka5e5II4Di3YZdmxv7SGt");
        $stmt = $pdo->prepare("SELECT exemplary_code FROM exemplary WHERE problem_id = ?");
        $stmt->execute([$problemId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['exemplary_code'] ?? "모범 코드 없음";
    } catch (PDOException $e) {
        return "DB 오류: " . $e->getMessage();
    }
}

// 가이드라인 가져오기
function getGuideline($problemId, $step) {
    $filePath = "/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/{$problemId}_step{$step}.txt";
    if (file_exists($filePath)) {
        return file_get_contents($filePath);
    }
    return "가이드라인 없음";
}

// 모범 코드와 가이드라인 불러오기
$modelAnswer = getModelAnswer($problemId);
$guideline = getGuideline($problemId, $step);

// 세션 ID를 이용하여 고유 파일명 생성
$session_id = session_id() ?: uniqid();
$tmpFile = sys_get_temp_dir() . "/aifeedback_input_" . $session_id . ".json";

// 파라미터를 파일에 기록 (JSON 형식)
file_put_contents($tmpFile, json_encode([
    "problem_id" => $problemId,
    "index" => $index,
    "block_code" => $blockCode,
    "step" => $step,
    "model_answer" => $modelAnswer,
    "guideline" => $guideline
], JSON_UNESCAPED_UNICODE));

// 파일 권한 설정
chmod($tmpFile, 0666);

// 파이썬 피드백 스크립트 경로
$scriptPath = "/home/Capstone_Design_Troy/judge/src/web/aifeedback/aifeedback.py";
$cmd = "python3 " . escapeshellarg($scriptPath) . " " . escapeshellarg($tmpFile);

// 파이썬 스크립트 실행 및 결과 수신
exec($cmd, $output, $return_var);

// 피드백을 하나의 문자열로 합치기
$feedback = implode("\n", $output);

// 결과 처리
$response = [
    "result" => $feedback,
    "status" => $return_var === 0 ? "success" : "error"
];

// JSON으로 반환
header("Content-Type: application/json");
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>