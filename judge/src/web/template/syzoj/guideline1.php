<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    $guideline_lines = $GLOBALS['OJ_BLOCK_TREE']; // 설명 줄들
    $codes = $GLOBALS['OJ_CORRECT_ANSWERS'];       // 코드 줄들

    $guideline_index = 0;
    $code_index = 0;
    $code_blocks = [];

    // 코드 블럭 재구성
    $current_block = '';

    foreach ($codes as $line) {
        $content = trim($line['content']);
        if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]$/", $content)) {
            $current_block = '';
        } elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\(\d+\)\]$/", $content)) {
            $code_blocks[] = trim($current_block);
        } else {
            $clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $content);
            $current_block .= $clean . "\n";
        }
    }

    $output_index = 0;
    $correctAnswerList = [];

    while ($guideline_index < count($guideline_lines) && $output_index < count($code_blocks)) {
        $desc = trim($guideline_lines[$guideline_index]);
        $guideline_index++;

        if ($desc === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $desc)) {
            continue;
        }

        // ✏️ 문제 설명 출력
        echo "<div class='code-line'>".htmlspecialchars($desc)."</div>";

        // ✏️ 코드 입력창 출력
        $code = trim($code_blocks[$output_index]);
        $correctAnswerList[$output_index] = $code; // 저장
        $escaped_code = htmlspecialchars($code);

        echo "<div class='submission-line'>";
        echo "<div style='flex: 1'>";
        echo "<textarea id='ta_{$output_index}' class='styled-textarea' data-index='{$output_index}'>".$escaped_code."</textarea>";
        echo "<button onclick='submitAnswer({$output_index})' id='btn_{$output_index}' class='submit-button'>제출</button>";
        echo "<span id='check_{$output_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
        echo "<span id='wrong_{$output_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
        echo "</div></div>";

        $output_index++;
    }
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
    </div>
</div>

<script>
// 🧩 정답 배열
const correctAnswers = <?= json_encode($correctAnswerList, JSON_UNESCAPED_UNICODE) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index] || "").trim();

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
