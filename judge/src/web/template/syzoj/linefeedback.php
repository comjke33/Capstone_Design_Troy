<h3>(1단계)</h3>
<style>
    .correct { background-color: #d4edda; }
    .hint { color: gray; font-size: 0.9em; }
    .step { margin-bottom: 20px; }
</style>

<form method="post">
    <?php foreach ($correct_lines as $i => $line): ?>
    <div class="step">
        <p><?= ($i+1) . '. ' . $descriptions[$i] ?>
            <?php if (isset($results[$i]) && $results[$i]): ?>
                ✅
            <?php endif; ?>
        </p>
        <textarea name="line_<?= $i ?>" rows="2" cols="40"
            class="<?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
            <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>
        ><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
        <button type="submit" name="submit" value="<?= $i ?>">제출</button>
        <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
            <div class="hint">힌트: Scanf를 사용할 때는 변수 앞에 ~~</div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</form>
