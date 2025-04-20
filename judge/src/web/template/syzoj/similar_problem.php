<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 2em">
    <h2 class="ui header">유사 문제 추천</h2>

    <?php if (!isset($similar_problems) || count($similar_problems) === 0): ?>
        <div class="ui warning message">
            <div class="header">유사 문제 없음</div>
            <p>제출 코드와 유사한 문제를 찾을 수 없습니다.</p>
        </div>
    <?php else: ?>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th>문제 ID</th>
                    <th>문제 제목</th>
                    <th>출처</th>
                    <th>유사도 (%)</th>
                    <th>이동</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($similar_problems as $problem): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($problem['id']); ?></td>
                        <td><?php echo htmlspecialchars($problem['title']); ?></td>
                        <td><?php echo htmlspecialchars($problem['source']); ?></td>
                        <td><?php echo round($problem['score'], 2); ?>%</td>
                        <td>
                            <a class="ui blue mini button" href="<?php echo $problem['url']; ?>" target="_blank">이동</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
