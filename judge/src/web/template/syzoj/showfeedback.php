<?php
$show_title = "$MSG_STATUS - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>

<script src="template/<?php echo $OJ_TEMPLATE ?>/js/textFit.min.js"></script>
<div class="padding">

<?php
require_once("./include/db_info.inc.php");

// GET 파라미터로 solution_id 받기
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

if ($solution_id <= 0) {
    echo "<p>❌ 유효하지 않은 solution_id입니다.</p>";
} else {
    // source_code 테이블에서 소스코드 조회
    $sql = "SELECT solution_id, source FROM source_code WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->bind_result($sid, $source);
    $stmt->fetch();
    $stmt->close();

    if ($sid) {
        echo "<h2>🧾 제출 번호: <code>$sid</code></h2>";
        echo "<h3>📄 소스 코드</h3>";
        echo "<pre style='background:#f8f8f8; padding:15px; border:1px solid #ccc; border-radius:6px; font-family:monospace; overflow:auto;'>";
        echo htmlspecialchars($source);
        echo "</pre>";
    } else {
        echo "<p>❌ 해당 제출을 찾을 수 없습니다.</p>";
    }
}
?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
