<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB 연결
require_once('./include/db_info.inc.php');

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "❌ 유효하지 않은 solution_id입니다.";
    exit;
}

if (!isset($mysqli)) {
    die("❌ \$mysqli 객체가 존재하지 않습니다. DB 연결 확인 필요.");
}

$sql = "SELECT source FROM source_code WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    die("❌ prepare() 실패: " . $mysqli->error);
}
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($source);
$stmt->fetch();
$stmt->close();

if ($source) {
    echo "<h2>📝 Solution ID: $solution_id 의 소스코드</h2>";
    echo "<pre style='background:#f4f4f4;padding:15px;border-radius:6px;overflow:auto;'>";
    echo htmlspecialchars($source);
    echo "</pre>";
} else {
    echo "<p>❌ 해당 solution_id에 대한 소스코드를 찾을 수 없습니다.</p>";
}
?>
