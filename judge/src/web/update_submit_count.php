<?php
// require_once("oj-header.php"); // 로그인 확인용
@session_start();
require_once("include/db_info.inc.php");

// if (!isset($_SESSION['user_id'])) {
//     http_response_code(403);
//     echo "Not logged in";
//     exit();
// }

$user_id = $_SESSION[$OJ_NAME . '_user_id'];

$sql = "INSERT INTO submit (user_id, submit_count)
        VALUES (?, 1)
        ON DUPLICATE KEY UPDATE submit_count = submit_count + 1";

$plus = pdo_query($sql, [$user_id]);

echo "OK";
?>