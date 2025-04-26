<?php
/** @var string $OJ_SID */
/** @var array $OJ_BLOCK_TREE */
/** @var array $OJ_CORRECT_ANSWERS */
?>
<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<style>
/* 기존 style 그대로 유지 */
...
</style>

<div class="main-layout">
    <div class="left-panel">
        <?php
        function render_tree_plain($blocks, &$answer_index = 0) {
            $html = "";
            foreach ($blocks as $block) {
                ...
            }
            return $html;
        }

        $answer_index = 0;
        echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <div class="right-panel" id="feedback-panel">
        <h4>📝 피드백</h4>
    </div>
</div>

<!-- ⭐ 이 부분만 추가 -->
<script src="template/syzoj/js/guideline1.js"></script>
<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
initializeGuideline(correctAnswers);
</script>
