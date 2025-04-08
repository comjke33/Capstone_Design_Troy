<?php
require_once("./include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
$source = "";
$sid = 0;

if ($solution_id > 0) {
    $sql = "SELECT solution_id, source FROM source_code WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->bind_result($sid, $source);
    $stmt->fetch();
    $stmt->close();
}

// 👉 화면은 이쪽에서 렌더링
include("template/$OJ_TEMPLATE/showfeedback.php");
