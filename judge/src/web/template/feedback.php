<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; max-width: 1200px;">
    <div class="ui stackable grid">   
        <div class="eight wide column">
            <div class="ui segment">
                <h2 class="ui header" style="color: #21ba45;">📝 제출한 코드</h2>
                <?php
                    $code_lines = explode("\n", $code ?? '');
                    echo '<pre style="background-color: #f9f9f9; padding: 1em;">';
                    foreach ($code_lines as $i => $line) {
                        echo htmlspecialchars($i + 1) . ': ' . htmlspecialchars($line) . "\n";
                    }
                    echo '</pre>';
                ?>
            </div>
        </div>

        <div class="eight wide column">
            <div class="ui segment">
                <h2 class="ui header" style="color: #2185d0;">📋 피드백 가이드</h2>

                <?php if (!empty($feedback_error)): ?>
                    <div class="ui red message"><?php echo htmlspecialchars($feedback_error); ?></div>
                <?php elseif (!empty($link_results)): ?>
                    <?php foreach ($link_results as $result): ?>
                        <div class="ui segment">
                            <h4>🔍 오류 메시지: <?php echo htmlspecialchars($result['message']); ?></h4>
                            <?php foreach ($result['matches'] as $match): ?>
                                <div style="margin-left: 1em;">
                                    <p><strong>개념:</strong> <?php echo htmlspecialchars($match['concepts']); ?></p>
                                    <p><strong>블록:</strong> <?php echo htmlspecialchars($match['block']); ?></p>
                                    <?php if (!empty($match['link'])): ?>
                                        <a href="<?php echo htmlspecialchars($match['link']); ?>" target="_blank" style="font-weight: bold; color: #2185d0;">📚 문법 개념 링크</a>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ui positive message">
                        <div class="header">🎉 문법 오류가 없습니다!</div>
                        <p>코드에 문법 오류가 발견되지 않았습니다.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
