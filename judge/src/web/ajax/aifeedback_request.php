<?php
header("Content-Type: application/json");

// 입력 파라미터 가져오기
$data = json_decode(file_get_contents("php://input"), true);
$block_code = $data["block_code"] ?? "";
$problem_id = $data["problem_id"] ?? "0";
$index = $data["index"] ?? "0";

// 파라미터 유효성 검사
if (!is_numeric($problem_id) || !is_numeric($index) || empty($block_code)) {
    echo json_encode(["result" => "error", "message" => "잘못된 파라미터"]);
    exit;
}

// 사용자 입력을 안전하게 처리
$escapedProblemId = escapeshellarg($problem_id);
$escapedIndex = escapeshellarg($index);
$escapedBlockCode = escapeshellarg($block_code);

// 파이썬 실행 명령어
$cmd = "cd ../../aifeedback && python3 aifeedback.py $escapedProblemId $escapedIndex $escapedBlockCode";
exec($cmd, $output, $return_var);

if ($return_var !== 0) {
    echo json_encode(["result" => "error", "message" => "Python 스크립트 실행 오류"]);
} else {
    $feedback = implode("\n", $output);
    echo json_encode(["result" => "success", "feedback" => trim($feedback)]);
}
?>