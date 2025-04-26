function initializeGuideline(correctAnswers) {
  document.querySelectorAll('.styled-textarea').forEach(ta => {
      if (!ta.disabled) {
          ta.addEventListener('input', () => autoResize(ta));
      }
  });

  window.submitAnswer = function(index) {
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

  function autoResize(ta) {
      ta.style.height = 'auto';
      ta.style.height = ta.scrollHeight + 'px';
  }
}
