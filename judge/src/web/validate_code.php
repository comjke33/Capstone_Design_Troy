<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$index = intval($data['index']);
$input = trim($data['input']);
$problem_id = intval($data['problem_id']);

function compile_and_run($code, $problem_id) {
    $filename = "/tmp/submission_{$problem_id}.c";
    $output_exe = "/tmp/submission_{$problem_id}.out";
    $input_file = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.in";
    $expected_output = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.out";

    // 코드 파일 생성
    file_put_contents($filename, $code);

    // Clang 컴파일 명령어로 수정
    $compile_cmd = "clang $filename -o $output_exe 2>&1";
    $compile_result = shell_exec($compile_cmd);

    if (!file_exists($output_exe)) {
        return ["success" => false, "message" => "컴파일 오류: " . htmlspecialchars($compile_result)];
    }

    // 실행 명령어
    $exec_cmd = "$output_exe < $input_file";
    $actual_output = shell_exec($exec_cmd);
    $expected = file_get_contents($expected_output);

    if (trim($actual_output) === trim($expected)) {
        return ["success" => true, "message" => "정답입니다!"];
    } else {
        return ["success" => false, "message" => "출력 불일치: 예상: " . htmlspecialchars($expected) . " / 실제: " . htmlspecialchars($actual_output)];
    }
}

function get_combined_code($index, $input) {
    global $OJ_CORRECT_ANSWERS;
    $combined_code = "";
    foreach ($OJ_CORRECT_ANSWERS as $i => $correct) {
        // 사용자가 입력한 블록을 교체하여 코드 조합
        if ($i == $index) {
            $combined_code .= $input . "\n";
        } else {
            $combined_code .= $correct['content'] . "\n";
        }
    }
    return $combined_code;
}

// 코드 검증 로직
if (!empty($input)) {
    $complete_code = get_combined_code($index, $input);
    $result = compile_and_run($complete_code, $problem_id);
    echo json_encode($result);
} else {
    echo json_encode(["success" => false, "message" => "코드가 비어 있습니다."]);
}
?>