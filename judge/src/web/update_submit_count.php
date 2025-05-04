<?php
require_once("oj-header.php"); // 로그인 확인용
require_once("include/db_info.inc.php");

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Not logged in";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "UPDATE submit SET count = count + 1 WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);

echo "OK";
?>