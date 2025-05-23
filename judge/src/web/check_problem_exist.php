<?php
require_once("include/db_info.inc.php");
header("Content-Type: application/json");

$problem_id = $_GET['id'] ?? '';
$problem_id = trim($problem_id);

// ID가 정수 형태가 아니면 바로 false
if (!ctype_digit($problem_id)) {
    echo json_encode(['exists' => false]);
    exit;
}

// 문제 존재 여부 쿼리
$sql = $pdo->prepare("SELECT COUNT(*) FROM problem WHERE problem_id = ?");
$sql->execute([$problem_id]);
$count = $sql->fetchColumn();

echo json_encode(['exists' => ($count > 0)]);
