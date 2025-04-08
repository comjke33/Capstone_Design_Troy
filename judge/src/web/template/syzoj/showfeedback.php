<div class="ui two column stackable grid">

  <!-- ✅ 왼쪽: 전체 코드 입력창 -->
  <div class="column ten wide">
    <div class="ui segment">
      <h4 class="ui dividing header">전체 코드 입력</h4>
      <form method="post" class="ui form">
        <div class="field">
          <label>
            'Hello World'를 출력하시오.
            <?php if ($result): ?> ✅<?php endif; ?>
          </label>
          <textarea name="full_source" rows="20" style="font-family: monospace; <?= $result ? 'background-color: #f0f0f0;' : '' ?>" 
            class="ui textarea"
            <?= $result ? 'readonly' : '' ?>
          ><?= htmlspecialchars($_POST['full_source'] ?? '') ?></textarea>
        </div>
        <button class="ui blue button" type="submit">제출</button>

        <!-- ✅ 정답일 경우에만 완료 버튼 표시 -->
        <?php if ($result): ?>
          <div style="text-align: right; margin-top: 1em;">
            <a href="selectlevel.php?solution_id=<?= htmlspecialchars(urlencode($sid)) ?>" class="ui yellow button">완료</a>
          </div>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- ✅ 오른쪽: 피드백 -->
  <div class="column six wide">
    <div class="ui segment">
      <h4 class="ui dividing header">피드백</h4>
      <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <?php if ($result): ?>
          <div class="ui positive message">
            <div class="header">정답입니다!</div>
            <p>완료 버튼을 눌러 다음 단계로 이동하세요.</p>
          </div>
        <?php else: ?>
          <div class="ui warning message">
            <div class="header">틀렸습니다</div>
            <p>출력 양식 또는 문법 오류가 없는지 다시 확인하세요.</p>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="ui info message">
          <p>전체 코드를 작성하고 제출을 누르면 피드백이 표시됩니다.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>
