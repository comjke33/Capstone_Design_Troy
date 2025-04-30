<?php include("template/syzoj/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content">
        <!-- 여기에 동적으로 guideline1/2/3.php의 결과가 삽입됩니다 -->
    </div>
</div>

<script>
// JS 코드에서 fetch를 사용하여 PHP 파일로부터 데이터를 가져옵니다.
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    // 파일 로딩 함수 (step에 해당하는 guideline1.php, guideline2.php, guideline3.php를 불러옴)
    function loadStep(step) {
        fetch(`guideline1.php?step=${step}`)  // PHP 파일에서 데이터를 가져옵니다.
            .then(res => res.text())  // HTML 형식으로 응답을 받습니다.
            .then(data => {
                content.innerHTML = data;  // 반환된 content를 HTML에 삽입

                // 동적 기능을 다시 초기화하거나 실행
                initDynamicFeatures(); // 동적 기능 초기화 함수 호출

                window.history.pushState(null, "", `?step=${step}`);  // URL에 step 파라미터 추가
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                console.error("가이드라인 로딩 오류:", error);
            });
    }

    // 버튼 클릭 이벤트
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active")); // 기존 활성화된 버튼 비활성화
            btn.classList.add("active"); // 클릭된 버튼을 활성화

            const step = btn.dataset.step; // 클릭된 버튼의 data-step 값
            loadStep(step); // 해당 step에 맞는 가이드라인 로드
        });
    });

    // URL에 step이 이미 있으면 그 값을 사용하여 초기화, 없으면 기본 1로 설정
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

// 동적 기능을 처리하는 함수 (예: 텍스트 영역 크기 자동 조정)
function initDynamicFeatures() {
    const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>; // 정답 코드 배열 (PHP에서 제공)

    // 동적으로 버튼에 이벤트 리스너 추가
    document.querySelectorAll('.submit-button').forEach((btn, index) => {
        btn.addEventListener("click", function () {
            submitAnswer(index);
        });
    });

    document.querySelectorAll('.view-button').forEach((btn, index) => {
        btn.addEventListener("click", function () {
            showAnswer(index);
        });
    });

    // 제출 함수
    function submitAnswer(index) {
        const ta = document.getElementById(`ta_${index}`);
        const btn = document.getElementById(`btn_${index}`);
        const check = document.getElementById(`check_${index}`);

        const input = ta.value.trim();
        const correct = (correctAnswers[index]?.content || "").trim();

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

    // 답안 확인 함수
    function showAnswer(index) {
        const correctCode = correctAnswers[index]?.content.trim();
        if (!correctCode) return; // 정답 없으면 리턴

        const answerArea = document.getElementById(`answer_area_${index}`);
        const answerHtml = `
            <strong>정답:</strong><br>
            <pre class='code-line'>${correctCode}</pre>
        `;

        answerArea.innerHTML = answerHtml;
        answerArea.style.display = 'block';
    }

    // 텍스트 영역 크기 자동 조정 함수
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
}
</script>

<!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
<div class="left-panel" style="flex: 0.2; padding-right: 10px; overflow-y: hidden; position: relative; display: flex; justify-content: center; align-items: center;">
    <!-- 슬라이더를 추가해서 이미지를 마우스 커서의 Y 위치에 따라 위 아래로 움직이게 하기 -->
    <div id="slider-container" style="position: absolute; height: 100%; width: 100px; overflow-y: hidden;">
        <img src="/image/feedback.jpg" alt="Feedback" id="feedback-img" 
             style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
    </div>
</div>

<!-- 가운데 패널: 문제 설명 및 텍스트 입력 영역 -->
<div class="center-panel" style="flex-grow: 1; padding: 20px; overflow-y: auto;">
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

<!-- 오른쪽 패널: 피드백 부분 -->
<div class="right-panel" style="flex: 0.3; padding-left: 20px; border-left: 1px solid #ddd; height: 100vh;">
    <h3>피드백 부분</h3>
    <div class="feedback-content" style="padding: 20px; background-color: #f9f9f9; height: calc(100% - 40px);">
        <!-- 피드백 내용이 여기에 표시됩니다. -->
    </div>
</div>

<!-- js 불러오기 -->
<script src="/template/syzoj/js/guideline.js"></script>

<script>
    // 마우스 커서에 Y 위치에 따라 이미지를 실시간으로 위아래로만 움직이게 하는 기능
    document.getElementById('slider-container').addEventListener('mousemove', function(event) {
        const feedbackImage = document.getElementById('feedback-img');
        const container = document.getElementById('slider-container');
        
        // 이미지의 이동 범위 제한
        const containerHeight = container.clientHeight;
        const scrollPosition = event.clientY; // 마우스 위치
        const imageTop = Math.min(containerHeight - feedbackImage.height, Math.max(0, scrollPosition - 20)); // 위치 제한

        feedbackImage.style.top = `${imageTop}px`;  // 이미지의 Y 위치를 마우스 위치에 맞게 변경
    });
</script>

<?php include("template/syzoj/footer.php"); ?>
