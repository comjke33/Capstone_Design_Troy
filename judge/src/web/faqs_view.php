<?php
require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');

// 1. GET 파라미터로 전략 ID 받아오기
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. strategy 테이블에서 전략 조회
$sql = "SELECT * FROM strategy WHERE id = ?";
$result = pdo_query($sql, $id);
$strategy = $result[0] ?? null;

if (!$strategy) {
    echo "<script>alert('해당 전략을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}
?>

<?php include("template/syzoj/faqs_view.php"); ?>