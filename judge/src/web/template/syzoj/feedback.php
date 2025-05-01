<?php require_once("include/db_info.inc.php"); ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; max-width: 1200px;">
    <div class="ui stackable grid">
        <!-- 왼쪽: 제출 코드 -->
        <div class="eight wide column">
            <div class="ui segment" style="height: 100%; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px;">
                <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #21ba45;">
                    📝 제출한 코드
                </h2>
                <pre style="background-color: #f9f9f9; padding: 1em; border-radius: 5px; height: 600px; overflow: auto;"><?php echo $code?></pre>
            </div>
        </div>

        <!-- 오른쪽: 피드백 가이드 -->
        <div class="eight wide column">
            <div class="ui segment" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px; height: 100%;">
                <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #2185d0;">
                    📋 피드백 가이드
                </h2>
                <?php if (!empty($link_results)): ?>
                    <?php foreach ($link_results as $result): ?>
                        <div class="ui segment">
                            <h4>🔍 오류 메시지: <?php echo htmlspecialchars($result['message']); ?></h4>
                            <?php foreach ($result['matches'] as $match): ?>
                                <div style="margin-left: 1em;">
                                    <p><strong>개념:</strong> <?php echo htmlspecialchars($match['concepts']); ?></p>
                                    <p><strong>블록:</strong> <?php echo htmlspecialchars($match['block']); ?></p>
                                    <a href="<?php echo htmlspecialchars($match['link']); ?>" target="_blank" style="font-weight: bold; color: #2185d0;">📚 문법 개념 링크</a>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ui positive message">
                        <div class="header">🎉 문법 오류가 없습니다!</div>
                        <p>코드에 문법 오류가 발견되지 않았습니다. 논리적인 부분에서 잘못된 부분이 없는지 다시 한번 살펴보세요!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    $('.ui.accordion').accordion();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
