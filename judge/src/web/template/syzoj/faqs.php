<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 2em">
    <h2 class="ui dividing header">문제 해결 전략 게시판</h2>
    <?php if (!isset($list) || !is_array($list) || count($list) === 0): ?>
        <div class="ui warning message">
            <div class="header">표시할 전략이 없습니다.</div>
            새로운 전략을 등록하거나, 나중에 다시 확인해 주세요.
        </div>
    <?php else: ?>
        <table class="ui celled table">
            <thead>
                <tr>
                    <th style="width: 10%;">전략 ID</th>
                    <th style="width: 25%;">제목</th>
                    <th style="width: 15%;">기초 문제 ID</th>
                    <th style="width: 40%;">요약 설명</th>
                    <th style="width: 10%;">보기</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id']) ?></td>
                        <td>
                            <a href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                <?php echo htmlspecialchars($item['title']) ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($item['problem_id']) ?></td>
                        <td><?php echo mb_substr(strip_tags($item['description']), 0, 80) ?>...</td>
                        <td>
                            <a class="ui mini primary button" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                전략 보기
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
