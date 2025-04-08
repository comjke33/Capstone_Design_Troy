<h2>(2단계)</h2>
<style>
    .correct { background-color: #e0e0e0; }
    .hint { color: gray; font-size: 0.9em; margin-top: 4px; }
    .step { margin-bottom: 30px; }
    textarea { width: 500px; height: 120px; font-family: monospace; }
</style>

<form method="post">
    <?php foreach ($paragraphs as $i => $block): ?>
        <div class="step">
            <p><?= $block['description'] ?>
                <?php if (isset($results[$i]) && $results[$i]): ?>
                    ✅
                <?php endif; ?>
            </p>
            <textarea name="para_<?= $i ?>" class="<?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
                <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>
            ><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
            <br>
            <button type="submit">제출</button>
            <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
                <div class="hint">피드백 칸<br><?= $block['hint'] ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</form>
