<div class="ui two column stackable grid">
    
<!-- ✅ 왼쪽: 한 줄씩문제 풀기 -->
<div class="column six wide">
        <div class="ui segment">
            <h4 class="ui dividing header">한 줄씩 풀기</h4>

            <form method="post" class="ui form">
                <?php foreach ($correct_lines as $i => $line): ?>
                    <div class="field step">
                        <label>
                            <?= ($i+1) . '. ' . $descriptions[$i] ?>
                            <?php if (isset($results[$i]) && $results[$i]): ?> ✅<?php endif; ?>
                        </label>

                        <textarea name="line_<?= $i ?>" rows="2"
                            class="ui textarea"
                            style="<?= isset($results[$i]) && $results[$i] ? 'background-color: #f0f0f0;' : '' ?>"
                            <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>

                        <button class="ui blue button" type="submit" name="submit" value="<?= $i ?>">제출</button>
                    </div>
                <?php endforeach; ?>
            </form>

        </div>
    </div>

    <!-- ✅ 오른쪽: 깔끔한 HUSTOJ 스타일 피드백 -->
    <div class="column six wide">
      <div class="ui segment">
        <h4 class="ui dividing header">피드백</h4>
        <div class="ui info message">
          <p><strong>출력 부분에서 출력 양식이 틀렸습니다.</strong></p>
          <p>문제 양식을 확인하고 알맞게 제출해주세요!</p>
        </div>
      </div>
    </div>

    

</div>
