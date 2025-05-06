<?php
include("include/db_info.inc.php");

$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;

// 인자를 공백으로 구분해 Python 스크립트에 전달
$tagged_guideline = "./tagged_guideline/" . $problem_id . ".txt";


// problem을 가져오는 SQL 쿼리
$sql = "SELECT description FROM problem WHERE problem_id = ?";
$problem = pdo_query($sql, $problem_id);

// $problem = "problem.txt";
$output_dir = "/output/";

echo "<pre>Python 스크립트 실행 중...</pre>";

$command = "cd /home/Capstone_Design_Troy/py/ && python3 make_flowcharts.py "
    . escapeshellarg($tagged_guideline) . " "
    . escapeshellarg($problem) . " "
    . escapeshellarg($output_dir) . " "
    . escapeshellarg($problem_id);

$result = shell_exec($command);

if ($result === null) {
    echo "<pre>Python 스크립트 실행 실패!</pre>";
    exit;
}
echo "<pre>$result</pre>";

// TODO 
// 제작된 flowchart 이미지 파일을 HTML에 삽입하는 코드 추가


?>

