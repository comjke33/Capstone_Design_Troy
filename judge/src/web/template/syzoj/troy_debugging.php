<?php
include("include/db_info.inc.php");

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$problem_id = "1000";  // 디버깅: 문제 ID 강제 설정
echo "<pre>문제 ID: $problem_id</pre>";

$desc = $description;
echo "<pre>문제 설명 읽음 (길이: " . strlen($desc) . ")</pre>";

$output_dir = "/home/Capstone_Design_Troy/judge/src/web/flowcharts/";
echo "<pre>출력 디렉토리: $output_dir</pre>";

// --- 파이썬 명령어 구성 ---
$command = "cd /home/Capstone_Design_Troy/py/ && python3 make_flowchart.py "
    . escapeshellarg($tagged_guideline) . " "
    . escapeshellarg($desc) . " "
    . escapeshellarg($output_dir) . " "
    . escapeshellarg($problem_id);

echo "<pre>실행할 명령어: $command</pre>";

// --- 파이썬 실행 ---
echo "<pre>Python 스크립트 실행 중...</pre>";
$result = shell_exec($command);

if ($result === null) {
    echo "<pre>Python 스크립트 실행 실패!</pre>";
    exit;
}

echo "<pre>파이썬 결과:\n$result</pre>";

// --- 결과 파싱 ---
$json_data = json_decode($result, true);
if ($json_data === null) {
    echo "<pre>JSON 디코드 실패! 원본 결과:\n$result</pre>";
    exit;
}

echo "<pre>JSON 데이터 파싱 완료. 블록 수: " . count($json_data) . "</pre>";

// --- DB 저장 ---
foreach ($json_data as $index => $row) {
    $idx = intval($index);
    $start_line = intval($row['start_line']);
    $end_line = intval($row['end_line']);

    echo "<pre>DB 입력 준비 - index: $idx, start_line: $start_line, end_line: $end_line</pre>";

    $sql = "INSERT INTO flowchart (problem_id, png_address, png_number, start_num, end_num) VALUES (?, ?, ?, ?, ?)";
    $result = pdo_query($sql, $problem_id, $output_dir, $idx, $start_line, $end_line);

    if ($result === false) {
        echo "<pre>DB 삽입 실패 - index: $idx</pre>";
        exit;
    } else {
        echo "<pre>DB 삽입 성공 - index: $idx</pre>";
    }
}

echo "<pre>모든 데이터 DB 삽입 완료.</pre>";

// TODO 
// 제작된 flowchart 이미지 파일을 HTML에 삽입하는 코드 추가

?>
