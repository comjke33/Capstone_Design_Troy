<?php
require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');

// 1. GET 파라미터로 전략 ID 받아오기
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. strategy 테이블에서 전략 조회
$sql = "SELECT * FROM strategy WHERE id = ?";
$result = pdo_query($sql, $id);
$strategy = $result[0] ?? null;

if (!$strategy) {
    echo "<script>alert('해당 전략을 찾을 수 없습니다.'); history.back();</script>";
    exit;
}
?>

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

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
