<?php
include("include/db_info.inc.php");

$filename = ".txt";

// 인자를 공백으로 구분해 Python 스크립트에 전달
$command = "cd /home/Capstone_Design_Troy/py/ && python3 make_flowcharts.py " . escapeshellarg($filename);
$result = shell_exec($command);
if ($result === null) {
    echo "<pre>Python 스크립트 실행 실패!</pre>";
    exit;
}

?>
