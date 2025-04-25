<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
  <!-- Step 버튼들을 위한 UI -->
  <div class="ui large buttons" id="step_buttons">
    <button class="ui blue button" id="step1_button">Step 1</button>
    <button class="ui blue button" id="step2_button">Step 2</button>
    <button class="ui blue button" id="step3_button">Step 3</button>
  </div>

  <!-- 동적으로 내용이 로드될 부분 -->
  <div id="content_area" class="ui segment" style="margin-top: 2em; padding: 2em;">
    <h3>여기에 단계별 내용을 표시합니다.</h3>
  </div>
</div>

<script>
  const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

  // 제출 버튼 클릭 시 처리할 함수
  function submitAnswer(index, problemId) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    console.log(`정답 (index ${index}):`, correct);

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        updateFeedback(index, true);

        const nextIndex = index + 1;
        const nextTa = document.getElementById(`ta_${nextIndex}`);
        const nextBtn = document.getElementById(`btn_${nextIndex}`);
        if (nextTa && nextBtn) {
            nextTa.disabled = false;
            nextBtn.disabled = false;
            nextTa.focus();
            nextTa.addEventListener('input', () => autoResize(nextTa));
        }

        // 문제 ID와 사용자 입력값을 서버로 제출
        submitToServer(problemId, input);
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
        updateFeedback(index, false);
    }
  }

  function updateFeedback(index, isCorrect) {
    const panel = document.getElementById('feedback-panel');
    const existing = document.getElementById(`feedback_${index}`);
    const result = isCorrect ? "✔️ 정답" : "❌ 오답";
    const line = `<div id="feedback_${index}" class="feedback-line ${isCorrect ? 'feedback-correct' : 'feedback-wrong'}">Line ${index + 1}: ${result}</div>`;
    if (existing) existing.outerHTML = line;
    else panel.insertAdjacentHTML('beforeend', line);
  }

  function submitToServer(problemId, userInput) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'submit_answer.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
      if (xhr.status === 200) {
        alert('제출되었습니다!');
      } else {
        alert('서버에 문제가 발생했습니다.');
      }
    };
    xhr.send('user_input=' + encodeURIComponent(userInput) + '&problem_id=' + encodeURIComponent(problemId));
  }

  function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
  }

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
  });
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
