<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
    <?php
    // 🔥 새로운 함수: block 트리에서 순수 텍스트만 추출
    function extract_guidelines($blocks) {
        $guidelines = [];

        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                $raw = trim($block['content']);
                if ($raw !== '' && !preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                    $guidelines[] = $raw;
                }
            }
            if (isset($block['children']) && is_array($block['children'])) {
                $guidelines = array_merge($guidelines, extract_guidelines($block['children']));
            }
        }

        return $guidelines;
    }

    // 🔥 실제 출력
    function render_guideline_and_code($guidelines, $codes) {
        $guideline_index = 0;
        $code_index = 0;
        $guideline_count = count($guidelines);
        $code_count = count($codes);

        while ($guideline_index < $guideline_count && $code_index < $code_count) {
            $desc = trim($guidelines[$guideline_index]);

            // 설명 출력
            echo "<div class='code-line'>" . htmlspecialchars($desc) . "</div>";

            // 코드 블럭 출력
            $code_content = $codes[$code_index]['content'] ?? '';
            $code_clean = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $code_content);
            $code_clean = htmlspecialchars(trim($code_clean));

            echo "<div class='submission-line'>";
            echo "<div style='flex: 1'>";
            echo "<textarea id='ta_{$guideline_index}' class='styled-textarea' data-index='{$guideline_index}'>".$code_clean."</textarea>";
            echo "<button onclick='submitAnswer({$guideline_index})' id='btn_{$guideline_index}' class='submit-button'>제출</button>";
            echo "<span id='check_{$guideline_index}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
            echo "<span id='wrong_{$guideline_index}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
            echo "</div></div>";

            $guideline_index++;
            $code_index++;
        }
    }

    // ✅ guideline 텍스트 추출
    $guidelines = extract_guidelines($OJ_BLOCK_TREE);

    // ✅ guideline과 code를 매칭 출력
    render_guideline_and_code($guidelines, $OJ_CORRECT_ANSWERS);
    ?>
    </div>

    <div class="right-panel" id="feedback-panel" style="height: 200px; overflow-y: auto;">
        <!-- 오른쪽 패널: 높이 고정 -->
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
