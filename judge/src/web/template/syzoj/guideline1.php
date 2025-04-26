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
        $guideline_index = 0;
        $code_index = 0;
        $guideline_count = count($guidelines);
        $code_count = count($codes);

        while ($guideline_index < $guideline_count && $code_index < $code_count) {
            // 1. 설명 출력
            $desc = trim($guidelines[$guideline_index]['content'] ?? '');
            $guideline_index++;

            if ($desc === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $desc)) {
                continue; // 빈 줄, 태그 무시
            }

            $html .= "<div class='code-line'>" . htmlspecialchars($desc) . "</div>";

            // 2. 코드 묶음 출력
            $code_block = "";
            while ($code_index < $code_count) {
                $line = $codes[$code_index]['content'] ?? '';
                $line_clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $line);
                $line_clean = trim($line_clean);

                if ($line_clean !== '') {
                    $code_block .= $line_clean . "\n";
                }

                // 만약 다음 코드가 [xxx_start(n)] 이면 멈춤 (하나의 블럭 끝)
                if (isset($codes[$code_index + 1]['content']) && 
                    preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]$/", trim($codes[$code_index + 1]['content']))) {
                    $code_index++;
                    break;
                }

                $code_index++;
            }

            $code_block = trim($code_block);

            $ta_id = $guideline_index - 1; // textarea id는 설명 인덱스 기준
            $html .= "<div class='submission-line'>";
            $html .= "<div style='flex: 1'>";
            $html .= "<textarea id='ta_{$ta_id}' class='styled-textarea' data-index='{$ta_id}'>" . htmlspecialchars($code_block) . "</textarea>";
            $html .= "<button onclick='submitAnswer({$ta_id})' id='btn_{$ta_id}' class='submit-button'>제출</button>";
            $html .= "<span id='check_{$ta_id}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
            $html .= "<span id='wrong_{$ta_id}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
            $html .= "</div></div>";
        }

        echo $html;
    }

    render_guideline_and_code($OJ_BLOCK_TREE, $OJ_CORRECT_ANSWERS);
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
    </div>
</div>

<script>
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const wrong = document.getElementById(`wrong_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#eef1f4";
        if (btn) btn.style.display = "none";
        if (check) check.style.display = "inline";
        if (wrong) wrong.style.display = "none";
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
