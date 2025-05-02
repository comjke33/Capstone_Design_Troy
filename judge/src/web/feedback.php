<?php
require_once('include/db_info.inc.php');

// 1. solution_id 받아오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
$code = "";
$pull_result = "";
$link_results = [];


// 2. DB에서 코드 가져오기
if ($solution_id > 0) {
    $sql = "SELECT source FROM source_code_user WHERE solution_id = '$solution_id'";
    $row = pdo_query($sql);

    if (!empty($row)) {
        $code = $row[0][0];
    } else {
        exit;
    }
} else {
    exit;
}

// 3. pull.py 실행
$escaped_code = escapeshellarg($code);
$cmd = "cd /home/Capstone_Design_Troy/py/ && python3 pull.py $escaped_code";
$pull_result = shell_exec($cmd);


if ($pull_result === null) {
    exit;
}

// 4. JSON 디코딩
$data = json_decode($pull_result, true);
if ($data === null) {
    exit;
}

// 5. linked 결과 확인
if (isset($data['linked']) && is_array($data['linked'])) {
    $link_results = $data['linked'];
} else {
}

include("template/syzoj/feedback.php");
?>
