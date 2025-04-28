<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout" style="display: flex; justify-content: space-between;">
    <!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
    <div class="left-panel" style="flex: 1; padding-right: 10px;">
        <?php
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
            
                        // 태그라인 무시
                        if ($raw === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                            continue;
                        }
            
                        $line = htmlspecialchars($block['content']);
                        if (strpos($line, '[start]') !== false && strpos($line, '[end]') !== false) {
                            $line = preg_replace('/\[(.*?)\]/', '', $line);  // 태그 제거
                            $line = trim($line);
                        }

                        // 정답 코드가 존재하는지 먼저 확인
                        $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);
            
                        $disabled = $has_correct_answer ? "" : "disabled";
            
                        // 가이드라인 설명 및 코드 입력 영역
                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<div style='flex: 1'>";
                        $html .= "<div class='code-line'>{$line}</div>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
                        if ($has_correct_answer) {
                            $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                            $html .= "<button onclick='showAnswer({$answer_index})' id='view_answer_btn_{$answer_index}' class='view-button'>답안 확인</button>";
                            $html .= "<button onclick='showFeedback({$answer_index})' id='view_feedback_btn_{$answer_index}' class='view-button'>피드백 확인</button>";
                        }
                        $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                        $html .= "</div></div>";
            
                        $answer_index++;
                    }
                }
            
                return $html;
            }

            // 주어진 코드를 파싱하여 문제와 설명을 출력
            $answer_index = 0;
            echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <!-- 오른쪽 패널: 정답확인 및 피드백 확인 영역 -->
    <div class="right-panel" id="feedback-panel" style="width: 300px; max-width: 300px; min-width: 250px; overflow-y: auto; padding-left: 10px;">
        <h4>📝 정답 확인 및 피드백</h4>
        <!-- 정답과 피드백이 이 곳에 표시됩니다 -->
        <div id="answer_section" style="display:none;">
            <h5>정답:</h5>
            <pre id="answer_code"></pre>
        </div>
        <div id="feedback_section" style="display:none;">
            <h5>피드백:</h5>
            <div id="feedback_content"></div>
        </div>
    </div>
</div>


<script>
// 정답 코드 및 피드백 확인 함수
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>; // 정답 코드 배열 (PHP에서 제공)

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);

    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    // 사용자가 제출한 코드와 정답 코드 비교
    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#d4edda";  // 연한 초록색 배경
        ta.style.border = "1px solid #d4edda";  // 연한 초록색 테두리
        ta.style.color = "#155724";             // ✅ 진한 초록색 글자 추가
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

// 정답 보기
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return; // 정답 없으면 리턴

    const answerSection = document.getElementById('answer_section');
    const feedbackSection = document.getElementById('feedback_section');
    
    document.getElementById('answer_code').textContent = correctCode;

    // 정답 섹션을 보이게 하고, 피드백 섹션은 숨기기
    answerSection.style.display = 'block';
    feedbackSection.style.display = 'none';
}

// 피드백 보기
function showFeedback(index) {
    const feedbackContent = `이 코드는 정답을 제출했을 때 발생하는 피드백입니다.`; // 피드백을 실제 로직에 맞게 채워주세요.

    const answerSection = document.getElementById('answer_section');
    const feedbackSection = document.getElementById('feedback_section');
    
    document.getElementById('feedback_content').textContent = feedbackContent;

    // 피드백 섹션을 보이게 하고, 정답 섹션은 숨기기
    feedbackSection.style.display = 'block';
    answerSection.style.display = 'none';
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
