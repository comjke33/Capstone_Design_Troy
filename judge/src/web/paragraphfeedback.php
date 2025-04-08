<h2 class="ui header">문단 피드백</h2>

<div class="ui segment">
    <form method="post" class="ui form">
        <?php foreach ($correct_paragraphs as $i => $answer): ?>
            <div class="field step">
                <label>
                    <?= $descriptions[$i] ?>
                    <?php if (isset($results[$i]) && $results[$i]): ?> ✅<?php endif; ?>
                </label>

                <textarea name="para_<?= $i ?>" rows="6"
                    class="ui textarea"
                    style="<?= isset($results[$i]) && $results[$i] ? 'background-color: #f0f0f0;' : '' ?>"
                    <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>

                <button class="ui blue button" type="submit">제출</button>
            </div>
        <?php endforeach; ?>
    </form>
</div>
