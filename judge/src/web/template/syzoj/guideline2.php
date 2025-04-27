<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 문단씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout" style="display: flex; justify-content: space-between;">
    <!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
    <div class="left-panel" style="flex: 1; padding-right: 10px;">
        <?php
            // 1. 태그들을 파싱해서 필요한 내용만 출력하는 함수
            function render_tree_plain($blocks, &$answer_index = 0) {
                $html = "";

                foreach ($blocks as $block) {
                    $indent_px = 10 * ($block['depth'] ?? 0);

                    // 자식 블록이 있는 경우 재귀적으로 처리
                    if (isset($block['children'])) {
                        $html .= "<div class='block-wrap block-{$block['type']}' style='margin-left: {$indent_px}px;'>";
                        $html .= render_tree_plain($block['children'], $answer_index);
                        $html .= "</div>";
                    } elseif ($block['type'] === 'text') {
                        $raw = trim($block['content']);

                        // 태그가 포함되지 않은 내용 출력
                        if ($raw !== '' && !preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                            // 일반적인 텍스트 내용 출력
                            $html .= "<div class='problem-description'>{$raw}</div>";
                        }
                    } elseif ($block['type'] === 'code') {
                        // 태그 사이의 코드만 추출
                        $line = htmlspecialchars($block['content']);
                        $line = preg_replace('/\[\s*(func_def|rep|cond|self|struct|construct)_[a-zA-Z0-9_]+\(\d+\)\s*\]/', '', $line); // 태그 제거

                        // 태그 사이의 코드 추출 (모든 태그에 대해 처리)
                        $pattern = '/\[(func_def|rep|cond|self|struct|construct)_[a-zA-Z0-9_]+\(\d+\)\](.*?)\[\s*\1_end\(\d+\)\]/s';
                        preg_match_all($pattern, $line, $matches);

                        // 태그 안의 내용만 출력 (matches[2]는 실제 코드 내용)
                        foreach ($matches[2] as $code_content) {
                            $code_content = htmlspecialchars(trim($code_content)); // 내용만 출력, 불필요한 공백 제거
                            $correct_code = htmlspecialchars($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '');
                            $disabled = $answer_index > 0 ? "disabled" : "";

                            $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                            $html .= "<div style='flex: 1'>";
                            $html .= "<div class='code-line'>{$code_content}</div>";
                            $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$correct_code}</textarea>";
                            $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                            $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                            $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                            $html .= "</div></div>";

                            $answer_index++;
                        }
                    }
                }

                return $html;
            }

            $answer_index = 0;
            echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <!-- 오른쪽 패널: 정답확인 영역 -->
    <div class="right-panel" id="feedback-panel" style="width: 300px; max-width: 300px; min-width: 250px; overflow-y: auto; padding-left: 10px;">
        <h4>📝 정답 확인</h4>
        <?php
            // 태그 제거된 코드 내용만 오른쪽 패널에 출력하기
            function render_right_panel($blocks) {
                $output = "<div class='code-blocks'>";
                foreach ($blocks as $block) {
                    if ($block['type'] === 'text') {
                        continue; // 텍스트는 제외
                    } elseif ($block['type'] === 'code') {
                        $line = $block['content'];
                        $line = preg_replace('/\[\s*(func_def|rep|cond|self|struct|construct)_[a-zA-Z0-9_]+\(\d+\)\s*\]/', '', $line); // 태그 제거
                        $output .= "<pre class='code-line'>{$line}</pre>";
                    }
                }
                $output .= "</div>";
                return $output;
            }

            echo render_right_panel($OJ_BLOCK_TREE);
        ?>
    </div>
</div>

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
        btn.style.display = "none";
        check.style.display = "inline";
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
