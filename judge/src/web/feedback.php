<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<?php
require_once("include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
if ($solution_id <= 0) {
    echo "<div class='ui negative message'><div class='header'>❌ 유효하지 않은 요청입니다.</div></div>";
    include("template/$OJ_TEMPLATE/footer.php");
    exit;
}

// feedback 테이블에서 solution_id 기준으로 조회
$sql = "SELECT problem_id, feedback_code FROM feedback WHERE solution_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $solution_id);
$stmt->execute();
$stmt->bind_result($problem_id, $feedback_code);

$has_result = false;
?>

<div class="ui container" style="margin-top: 2em">
    <h2 class="ui dividing header">📋 제출 피드백</h2>

    <?php while ($stmt->fetch()): ?>
        <?php $has_result = true; ?>
        <div class="ui segment">
            <p><strong>문제 ID:</strong> <?php echo $problem_id; ?></p>
            <div style="background: #f4f4f4; padding: 1em; border-radius: 6px;">
                <pre style="margin: 0;"><code><?php echo htmlspecialchars($feedback_code); ?></code></pre>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if (!$has_result): ?>
        <div class="ui warning message">
            <div class="header"> 피드백이 존재하지 않습니다.</div>
            <p>이 제출에 대한 피드백이 아직 등록되지 않았습니다.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
include("template/$OJ_TEMPLATE/footer.php");
?>
