<h2 class="ui header">문단 피드백</h2>

<form method="post" class="ui form">
    <?php foreach ($paragraphs as $i => $block): ?>
        <div class="field step">
            <label><?= $block['description'] ?>
                <?php if (isset($results[$i]) && $results[$i]): ?> ✅<?php endif; ?>
            </label>
            <textarea name="para_<?= $i ?>" rows="6"
                class="ui textarea <?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
                <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
            <button class="ui blue button" type="submit">제출</button>
            <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
                <div class="ui hint message">피드백: <?= $block['hint'] ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</form>
