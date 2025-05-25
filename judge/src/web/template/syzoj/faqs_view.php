
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; margin-bottom: 5em;">
    <h2 class="ui dividing header"><?= htmlspecialchars($strategy['title']) ?></h2>

    <div class="ui segment">
        <p><strong>📌 문제 ID:</strong> <?= intval($strategy['problem_id']) ?></p>
        <p><strong>🕓 작성 시각:</strong> <?= htmlspecialchars($strategy['created_at']) ?></p>

        <h4 class="ui header">📖 전략 설명</h4>
        <div class="ui message">
            <?= nl2br(htmlspecialchars($strategy['description'])) ?>
        </div>

        <?php if (!empty($strategy['helper_function'])): ?>
        <h4 class="ui header">🔧 보조 함수</h4>
        <pre style="background:#f4f4f4; padding:1em; border-radius:5px;">
<?= htmlspecialchars($strategy['helper_function']) ?>
        </pre>
        <?php endif; ?>

        <?php if (!empty($strategy['solution_code'])): ?>
        <h4 class="ui header">💡 예제 코드</h4>
        <pre style="background:#f9f9f9; padding:1em; border-radius:5px;">
<?= htmlspecialchars($strategy['solution_code']) ?>
        </pre>
        <?php endif; ?>
    </div>

    <div style="text-align:right;">
        <a href="strategy_board.php" class="ui button">← 전략 목록으로</a>
    </div>
</div>

<!-- 렌더링 파일 불러오기 -->
<?php include("template/syzoj/faqs_view.php"); ?>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
