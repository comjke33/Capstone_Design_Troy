<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    // ✅ 설명들
    $guidelines = array_filter($GLOBALS['OJ_BLOCK_TREE'], function($line) {
        $trimmed = trim($line);
        return $trimmed !== '' && !preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $trimmed);
    });

    // ✅ 코드 블록 쪼개기
    $raw_codes = $GLOBALS['OJ_CORRECT_ANSWERS'] ?? [];
    $blocks = [];
    $current = "";

    foreach ($raw_codes as $entry) {
        $line = trim($entry['content']);
        if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]$/", $line)) {
            $current = "";
        } elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\(\d+\)\]$/", $line)) {
            $blocks[] = trim($current);
        } else {
            $line = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $line);
            $current .= $line . "\n";
        }
    }

    $guidelines = array_values($guidelines);
    $blocks = array_values($blocks);

    // ✅ 출력
    $correctAnswerList = [];
    for ($i = 0; $i < min(count($guidelines), count($blocks)); $i++) {
        $desc = htmlspecialchars($guidelines[$i]);
        $code = htmlspecialchars(trim($blocks[$i]));
        $correctAnswerList[$i] = trim($blocks[$i]);

        echo "<div class='code-line'>{$desc}</div>";
        echo "<div class='submission-line'>";
        echo "<div style='flex: 1'>";
        echo "<textarea id='ta_{$i}' class='styled-textarea' data-index='{$i}'>".$code."</textarea>";
        echo "<button onclick='submitAnswer({$i})' id='btn_{$i}' class='submit-button'>제출</button>";
        echo "<span id='check_{$i}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
        echo "<span id='wrong_{$i}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
        echo "</div></div>";
    }
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
    </div>
</div>

<script>
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
