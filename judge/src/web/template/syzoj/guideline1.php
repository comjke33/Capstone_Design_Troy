<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    // 📦 가이드라인 한 줄씩
    $guideline_lines = $GLOBALS['OJ_BLOCK_TREE']; // 예: ['main 함수입니다.', 'r, c 변수 선언', ...]

    // 📦 코드 블록 (tagged_code 파싱)
    $code_blocks = [];
    $current_block = '';

    foreach ($GLOBALS['OJ_CORRECT_ANSWERS'] as $entry) {
        $content = trim($entry['content']);
        if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]$/", $content)) {
            $current_block = ''; // 새 블럭 시작
        } elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\(\d+\)\]$/", $content)) {
            $code_blocks[] = trim($current_block); // 블럭 저장
        } else {
            $clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $content);
            $current_block .= $clean . "\n"; // 코드 누적
        }
    }

    // 📦 출력
    $guideline_index = 0;
    $code_index = 0;

    while ($guideline_index < count($guideline_lines) && $code_index < count($code_blocks)) {
        $desc = trim($guideline_lines[$guideline_index]);
        $guideline_index++;

        // 설명이 빈 줄이거나 태그면 스킵
        if ($desc === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $desc)) {
            continue;
        }

        // ✏️ 설명 출력
        echo "<div class='code-line'>".htmlspecialchars($desc)."</div>";

        // ✏️ 코드 블럭 출력
        $code = trim($code_blocks[$code_index]);
        $code_index++;

        echo "<div class='submission-line'>";
        echo "<div style='flex: 1'>";
        echo "<textarea id='ta_{$code_index}' class='styled-textarea' data-index='{$code_index}'>".htmlspecialchars($code)."</textarea>";
        echo "<button onclick='submitAnswer({$code_index})' id='btn_{$code_index}' class='submit-button'>제출</button>";
        echo "<span id='check_{$code_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
        echo "<span id='wrong_{$code_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
        echo "</div></div>";
    }
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
    </div>
</div>

<script>
const correctAnswers = <?= json_encode(array_map(function($block) {
    return trim($block);
}, $code_blocks)) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index - 1] || "").trim(); // index - 1 맞춰줌

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        if (wrong) wrong.style.display = "none";

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
        if (wrong) wrong.style.display = "inline";
    }
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
