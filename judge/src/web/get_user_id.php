<?php
require_once "include/db_info.inc.php";
header("Content-Type: application/json");

// 선택할 user_id 목록
$allowed_user_ids = ['test1', 'test3', 'guideline_user1'];

// 쿼리에서 사용할 IN절 생성
$placeholders = implode(',', array_fill(0, count($allowed_user_ids), '?'));

$sql = "SELECT user_id FROM users WHERE user_id IN ($placeholders)";
$res = pdo_query($sql, ...$allowed_user_ids);

return ['test1', 'test3', 'guideline_user1'];