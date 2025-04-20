<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 2em; margin-bottom: 3em;">
    <h2 class="ui center aligned header">
        <i class="clipboard list icon"></i>
        문제 해결 전략 게시판
        <div class="sub header">자주 사용하는 보조 함수와 예제 코드를 포함한 전략 목록입니다</div>
    </h2>

    <?php if (!isset($list) || !is_array($list) || count($list) === 0): ?>
        <div class="ui warning message">
            <div class="header">전략 없음</div>
            <p>표시할 문제 해결 전략이 없습니다.</p>
        </div>
    <?php else: ?>
        <div class="ui styled fluid accordion">
            <?php foreach ($list as $item): ?>
                <div class="title">
                    <i class="dropdown icon"></i>
                    <?php echo htmlspecialchars($item['title']) ?> (문제 ID: <?php echo $item['problem_id'] ?>)
                </div>
                <div class="content">
                    <div class="ui segment">
                        <h4 class="ui dividing header">문제 설명</h4>
                        <p><?php echo nl2br(htmlspecialchars($item['description'])) ?></p>

                        <h4 class="ui dividing header">보조 함수 목록</h4>
                        <div class="ui list">
                            <?php foreach (explode(',', $item['helper_functions']) as $func): ?>
                                <div class="item"><i class="code icon"></i><?php echo htmlspecialchars(trim($func)) ?></div>
                            <?php endforeach; ?>
                        </div>

                        <h4 class="ui dividing header">예제 코드</h4>
                        <pre style="background-color: #f9f9f9; padding: 1em; border-radius: 6px; overflow-x: auto;"><?php echo htmlspecialchars($item['example_code']) ?></pre>

                        <div style="margin-top: 1em;">
                            <a class="ui primary mini button" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                <i class="search icon"></i> 전략 상세 보기
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
  // Semantic UI accordion
  $('.ui.accordion').accordion();
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
