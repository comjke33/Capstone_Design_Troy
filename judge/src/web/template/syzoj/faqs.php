<?php include("template/$OJ_TEMPLATE/header.php");?>

<?php
if (!isset($list) || !is_array($list)) {
    echo "<h2>문제 전략 게시판</h2><p>표시할 전략이 없습니다.</p>";
    return;
}
?>
<div class="ui container" style="margin-top: 2em">
    <h2 class="ui header">문제 해결 전략 게시판</h2>
    <div class="ui divided items">
        <?php foreach ($list as $item): ?>
            <div class="item">
                <div class="content">
                    <a class="header" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                        <?php echo htmlspecialchars($item['title']) ?>
                    </a>
                    <div class="meta">
                        <span>기초 문제 ID: <?php echo $item['problem_id'] ?></span>
                    </div>
                    <div class="description">
                        <p><?php echo mb_substr(strip_tags($item['description']), 0, 80) ?>...</p>
                    </div>
                    <div class="extra">
                        <a class="ui primary button" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                            전략 보기
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");