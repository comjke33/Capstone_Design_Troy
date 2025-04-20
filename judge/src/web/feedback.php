<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<?php require_once("include/db_info.inc.php"); ?>

<?php
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;

$feedback_data = [];
$feedback_error = null;

if ($solution_id <= 0) {
    $feedback_error = "❌ 유효하지 않은 요청입니다.";
} else {
    $sql = "SELECT problem_id, feedback_code FROM feedback WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $solution_id);
        $stmt->execute();
        $stmt->bind_result($problem_id, $feedback_code);

        while ($stmt->fetch()) {
            $feedback_data[] = [
                'problem_id' => $problem_id,
                'feedback_code' => $feedback_code
            ];
        }

        $stmt->close();
    } else {
        $feedback_error = "❌ 데이터베이스 오류가 발생했습니다.";
    }
}
?>

<div class="ui container" style="margin-top: 3em; max-width: 850px;">
    <div class="ui segment" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px;">
        <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #2185d0;">
            📋 제출 피드백
        </h2>

        <?php if ($feedback_error): ?>
            <div class="ui negative message">
                <div class="header"><?php echo $feedback_error; ?></div>
            </div>

        <?php elseif (empty($feedback_data)): ?>
            <div class="ui warning message">
                <div class="header">📭 피드백이 존재하지 않습니다.</div>
                <p>이 제출에 대한 피드백이 아직 등록되지 않았습니다.</p>
            </div>

        <?php else: ?>
            <?php foreach ($feedback_data as $item): ?>
                <div class="ui fluid styled accordion">
                    <div class="title active">
                        <i class="dropdown icon"></i>
                        문제 ID: <?php echo htmlspecialchars($item['problem_id']); ?>
                    </div>
                    <div class="content active" style="background: #fcfcfc;">
                        <div class="ui attached segment" style="border-left: 4px solid #21ba45; background: #f9f9f9;">
                            <pre style="white-space: pre-wrap; word-break: break-word; margin: 0; font-size: 1em; color: #333;">
<?php echo htmlspecialchars($item['feedback_code']); ?>
                            </pre>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Semantic UI accordion 기능 활성화
    $('.ui.accordion').accordion();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
