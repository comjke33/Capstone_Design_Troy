<?php
session_start();
require_once("../include/db_info.inc.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}


//////////////////////make_question_and_code.py//////////////////////////////
// 파라미터 받기
$description = $_POST['description'] ?? '';
// <span class='md auto_select'> 내부 텍스트 추출
if (preg_match("/<span\s+class=['\"]md auto_select['\"]>(.*?)<\/span>/is", $description, $matches)) {
    $innerText = $matches[1];

    // 공백 문자 제거 (일반 공백 + non-breaking space 포함)
    $innerText = str_replace("\xc2\xa0", ' ', $innerText); // &nbsp; (U+00A0)
    $innerText = preg_replace('/\s+/', '', $innerText);    // 모든 공백 문자 제거

    $description = $innerText;
}
#$description = str_replace(",", "&#44;", $description);
$exemplary_code = $_POST['exemplary_code'] ?? '';
$problem_id = $_POST['problem_id'] ?? '';
$output_dir = $_POST['output_dir'] ?? '';

$description_b64 = base64_encode($description);
$exemplary_code_b64 = base64_encode($exemplary_code);
$problem_id_b64 = base64_encode($problem_id);
$output_dir_b64 = base64_encode($output_dir);

$cmd = "nohup bash -c 'cd /home/Capstone_Design_Troy/judge/src/web/add_problem && bash run_add_problem.sh " 
       . escapeshellarg($problem_id_b64) . " " 
       . escapeshellarg($description_b64) . " " 
       . escapeshellarg($exemplary_code_b64) . " "
       . escapeshellarg($output_dir_b64) ."' > /home/debug.log 2>&1 &";

// $cmd = "nohup bash -c 'cd /home/Capstone_Design_Troy/judge/src/web/add_problem && bash run_add_problem.sh " 
//        . escapeshellarg($problem_id) . " " 
//        . escapeshellarg($description) . " " 
//        . escapeshellarg($exemplary_code) . "' > /home/user/pipeline.log 2>&1 &";

// 로그 저장용
function run_script($cmd) {
    $output = [];
    $return_var = 0;
    exec($cmd . " 2>&1", $output, $return_var); // 에러 포함 출력 캡처
    return [
        'command' => $cmd,
        'output' => $output,
        'return_code' => $return_var
    ];
}

$result = run_script($cmd);
file_put_contents('/home/debug.log', print_r($result, true));
echo json_encode([
    "status" => "started"
]);

// $results = [];
// $results[] = run_script("cd /home/Capstone_Design_Troy/judge/src/web/add_problem && python3 make_question_and_code.py " . escapeshellarg($description) . ' ' . escapeshellarg($exemplary_code));
// $results[] = run_script($command);


// header("Content-Type: application/json");
// echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// //////////////////////make_question_and_code.py//////////////////////////////

// //////////////////////make_guideline.py//////////////////////////////
// $result_guideline = [];
// $result_guideline = run_script("cd /home/Capstone_Design_Troy/judge/src/web/add_problem && python3 make_guideline.py " . escapeshellarg($problem_id));
// echo json_encode($result_guideline, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
// //////////////////////make_guideline.py//////////////////////////////
?>