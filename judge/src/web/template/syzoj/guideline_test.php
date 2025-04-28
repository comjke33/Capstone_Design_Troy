<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한 문단씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<!-- <link rel="stylesheet" href="/template/syzoj/css/guideline.css"> -->

<div class="main-layout" style="display: flex; justify-content: space-between;">
    <!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
    <div class="left-panel" style="flex: 1; padding-right: 10px;">
        <?php
            // 1. 태그들을 파싱해서 필요한 내용만 출력하는 함수
            function render_tree_plain($blocks, &$answer_index = 0) {
                $html = "";

                foreach ($blocks as $block) {
                    $indent_px = 10 * ($block['depth'] ?? 0);

                    if (isset($block['children'])) {
                        $html .= "<div class='block-wrap block-{$block['type']}' style='margin-left: {$indent_px}px;'>";
                        $html .= render_tree_plain($block['children'], $answer_index);
                        $html .= "</div>";
                    } elseif ($block['type'] === 'text') {
                        $raw = trim($block['content']);

                        // 불필요한 태그를 포함한 코드를 제거 (예: [self_start]와 [self_end] 사이의 내용만 추출)
                        if ($raw === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                            continue;
                        }

                        // [self_start]와 [self_end] 사이의 내용만 출력
                        $line = htmlspecialchars($block['content']);
                        if (strpos($line, '[self_start]') !== false && strpos($line, '[self_end]') !== false) {
                            // [self_start]와 [self_end] 사이의 내용을 출력
                            $line = preg_replace('/\[(.*?)\]/', '', $line);  // 태그 제거
                            $line = trim($line);  // 양옆 공백 제거
                        }

                        $correct_code = htmlspecialchars($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'] ?? '');
                        $disabled = $answer_index > 0 ? "disabled" : "";

                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<div style='flex: 1'>";
                        $html .= "<div class='code-line'>{$line}</div>";
                        // 정답은 비워두기
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                        $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button' {$disabled}>답안 확인</button>"; // 답안 확인 버튼 추가
                        $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                        $html .= "</div></div>";

                        $answer_index++;
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
        <!-- 정답이 이 곳에 표시될 것입니다 -->
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
        updateFeedback(index, true, input);

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
        updateFeedback(index, false, input);
    }
}

// 답안 보기 버튼 클릭 시 오른쪽 패널에 정답을 표시
function showAnswer(index) {
    const panel = document.getElementById('feedback-panel');
    let answerHtml = "<h4>정답:</h4><div>";
    
    // 정답 코드 출력
    const correctCode = correctAnswers[index]?.content.trim();
    answerHtml += `<pre class='code-line'>${correctCode}</pre>`;
    answerHtml += "</div>";
    
    // 오른쪽 패널에 정답 추가
    panel.innerHTML = answerHtml;
}

function updateFeedback(index, isCorrect, inputCode) {
    const panel = document.getElementById('feedback-panel');
    const existing = document.getElementById(`feedback_${index}`);
    const result = isCorrect ? "✔️ 정답" : "❌ 오답";
    const feedbackLine = `
        <div id="feedback_${index}" class="feedback-line ${isCorrect ? 'feedback-correct' : 'feedback-wrong'}">
            <strong>Line ${index + 1}:</strong> ${result}<br>
            <strong>제출 코드:</strong><pre>${inputCode}</pre>
        </div>
    `;
    if (existing) existing.outerHTML = feedbackLine;
    else panel.insertAdjacentHTML('beforeend', feedbackLine);
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
