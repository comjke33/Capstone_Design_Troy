<h2 class="ui header">한 줄 피드백</h2>

<form method="post" class="ui form">
    <?php foreach ($correct_lines as $i => $line): ?>
        <div class="field step">
            <label><?= ($i+1) . '. ' . $descriptions[$i] ?>
                <?php if (isset($results[$i]) && $results[$i]): ?> ✅<?php endif; ?>
            </label>
            <textarea name="line_<?= $i ?>" rows="2"
                class="ui textarea <?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
                <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
            <button class="ui blue button" type="submit" name="submit" value="<?= $i ?>">제출</button>
            <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
                <div class="ui hint message">힌트: Scanf를 사용할 때는 변수 앞에 ~~</div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</form>
