<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$index = intval($data['index']);
$input = trim($data['input']);
$problem_id = intval($data['problem_id']);

// 태그 제거 함수
function remove_tags($code) {
    // 정규식을 이용하여 태그 제거
    return preg_replace('/\[\w+_(start|end)\(\d+\)\]/', '', $code);
}

// 파일에서 태그 제거 후 C 코드로 변환
function convert_txt_to_c($problem_id) {
    // 문제 경로 설정 (txt 파일 경로)
    $txt_file = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/{$problem_id}_step2.txt";
    $c_file = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/submission.c";
    
    // 파일 존재 여부 확인
    if (!file_exists($txt_file)) {
        return ["success" => false, "message" => "TXT 파일을 찾을 수 없습니다."];
    }

    // TXT 파일 읽기
    $code = file_get_contents($txt_file);
    if ($code === false) {
        return ["success" => false, "message" => "파일 읽기 오류"];
    }

    // 태그 제거
    $cleaned_code = remove_tags($code);

    // C 파일로 저장
    if (file_put_contents($c_file, $cleaned_code) === false) {
        return ["success" => false, "message" => "C 파일 생성 오류"];
    }

    return ["success" => true, "message" => "C 코드 변환 완료"];
}

// 코드 컴파일 및 실행
function compile_and_run($problem_id) {
    $c_file = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/submission.c";
    $output_exe = "/home/Capstone_Design_Troy/judge/src/web/tagged_code/submission.out";
    $input_file = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.in";
    $expected_output = "/home/Capstone_Design_Troy/judge/data/{$problem_id}/sample.out";

    // Clang 컴파일 명령어로 수정
    $compile_cmd = "env PATH=/usr/bin:/usr/local/bin clang -fuse-ld=/usr/bin/ld $c_file -o $output_exe 2>&1";
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

// TXT 파일 변환 후 실행
$result = convert_txt_to_c($problem_id);
if ($result['success']) {
    $result = compile_and_run($problem_id);
}

echo json_encode($result);
?>