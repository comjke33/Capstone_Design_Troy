<div class="ui two column stackable grid">

  <!-- ✅ 왼쪽: 한 줄씩 문제 풀기 -->
  <div class="column ten wide">
    <div class="ui segment">
      <h4 class="ui dividing header">한 줄씩 풀기</h4>

      <form method="post" class="ui form">
        <?php foreach ($correct_lines as $i => $line): ?>
          <div class="field step">
            <label>
              <?= ($i + 1) . '. ' . $descriptions[$i] ?>
              <?php if (isset($results[$i]) && $results[$i]): ?> ✅<?php endif; ?>
            </label>

            <textarea name="line_<?= $i ?>" rows="2"
              class="ui textarea"
              style="<?= isset($results[$i]) && $results[$i] ? 'background-color: #f0f0f0;' : '' ?>"
              <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>
            ><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>

            <button class="ui blue button" type="submit" name="submit" value="<?= $i ?>">제출</button>
          </div>
        <?php endforeach; ?>

        <!-- ✅ 모든 줄 정답 시 완료 버튼 출력 -->
        <?php if (count($results) === count($correct_lines) && array_sum($results) === count($correct_lines)): ?>
          <div style="text-align: right; margin-top: 1em;">
            <a href="selectlevel.php?solution_id=<?= htmlspecialchars(urlencode($sid)) ?>" class="ui yellow button">완료</a>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- ✅ 오른쪽: 안내 피드백 -->
  <div class="column six wide">
    <div class="ui segment">
      <h4 class="ui dividing header">피드백</h4>
      <div class="ui info message">
        <p><strong>각 줄을 정확히 입력하면 체크표시가 나타납니다.</strong></p>
        <p>모든 줄이 정답일 경우에만 '완료' 버튼이 활성화됩니다.</p>
      </div>
    </div>
  </div>

</div>
