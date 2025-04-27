<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <div class="left-panel">
        <?php
        // 1. 설명 (guideline) 준비
        function extract_guidelines($tree) {
            $guidelines = [];
            foreach ($tree as $block) {
                if ($block['type'] == 'text') {
                    $text = trim($block['content']);
                    if ($text !== '' && !preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $text)) {
                        $guidelines[] = $text;
                    }
                }
                if (isset($block['children'])) {
                    $guidelines = array_merge($guidelines, extract_guidelines($block['children']));
                }
            }
            return $guidelines;
        }
        $guidelines = extract_guidelines($OJ_BLOCK_TREE);

        // 2. 코드 블럭 준비
        function extract_code_blocks($codes) {
            $blocks = [];
            $current_block = [];
            foreach ($codes as $entry) {
                $line = trim($entry['content']);
                if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]$/", $line)) {
                    $current_block = [];
                } elseif (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_end\(\d+\)\]$/", $line)) {
                    if (!empty($current_block)) {
                        $blocks[] = implode("\n", $current_block);
                    }
                } else {
                    $line = preg_replace("/\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]/", "", $line);
                    $current_block[] = $line;
                }
            }
            return $blocks;
        }
        $code_blocks = extract_code_blocks($OJ_CORRECT_ANSWERS);

        // 3. 출력
        $count = min(count($guidelines), count($code_blocks));
        for ($i = 0; $i < $count; $i++) {
            $desc = htmlspecialchars($guidelines[$i]);
            $code = htmlspecialchars($code_blocks[$i]);
            echo "<div class='code-line'>{$desc}</div>";
            echo "<div class='submission-line'>";
            echo "<div style='flex: 1'>";
            echo "<textarea id='ta_{$i}' class='styled-textarea' data-index='{$i}'>{$code}</textarea>";
            echo "<button onclick='submitAnswer({$i})' id='btn_{$i}' class='submit-button'>제출</button>";
            echo "<span id='check_{$i}' class='checkmark' style='display:none; margin-left:10px;'>✔️</span>";
            echo "<span id='wrong_{$i}' class='wrongmark' style='display:none; margin-left:10px; color:#e74c3c;'>❌</span>";
            echo "</div></div>";
        }
        ?>
    </div>

    <!-- 오른쪽 피드백 패널은 삭제되었습니다. -->

</div>

<script>
const correctAnswers = <?= json_encode($code_blocks, JSON_UNESCAPED_UNICODE) ?>;

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
