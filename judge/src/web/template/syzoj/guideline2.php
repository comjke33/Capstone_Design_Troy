<div class="ui two column stackable grid">

  <!-- ✅ 왼쪽: 한 문단씩 풀기 -->
  <div class="column ten wide">
    <div class="ui segment">
      <h4 class="ui dividing header">한 문단씩 풀기</h4>

      <form method="post" class="ui form">
        <?php foreach ($exercises as $i => $desc): ?>
          <div class="field">
            <label><strong>Step <?= $i + 1 ?>.</strong> <?= nl2br(htmlspecialchars($desc)) ?></label>
            <textarea name="code_<?= $i ?>" rows="6" placeholder="여기에 코드를 작성하세요..."></textarea>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="ui blue button">제출</button>
      </form>

    </div>
  </div>

  <!-- ✅ 오른쪽: 피드백 안내 -->
  <div class="column six wide">
    <div class="ui segment">
      <h4 class="ui dividing header">피드백</h4>
      <div class="ui info message">
        <p><strong>문단을 정확히 작성하면 체크 표시가 나타납니다.</strong></p>
        <p>모든 문단이 정답일 경우에만 '완료' 버튼이 활성화됩니다.</p>
      </div>
    </div>
  </div>

</div>
