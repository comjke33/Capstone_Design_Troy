<div class="ui two column stackable grid">
    
    <!-- ✅ 왼쪽: 전체 소스코드 보기 -->
    <div class="column ten wide">
        <div class="ui segment">
            <h4 class="ui top attached header">제출 번호: <code><?= htmlspecialchars($sid) ?></code></h4>
            <div class="ui attached segment" style="background: #f9f9f9; padding: 15px; border-radius: 0; font-family: monospace; white-space: pre-wrap; overflow-x: auto;">
<?= htmlspecialchars($source ?? '소스코드가 없습니다.') ?>
            </div>
        </div>
    </div>

    <!-- ✅ 오른쪽: 피드백 입력 -->
    <div class="column six wide">
        <div class="ui segment">
            <h4 class="ui dividing header">문단 피드백</h4>

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

        </div>
    </div>

</div>
