<?php
// 에러 메시지 확인을 위해 설정
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB 연결
require_once('./include/db_info.inc.php');

// solution_id GET 파라미터로 받기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "❌ 유효하지 않은 solution_id입니다.";
    exit;
}

// source_code 테이블에서 solution_id와 source 가져오기
$sql = "SELECT solution_id, source FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($fetched_solution_id, $source);
$stmt->fetch();
$stmt->close();

// 결과 출력
if ($fetched_solution_id) {
    echo "<h2>🧾 Solution ID: <code>$fetched_solution_id</code></h2>";
    echo "<h3>📄 소스 코드:</h3>";
    echo "<pre style='background:#f4f4f4; padding:15px; border-radius:6px; font-family:monospace; overflow:auto;'>";
    echo htmlspecialchars($source);
    echo "</pre>";
} else {
    echo "<p>❌ 해당 solution_id에 대한 소스코드를 찾을 수 없습니다.</p>";
}
?>
