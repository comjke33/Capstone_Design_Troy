<?php
// 📦 파일 읽기 (guideline 설명과 tagged code)
$guideline_lines = explode("\n", trim(file_get_contents($GLOBALS['guideline_file'])));
$tagged_code_lines_raw = explode("\n", trim(file_get_contents($GLOBALS['tagged_file'])));

// 📦 tagged_code에서 태그 제거 (진짜 코드라인만 남김)
$tagged_code_lines = [];
foreach ($tagged_code_lines_raw as $line) {
    $clean = trim($line);
    if ($clean === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\]\$/", $clean)) {
        continue; // 태그라인은 스킵
    }
    $tagged_code_lines[] = $clean;
}

// 📦 줄 수 맞는지 확인
if (count($guideline_lines) !== count($tagged_code_lines)) {
    echo "<div style='color:red'>❌ 줄 수가 다릅니다. guideline 줄수: ".count($guideline_lines).", tagged_code 줄수: ".count($tagged_code_lines)."</div>";
    exit;
}

?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    foreach ($guideline_lines as $i => $guideline_line) {
        $description = htmlspecialchars(trim($guideline_line));
        $code_line = htmlspecialchars(trim($tagged_code_lines[$i]));
        $disabled = $i > 0 ? "disabled" : "";
        echo "<div class='code-line'>{$description}</div>";
        echo "<div class='submission-line'>";
        echo "<div style='flex:1'>";
        echo "<textarea id='ta_{$i}' class='styled-textarea' data-index='{$i}' {$disabled}>{$code_line}</textarea>";
        echo "<button onclick='submitAnswer({$i})' id='btn_{$i}' class='submit-button' {$disabled}>제출</button>";
        echo "<span id='check_{$i}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
        echo "<span id='wrong_{$i}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
        echo "</div></div>";
    }
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
        <!-- 오른쪽 패널 비워둠 -->
    </div>
</div>

<script>
const correctAnswers = <?= json_encode($tagged_code_lines) ?>;

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

// 초기화
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.styled-textarea').forEach(ta => {
        if (!ta.disabled) {
            ta.addEventListener('input', () => autoResize(ta));
        }
    });
});
</script>
