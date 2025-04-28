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
            function parse_blocks_with_loose_text($text, $depth = 0) {
                // 패턴 수정: start와 end가 뒤죽박죽일 경우에도 처리할 수 있도록
                $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/s";
                
                $blocks = [];
                $offset = 0;
            
                // 정규식으로 태그 및 그 사이의 내용을 찾는다.
                while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
                    $start_pos = $m[0][1];
                    $full_len = strlen($m[0][0]);
                    $end_pos = $start_pos + $full_len;
            
                    // start와 end 사이의 텍스트를 처리
                    $content = $m[3][0]; // 태그 사이에 있는 내용
                    
                    // 재귀적으로 처리하여 중첩된 블록을 파싱
                    $children = parse_blocks_with_loose_text($content, $depth + 1);
                    
                    // 시작과 종료 태그에 대한 메타정보 추가
                    $type = $m[1][0];
                    $index = $m[3][0];
                    array_unshift($children, [
                        'type' => 'text',
                        'content' => "[{$type}_start({$index})]",
                        'depth' => $depth + 1
                    ]);
                    array_push($children, [
                        'type' => 'text',
                        'content' => "[{$type}_end({$index})]",
                        'depth' => $depth + 1
                    ]);
                    
                    // 블록 추가
                    $blocks[] = [
                        'type' => $type,
                        'index' => $index,
                        'depth' => $depth,
                        'children' => $children
                    ];
                    
                    $offset = $end_pos; // 다음 검색을 위한 오프셋 업데이트
                }
            
                // 마지막까지 읽은 부분 처리
                $tail = substr($text, $offset);
                if (trim($tail) !== '') {
                    foreach (explode("\n", $tail) as $line) {
                        $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
                        $blocks[] = [
                            'type' => 'text',
                            'content' => rtrim($line),
                            'depth' => $depth + $indent_level
                        ];
                    }
                }
            
                return $blocks;
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
