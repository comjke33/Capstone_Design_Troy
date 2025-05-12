<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$index = intval($data['index']);
$input = trim($data['input']);
$problem_id = intval($data['problem_id']);

// 코드 파일 경로 설정
function get_submission_path($problem_id) {
    return "/tmp/submission_{$problem_id}.c";
}

function get_output_path($problem_id) {
    return "/tmp/submission_{$problem_id}.out";
}

function get_txt_path($problem_id) {
    return "/home/Capstone_Design_Troy/judge/src/web/tagged_code/{$problem_id}_step2.txt";
}

// 태그 제거 함수
function remove_tags($code) {
    return preg_replace('/\[\w+_(start|end)\(\d+\)\]/', '', $code);
}

// 코드 블럭 교체 함수
function replace_code_block($original_code, $index, $new_code) {
    $lines = explode("\n", $original_code);
    $code_block_found = false;
    foreach ($lines as &$line) {
        if (strpos($line, "[self_start($index)]") !== false) {
            $code_block_found = true;
            continue;  // 태그 줄은 건너뛰기
        }
        if ($code_block_found) {
            if (strpos($line, "[self_end($index)]") !== false) {
                $code_block_found = false;
                continue;  // 태그 줄은 건너뛰기
            }
            // 코드 블럭 교체
            $line = $new_code;
        }
    }
    return implode("\n", $lines);
}

// 코드 파일 불러오기
function load_original_code($problem_id) {
    $file_path = get_txt_path($problem_id);
    if (!file_exists($file_path)) {
        return ["success" => false, "message" => "원본 코드 파일을 찾을 수 없습니다."];
    }
    return file_get_contents($file_path);
}

// 코드 컴파일 및 실행
function compile_and_run($code, $problem_id) {
    $filename = get_submission_path($problem_id);
    $output_exe = get_output_path($problem_id);
    $input_file = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.in";
    $expected_output = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.out";

    // 코드 파일 생성
    file_put_contents($filename, $code);

    // Clang 컴파일 명령어
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

// 코드 검증 로직
if (!empty($input)) {
    // 원본 코드 불러오기
    $original_code = load_original_code($problem_id);
    if (is_array($original_code)) {
        echo json_encode($original_code);
        exit;
    }

    // 태그 제거
    $cleaned_code = remove_tags($original_code);

    // 코드 블럭 교체
    $complete_code = replace_code_block($cleaned_code, $index, $input);

    // 컴파일 및 실행
    $result = compile_and_run($complete_code, $problem_id);
    echo json_encode($result);
} else {
    echo json_encode(["success" => false, "message" => "코드가 비어 있습니다."]);
}
?>