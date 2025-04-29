<?php include("template/$OJ_TEMPLATE/header.php");?>

<div class="ui container" style="margin-top: 3em;">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content">
        <!-- 여기에 동적으로 guideline1/2/3.php의 결과가 삽입됩니다 -->
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
                                    $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>";
                                }
                                // 정답이 표시될 공간 추가 (textarea와 제출 버튼 사이)
                                $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
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
        </div>
    </div>
</div>

<script>
// 정답 확인 및 제출 기능
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

// 답안 확인 버튼 클릭 시 정답을 textarea 아래에 표시
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return; // 정답 없으면 리턴

    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `
        <strong>정답:</strong><br>
        <pre class='code-line'>${correctCode}</pre>
    `;

    // 정답을 표시하고, 표시된 영역을 보이도록 설정
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';  // 정답을 보이도록 설정
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

// Step 버튼 클릭 시 가이드라인 파일 동적 로딩
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    function loadStep(step) {
        fetch(`guideline${step}.php`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html; // 가이드라인 내용을 삽입
                window.history.pushState(null, "", `?step=${step}`); // URL에 step 파라미터 추가
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
            });
    }

    // 버튼 클릭 이벤트
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active")); // 모든 버튼에서 active 제거
            btn.classList.add("active"); // 클릭된 버튼에 active 추가

            const step = btn.dataset.step;
            loadStep(step); // 해당 step 로드
        });
    });

    // URL에 step이 이미 있으면 그걸 로딩, 아니면 기본 1로
    const urlParams = new URLSearchParams(window.location.search);
    const initialStep = urlParams.get('step') || 1;
    loadStep(initialStep); // 초기 step 로드

    // 버튼 활성화도 초기 상태 반영
    buttons.forEach(btn => {
        if (btn.dataset.step == initialStep) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
});
</script>


<?php include("template/$OJ_TEMPLATE/footer.php");?>