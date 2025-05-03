<?php
session_start();
require_once("../include/db_info.inc.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
    http_response_code(403);
    echo "Unauthorized";
    exit();
}

// 파라미터 받기
$description = $_POST['description'] ?? '';
$description = preg_replace('/<span\s+class=[\'"]md auto_select[\'"]>.*?<\/span>/is', '', $description);
$description = str_replace(",", "&#44;", $description);
$exemplary_code = $_POST['exemplary_code'] ?? '';
$problem_id = $_POST['problem_id'] ?? '';

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
$results = [];
$results[] = run_script("cd /home/Capstone_Design_Troy/test/ && python3 make_question_and_code.py " . escapeshellarg($description) . ' ' . escapeshellarg($exemplary_code));
$env_vars = parse_ini_file("/home/Capstone_Design_Troy/test/env");
$api_key = escapeshellarg($env_vars["OPENAI_API_KEY"]);
$command = "cd /home/Capstone_Design_Troy/test/ && OPENAI_API_KEY=$api_key python3 AIFlowchart.py $problem_id_arg";
$results[] = run_script($command);

#$results[] = run_script("cd /home/Capstone_Design_Troy/test/ && python3 AIFlowchart.py " . escapeshellarg($problem_id));

header("Content-Type: application/json");
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>