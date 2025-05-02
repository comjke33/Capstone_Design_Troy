<?php
require_once('include/db_info.inc.php');

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0; // solution_id를 GET 파라미터로 받음
$feedback_error = ""; // 피드백 오류 메시지 초기화
$code = ""; // 코드 초기화
$output = ""; // 출력 초기화
$compile_result = ""; // 컴파일 결과 초기화


// solution_id로 solution 테이블에서 code 가져오기
if ($solution_id > 0) {
    $sql = "SELECT source FROM source_code_user WHERE solution_id = '$solution_id'";
    $row=pdo_query($sql);

    if(!empty($row)) {
        $code = $row[0][0]; // source_code_user 테이블에서 code 가져오기
    } else {
        $feedback_error = "⚠️ 해당 solution_id에 대한 코드가 없습니다."; // 코드가 없을 경우 오류 처리
    }
}


if (isset($code)) {
    // 인자를 공백으로 구분해 Python 스크립트에 전달
    $command = "cd /home/Capstone_Design_Troy/py/ && python3 compile_process.py " . escapeshellarg($code);
    $compile_result = shell_exec($command);
}
file_put_contents("/tmp/compile_output.txt", $compile_result);
$data = json_decode($compile_result, true);
$link_results = array();

// stderrs가 존재하는지 확인하고 반복
if (isset($data['stderrs']) && is_array($data['stderrs'])) {
    foreach ($data['stderrs'] as $stderr) {
        if (isset($stderr['message'])) {
            $command = "cd /home/Capstone_Design_Troy/py/ && python3 matching_hyperlink.py " . escapeshellarg($stderr['message']);
            $link = shell_exec($command);

            $decoded = json_decode(trim($link), true);

            if ($decoded === null) {
                echo "<pre>json_decode 실패! 에러: " . json_last_error_msg() . "</pre>";
                var_dump($link);
                exit;
            }
            
            
            // 결과를 message별로 배열에 추가
            $link_results[] = array(
                "message" => $stderr['message'],
                "matches" => $decoded  // 개념/블록/링크들이 배열로 들어감
            );
        }
    }
}

include("template/syzoj/feedback.php");
?>