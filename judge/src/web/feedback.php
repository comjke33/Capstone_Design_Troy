<?php
// 데이터베이스 연결
include("template/$OJ_TEMPLATE/header.php");
include("include/db_info.inc.php"); // 데이터베이스 연결을 위한 파일

// solution_id 값을 가져오기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

// solution_id가 유효하지 않으면 종료
if ($solution_id <= 0) {
    echo "잘못된 요청입니다. solution_id가 필요합니다.";
    exit;
}

// 먼저, source_code 테이블에서 해당 solution_id를 확인합니다.
$sql = "SELECT solution_id FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);  // "i"는 solution_id가 정수형
$stmt->execute();
$stmt->bind_result($existing_solution_id);  // 기존 solution_id를 가져옴
$stmt->fetch();
$stmt->close();

// 만약 solution_id에 해당하는 solution_id가 없으면 종료
if (!$existing_solution_id) {
    echo "해당 solution_id를 찾을 수 없습니다.";
    exit;
}

// solution 테이블에 solution_id를 삽입 또는 업데이트
$sql = "INSERT INTO solution (solution_id) VALUES (?) ON DUPLICATE KEY UPDATE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $solution_id, $solution_id);  // "ii"는 solution_id가 정수형
$stmt->execute();
$stmt->close();

// 완료 후 메시지 출력
echo "solution 테이블에 solution_id가 삽입되었습니다.";
?>

