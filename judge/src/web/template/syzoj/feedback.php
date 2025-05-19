<?php require_once("include/db_info.inc.php"); ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; max-width: 1200px;">
    <div class="ui stackable grid">   
        <!-- 왼쪽: 제출 코드 -->
        <div class="eight wide column">
            <div class="ui segment" style="height: 100%; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-radius: 10px; padding: 1rem;">
                <h2 class="ui header" style="font-weight: 500; font-size: 1.5em; color: #21ba45;">
                    📝 제출한 코드
                </h2>
                <?php
                    $code_lines = explode("\n", $code);
                    $error_lines = [];
                    if (isset($data['stderrs']) && is_array($data['stderrs'])) {
                        foreach ($data['stderrs'] as $stderr) {
                            if (isset($stderr['line'])) {
                                $error_lines[] = intval($stderr['line']);
                            }
                        }
                    }
                ?>

                <div style="max-height: 400px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 14px; background-color: #1e1e1e; border-radius: 5px; padding: 10px; color: #d4d4d4;">

                    <table style="width: 100%; border-collapse: collapse;">
                        <tbody>
                        <?php foreach ($code_lines as $index => $line): 
                            $line_number = $index + 1;
                            $is_error = in_array($line_number, $error_lines);
                        ?>
                            <tr style="<?php echo $is_error ? 'background-color: #5a1e1e;' : ''; ?>">
                                <td style="padding: 0 10px 0 5px; text-align: right; user-select: none; color: <?php echo $is_error ? '#ff6c6b' : '#858585'; ?>; width: 40px; border-right: 1px solid #333;">
                                    <?php echo htmlspecialchars($line_number); ?>
                                </td>
                                <td style="padding: 0 10px; white-space: pre-wrap; word-break: break-word; color: <?php echo $is_error ? '#ff6c6b' : '#d4d4d4'; ?>;">
                                    <?php echo htmlspecialchars($line); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
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
                            <h3>🔍 오류 메시지: <?php echo htmlspecialchars($result['message']); ?></h4>
                            <?php foreach ($result['matches'] as $match): ?>
                                <div style="margin-left: 1em;">
                                    <p><strong>개념:</strong> <?php echo htmlspecialchars($match['concepts']); ?></p>
                                    <p><strong>코멘트:</strong> <?php echo htmlspecialchars($match['block']); ?></p>
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
