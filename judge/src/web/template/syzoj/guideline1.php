<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    function render_guideline_and_code($guidelines, $codes) {
        $html = "";
        $answer_index = 0;
        $guideline_index = 0;
        $code_count = count($codes);

        while ($guideline_index < count($guidelines)) {
            $desc = trim($guidelines[$guideline_index]['content'] ?? '');
            $guideline_index++;

            if ($desc === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $desc)) {
                continue;
            }

            // 설명 출력
            $html .= "<div class='code-line'>".htmlspecialchars($desc)."</div>";

            // 코드 1줄 출력
            while ($answer_index < $code_count) {
                $content = trim($codes[$answer_index]['content'] ?? '');
                $answer_index++;

                // 태그([start]/[end])는 스킵
                if ($content === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $content)) {
                    continue;
                }

                $code_clean = htmlspecialchars($content);

                $html .= "<div class='submission-line'>";
                $html .= "<div style='flex: 1'>";
                $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}'>{$code_clean}</textarea>";
                $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
                $html .= "<span id='wrong_{$answer_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
                $html .= "</div></div>";
                break; // ✅ 코드 1줄 매칭했으면 다음 guideline으로
            }
        }

        echo $html;
    }

    render_guideline_and_code($OJ_BLOCK_TREE, $OJ_CORRECT_ANSWERS);
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;"></div>
</div>

<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index-1]?.content || "").trim();  // index -1 주의

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        if (wrong) wrong.style.display = "none";

        const nextIndex = index;
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
