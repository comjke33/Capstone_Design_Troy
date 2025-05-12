<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$index = intval($data['index']);
$input = trim($data['input']);
$problem_id = intval($data['problem_id']);

/**
 * 태그 제거 함수
 */
function remove_tags($code) {
    return preg_replace('/\[[^\]]*\]/', '', $code);
}

function compile_and_run($code, $problem_id) {
    // 파일 경로 설정
    $filename = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/submission.c";
    $output_exe = "/tmp/submission_{$problem_id}.out";
    $input_file = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.in";
    $expected_output = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.out";

    // 태그를 제거한 코드 생성
    $clean_code = remove_tags($code);
    file_put_contents($filename, $clean_code);

    // Clang 컴파일 명령어로 수정 (환경 변수 명시)
    $compile_cmd = "env PATH=/usr/bin:/usr/local/bin clang -fuse-ld=/usr/bin/ld $filename -o $output_exe 2>&1";
    $compile_result = shell_exec($compile_cmd);

    // 컴파일 오류 발생 시
    if (!file_exists($output_exe)) {
        return ["success" => false, "message" => "컴파일 오류: " . htmlspecialchars($compile_result)];
    }

    // 실행 명령어
    $exec_cmd = "$output_exe < $input_file";
    $actual_output = shell_exec($exec_cmd);
    $expected = file_get_contents($expected_output);

    // 실행 결과 비교
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
    // 사용자 입력을 코드에 반영하여 전체 코드 생성
    $complete_code = get_combined_code($index, $input);

    // 전체 코드를 컴파일하고 실행하여 결과 반환
    $result = compile_and_run($complete_code, $problem_id);
    echo json_encode($result);
} else {
    echo json_encode(["success" => false, "message" => "코드가 비어 있습니다."]);
}
?>