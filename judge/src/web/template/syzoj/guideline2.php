<div class="ui two column stackable grid">
  
  <!-- ✅ 왼쪽: 코드 작성 -->
  <div class="column ten wide">
    <div class="ui segment">
      <h4 class="ui dividing header">한 문단씩 풀기</h4>

      <!-- Form to input code for each section -->
      <form method="post" class="ui form">
        
        <?php
        // Loop through the extracted functions, loops, conditionals, and self-blocks
        // These should be provided or parsed from the `aaa.txt` file content
        foreach ($functions[0] as $i => $function) :
          $func_name = extract_function_name($function);
          $function_content = $functions[2][$i];
        ?>
          <div class="field">
            <label><strong>Function <?= $i + 1 ?>: <?= htmlspecialchars($func_name) ?></strong></label>
            <textarea name="code_<?= $i ?>" rows="6" placeholder="여기에 코드를 작성하세요..."><?= htmlspecialchars($function_content) ?></textarea>
          </div>
        <?php endforeach; ?>

        <?php foreach ($loops[0] as $i => $loop) :
          $loop_info = extract_loop_info($loop);
          $loop_content = $loops[2][$i];
        ?>
          <div class="field">
            <label><strong>Loop <?= $i + 1 ?>: <?= htmlspecialchars($loop_info) ?></strong></label>
            <textarea name="code_<?= $i + count($functions) ?>" rows="6" placeholder="여기에 코드를 작성하세요..."><?= htmlspecialchars($loop_content) ?></textarea>
          </div>
        <?php endforeach; ?>

        <?php foreach ($conditionals[0] as $i => $conditional) :
          $conditional_info = extract_conditional_info($conditional);
          $conditional_content = $conditionals[2][$i];
        ?>
          <div class="field">
            <label><strong>Conditional <?= $i + 1 ?>: <?= htmlspecialchars($conditional_info) ?></strong></label>
            <textarea name="code_<?= $i + count($functions) + count($loops) ?>" rows="6" placeholder="여기에 코드를 작성하세요..."><?= htmlspecialchars($conditional_content) ?></textarea>
          </div>
        <?php endforeach; ?>

        <?php foreach ($self_blocks[0] as $i => $self_block) :
          $self_block_info = extract_self_block_info($self_block);
          $self_block_content = $self_blocks[2][$i];
        ?>
          <div class="field">
            <label><strong>Self Block <?= $i + 1 ?>: <?= htmlspecialchars($self_block_info) ?></strong></label>
            <textarea name="code_<?= $i + count($functions) + count($loops) + count($conditionals) ?>" rows="6" placeholder="여기에 코드를 작성하세요..."><?= htmlspecialchars($self_block_content) ?></textarea>
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

      <div class="ui segment">
        <h5 class="ui dividing header">작성한 코드 피드백</h5>
        <p><strong>Function 피드백:</strong></p>
        <p>함수 정의 및 내용이 정확한지 확인합니다.</p>

        <p><strong>Loop 피드백:</strong></p>
        <p>반복문이 정확하게 작성되었는지 확인합니다.</p>

        <p><strong>Conditional 피드백:</strong></p>
        <p>조건문이 정확하게 작성되었는지 확인합니다.</p>

        <p><strong>Self Block 피드백:</strong></p>
        <p>자체적으로 정의된 블록이 올바르게 작성되었는지 확인합니다.</p>
      </div>
    </div>
  </div>

</div> <!-- End of UI Grid -->
