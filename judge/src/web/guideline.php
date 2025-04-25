<?php
// 헤더 및 DB 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 필요한 텍스트 파일 로드
$file_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
$guideline_contents = file_get_contents($file_path);

$txt_path = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);

// 데이터를 분석하는 함수 (guideline1.php와 동일한 함수)
function parse_blocks_with_loose_text($text, $depth = 0) {
    // ... (기존 코드 유지)
}

function extract_tagged_code_lines($text) {
    // ... (기존 코드 유지)
}

$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
$OJ_BLOCK_TREE = parse_blocks_with_loose_text($guideline_contents);
$OJ_CORRECT_ANSWERS = extract_tagged_code_lines($txt_contents);
$OJ_SID = $sid;

?>

<!-- guideline.php의 내용 -->
<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>
    문제 번호: <?= htmlspecialchars($OJ_SID) ?>
</div>

<!-- 스타일 및 기능 -->
<style>
    .main-layout { display: flex; gap: 40px; max-width: 1200px; margin: 0 auto; }
    .left-panel { flex: 2; }
    .right-panel { flex: 1; padding: 16px; background-color: #fafafa; border: 1px solid #eee; border-radius: 8px; font-family: monospace; }
    .code-line { background-color: #f8f8fa; border: 1px solid #ddd; border-radius: 6px; padding: 10px 16px; margin-bottom: 10px; font-size: 15px; color: #333; white-space: pre-wrap; }
    .styled-textarea { border: 1px solid #ccc; border-radius: 6px; padding: 10px 14px; font-family: monospace; font-size: 15px; background-color: #fff; line-height: 1.6; resize: none; width: 100%; box-sizing: border-box; min-height: 40px; }
    .submit-button { margin-top: 6px; background-color: #4a90e2; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
    .checkmark { font-size: 18px; margin-left: 6px; color: #2ecc71; }
    .submission-line { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 28px; }
    .feedback-line { margin-bottom: 12px; font-size: 15px; }
    .feedback-correct { color: #2ecc71; }
    .feedback-wrong { color: #e74c3c; }
</style>

<div class="main-layout">
    <div class="left-panel">
        <?php
        function render_tree_plain($blocks, &$answer_index = 0) {
            // ... (기존 코드 유지)
        }

        $answer_index = 0;
        echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <div class="right-panel" id="feedback-panel">
        <h4>📝 피드백</h4>
    </div>
</div>

<!-- JavaScript 코드 (guideline1.php에서 사용된 함수들) -->
<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

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

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
