<?php require_once("include/db_info.inc.php"); ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<div class="ui container" style="margin-top: 3em; max-width: 850px;">
    <div class="ui segment" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px;">
        <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #2185d0;">
            📋 피드백 가이드
        </h2>
        <?php if ($feedback_error): ?>
            <div class="ui negative message">
                <div class="header"><?php echo $feedback_error; ?></div>
            </div>

        <?php elseif (!isset($link_result) || empty($link_result)): ?>
            <div class="ui warning message">
                <div class="header">📭 피드백이 존재하지 않습니다.</div>
                <p>이 제출에 대한 피드백이 아직 등록되지 않았습니다.</p>
            </div>

        <?php else: ?>
            <?php foreach ($link_results as $result): ?>
            <div class="ui segment">
                <h4>🔍 오류 메시지: <?php echo htmlspecialchars($result['message']); ?></h4>
                <?php foreach ($result['matches'] as $match): ?>
                    <div style="margin-left: 1em;">
                        <p><strong>개념:</strong> <?php echo htmlspecialchars($match['concepts']); ?></p>
                        <p><strong>블록:</strong> <?php echo htmlspecialchars($match['block']); ?></p>
                        <a href="<?php echo htmlspecialchars($match['link']); ?>" target="_blank">관련 링크</a>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    $('.ui.accordion').accordion();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
