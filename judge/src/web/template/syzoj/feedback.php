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
                <?php

                    // 코드 문자열 → 줄별 배열로 변환
                    $code_lines = explode("\n", $code);
                    $error_lines = array();

                    if (isset($data['compile_result']['stderrs']) && is_array($data['compile_result']['stderrs'])) {
                        foreach ($data['compile_result']['stderrs'] as $stderr) {
                            if (isset($stderr['line'])) {
                                $error_lines[] = intval($stderr['line']);
                            }
                        }
                    }

                    echo '<pre style="background-color: #f9f9f9; padding: 1em; border-radius: 5px;">';
                    foreach ($code_lines as $index => $line) {
                        $line_number = $index + 1;
                        if (in_array($line_number, $error_lines)) {
                            echo '<span style="color: red; font-weight: bold;">' . htmlspecialchars($line_number) . ': ' . htmlspecialchars($line) . "</span>\n";
                        } else {
                            echo htmlspecialchars($line_number) . ': ' . htmlspecialchars($line) . "\n";
                        }
                    }
                    echo '</pre>';
                ?>
            </div>
        </div>

        <!-- 오른쪽: 피드베크 가이드 -->
        <div class="eight wide column">
            <div class="ui segment" style="box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px; height: 100%;">
                <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #2185d0;">
                    📋 피드백 가이드
                </h2>
                <?php if (!empty($link_results)): ?>
                    <?php foreach ($link_results as $result): ?>
                        <div class="ui segment">
                            <h4>🔍 오류 메시지: <?php echo htmlspecialchars($result['message']); ?></h4>
                            <p><strong>개념 ID:</strong> <?php echo htmlspecialchars($result['concept_id']); ?></p>
                            <?php foreach ($result['matches'] as $match): ?>
                                <div style="margin-left: 1em;">
                                    <p><strong>개념:</strong> <?php echo htmlspecialchars($match['concepts']); ?></p>
                                    <?php if (!empty($match['highlighted_code'])): ?>
                                        <p><strong>오류 코드:</strong></p>
                                        <pre style="background-color: #f4f4f4; padding: 1em; border-radius: 5px; color: #c7254e;"><?php echo htmlspecialchars($match['highlighted_code']); ?></pre>
                                    <?php else: ?>
                                        <p><strong>블록:</strong> <?php echo htmlspecialchars($match['block']); ?></p>
                                    <?php endif; ?>
                                    <a href="<?php echo htmlspecialchars($match['link']); ?>" target="_blank" style="font-weight: bold; color: #2185d0;">📚 문법 개념 링크</a>
                                </div>
                                <hr>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="ui positive message">
                        <div class="header">🎉 문법 오류가 없습니다!</div>
                        <p>코드에 문법 오류가 발견되지 않았습니다.<br>
                        논리적인 방면에서 잘못된 부분은 없는지 다시 한번 살펴보세요!</p>
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
