<?php
include("template/$OJ_TEMPLATE/header.php");
include("../../guideline_common.php");
?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'></div>
<link rel="stylesheet" href="/template/syzoj/css/guideline.css">


<!-- 상단 툴바 -->
<div class="top-toolbar">
  <!-- 뒤로가기 및 리셋 버튼 -->
  <div class="action-buttons">
        <div class="back-button">
            <button class="ui button back" id="view-problem-button">↩</button>
        </div>
  </div>
    
  <!-- Step1,2,3 buttons -->
  <div class="step-buttons">
    <button class="ui button" data-step="1" data-problem-id="<?= htmlspecialchars($problem_id) ?>">기초</button>
    <button class="ui button" data-step="2" data-problem-id="<?= htmlspecialchars($problem_id) ?>">실전</button>
    <button class="ui button" data-step="3" data-problem-id="<?= htmlspecialchars($problem_id) ?>">심화</button>
  </div>

  <div class="action-buttons">
    <div class="reset-button">
        <button class="ui button again" id="reset-button">↻</button>
    </div>
  </div>
</div>


<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
    <div class="flowchart-wrapper active" id="flowchart-wrapper">
        <div class="flowchart-title">Flowchart</div>
        <div class="flowchart-scroll">
        <img id="flowchart_image">
        </div>
    </div>
    </div>

    <!-- 가운데 패널 -->
<div class="center-panel">
    <h1>기초 풀기</h1>

    <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
    <br><br>

    <?php      
        function highlight_terms_with_tooltip($text) {
            $term_map = [
                "초기화" => "변수에 처음으로 값을 할당하여 유효한 상태로 만드는 작업입니다.",
                "선언"   => "변수나 함수를 처음 정의하는 과정입니다.",
                "변수"   => "데이터를 저장하는 이름 붙은 공간입니다.",
                "널"     => "값이 없음을 나타내는 특수 상수입니다. C 언어에서는 NULL로 표현됩니다.",
                "순회"   => "배열이나 리스트를 처음부터 끝까지 차례로 접근하는 작업입니다.",
                "인자"   => "함수 호출 시 괄호 안에 전달하는 실제 값입니다. 예: foo(5); 에서 5가 인자입니다.",
                "호출"   => "함수를 실행하는 동작입니다. 예: 함수이름(값); 형태입니다.",
                "매개 변수" => "함수 정의 시 괄호 안에 선언되는 변수로, 인자를 받아 처리합니다.",
                "삼항 연산자" => "조건에 따라 다른 값을 선택할 때 사용하는 연산자입니다. 형식: 조건 ? 값1 : 값2;",
                "인덱스" => "배열 요소를 가리키기 위한 번호입니다. C에서는 0부터 시작합니다."
            ];
        
            $placeholders = [];
            $i = 0;
        
            // 1단계: 특수 토큰으로 치환
            foreach ($term_map as $term => $desc) {
                $token = "__TERM_{$i}__";
                $placeholders[$token] = '<span class="term-tooltip" data-content="' . htmlspecialchars($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '">' . $term . '</span>';
        
                // 조사 포함도 매칭 (예: 순회를, 선언하고)
                $text = preg_replace('/' . preg_quote($term, '/') . '(?=[가-힣]{0,2})/u', $token, $text);
                $i++;
            }
        
            // 2단계: 전체 escape
            $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
            // 3단계: escape 후에 토큰을 실제 span 태그로 되돌림
            $final = strtr($escaped, $placeholders);
        
            return $final;
        }
        
        function render_tree_plain($blocks, &$answer_index = 0) {
        $html = "";
        foreach ($blocks as $block) {
            $depth = $block['depth'];
            $margin_left = $depth * 50;
            $isCorrect = false;

            if ($block['type'] === 'text') {
                $raw = trim($block['content']);
                if ($raw === '') continue;

                $html .= "<!-- DEBUG raw line [{$answer_index}]: " . htmlentities($raw) . " -->\n";
                $html .= "<script>console.log('Block index {$answer_index} - Depth: {$depth}');</script>";

                // 정답 가져오기
                $default_value = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index])
                    ? htmlspecialchars($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]['content'], ENT_QUOTES, 'UTF-8')
                    : '';

                $has_correct_answer = !empty($default_value);
                $disabled = $has_correct_answer ? "" : "disabled";
                $readonlyStyle = "background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;";
                $html .= "<div class='submission-line' style='margin-left: {$margin_left}px;'>";

                // ✅ Depth 1: 읽기 전용 정답 표시용 블록
                if ($depth === 1) {
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' readonly style='{$readonlyStyle}'>{$default_value}</textarea>";
                } else {
                    // 일반 입력 블록
                    //$escaped_line = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
                    //$escaped_line = highlight_terms_with_tooltip(htmlspecialchars($raw, ENT_QUOTES, 'UTF-8'));
                    $escaped_line = highlight_terms_with_tooltip($raw);
                    $html .= "<div class='code-line'>{$escaped_line}</div>";
                    $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";

                    if (!$isCorrect) {
                        $html .= "<button onclick='submitAnswer({$answer_index})' id='submit_btn_{$answer_index}' class='submit-button'>제출</button>";
                        $html .= "<button onclick='showAnswer({$answer_index})' id='answer_btn_{$answer_index}' class='answer-button'>답안 확인</button>";
                        $html .= "<button onclick='showFeedback({$answer_index})' id='feedback_btn_{$answer_index}' class='feedback-button'>피드백 보기</button>";
                    }
                }

                $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                $html .= "<div style='width: 50px; text-align: center; margin-top: 10px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✅</span></div>";
                $html .= "</div>";
                $answer_index++;
            } elseif (isset($block['children']) && is_array($block['children'])) {
                $html .= render_tree_plain($block['children'], $answer_index);
            }
        }

        return $html;
    }


    $answer_index = 0;
    echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
    ?>
</div>


    <!-- 오른쪽 패널 -->
    <div class="right-panel" style="display:none;">

    </div>
</div>

<script>

//뒤로가기 & 다시 풀기 버튼
document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = urlParams.get("step") || "1";
    const problemId = urlParams.get("problem_id") || "0";

    // 문제 가기 버튼
    document.getElementById("view-problem-button")?.addEventListener("click", () => {
        window.location.href = `/problem.php?id=${problemId}`;
    });

    // 다시 풀기 버튼
    document.getElementById("reset-button")?.addEventListener("click", () => {
        if (confirm("모든 입력을 초기화하고 다시 푸시겠습니까?")) {
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                // readonly 태그는 유지 (depth == 1 블록은 readonly임)
                if (textarea.hasAttribute('readonly')) return;

                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                const statusKey = `answer_status_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.removeItem(key);
                localStorage.removeItem(statusKey);

                // 시각적 스타일 리셋
                textarea.value = "";
                textarea.readOnly = false;
                textarea.disabled = false;
                textarea.style.backgroundColor = "white";
                textarea.style.border = "1px solid #ccc";
                textarea.style.color = "black";

                // 버튼/체크 아이콘 리셋
                const check = document.getElementById(`check_${index}`);
                const btn = document.getElementById(`btn_${index}`);
                const viewBtn = document.getElementById(`view_btn_${index}`);
                const answerArea = document.getElementById(`answer_area_${index}`);

                if (check) check.style.display = "none";
                if (btn) {
                    btn.style.display = "inline-block";
                    btn.disabled = false;
                }
                if (viewBtn) viewBtn.disabled = false;
                if (answerArea) answerArea.style.display = "none";
            });
        }
    });

});


//Step1, 2, 3버튼 부분
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const urlParams = new URLSearchParams(window.location.search);
    const currentStep = urlParams.get("step") || "1";
    const problemId = urlParams.get("problem_id") || "0";

    const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;

    //이 부분에서 오류발생
    document.querySelectorAll("textarea").forEach((textarea, index) => {
        const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
        const statusKey = `answer_status_step${currentStep}_q${index}_pid${problemId}`;
        const savedValue = localStorage.getItem(key);
        const savedStatus = localStorage.getItem(statusKey);

        // ✅ readonly 아닌 경우에만 저장된 값 덧씌우기
        if (!textarea.hasAttribute('readonly') && savedValue !== null) {
            textarea.value = savedValue;
        }

        // ✅ 입력값이 바뀔 때만 저장
        textarea.addEventListener("input", () => {
            if (!textarea.hasAttribute('readonly')) {
                localStorage.setItem(key, textarea.value);
            }
        });
    });

    // ✅ 버튼 클릭 시 저장 후 이동 + 스타일 토글
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            const nextStep = btn.getAttribute("data-step");
            const nextProblemId = btn.getAttribute("data-problem-id") || problemId;

            // 👉 모든 버튼에서 'active' 클래스 제거
            buttons.forEach(b => b.classList.remove("active"));

            // 👉 클릭한 버튼에만 'active' 클래스 추가
            btn.classList.add("active");

            // 값 저장
            document.querySelectorAll("textarea").forEach((textarea, index) => {
                const key = `answer_step${currentStep}_q${index}_pid${problemId}`;
                localStorage.setItem(key, textarea.value);
            });

            // 페이지 이동
            const baseUrl = window.location.pathname;
            window.location.href = `${baseUrl}?step=${nextStep}&problem_id=${nextProblemId}`;
        });
    });

    // ✅ 초기 로딩 시 URL의 step 값을 기준으로 버튼 강조
    buttons.forEach(btn => {
        const step = btn.getAttribute("data-step");
        if (step === currentStep) {
            btn.classList.add("active");
        } else {
            btn.classList.remove("active");
        }
    });
});

// textarea에서 tab을 누르면 들여쓰기가 적용되게([    ])
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('textarea').forEach((textarea) => {
      textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
          e.preventDefault(); // 기본 Tab 동작 막기

          const start = this.selectionStart;
          const end = this.selectionEnd;

          // 현재 위치에 '\t' 삽입
          this.value = this.value.substring(0, start) + '\t' + this.value.substring(end);

          // 커서 위치 조정
          this.selectionStart = this.selectionEnd = start + 1;
        }
      });
    });
  });

//textarea 입력 줄에 따라 높이 조절
document.addEventListener("DOMContentLoaded", function () {
    // 모든 textarea에 대해 자동 크기 조정 적용
    document.querySelectorAll(".styled-textarea").forEach((ta) => {
        autoResize(ta); // 초기 렌더링 시 높이 조정

        // 입력할 때마다 높이 자동 조정
        ta.addEventListener("input", () => autoResize(ta));
    });

    function autoResize(textarea) {
        // 높이를 auto로 리셋하고, scrollHeight를 기준으로 높이를 설정합니다.
        textarea.style.height = 'auto'; 
        textarea.style.height = textarea.scrollHeight + 'px'; // 내용에 따라 높이 설정
    }

    // 답안 확인 버튼 클릭 시에도 높이를 유지하도록 처리
    document.querySelectorAll(".answer-button").forEach((button, index) => {
        button.addEventListener("click", function () {
            // 답안 확인 시에도 이미 설정된 높이를 유지하도록 처리
            const textarea = document.getElementById(`ta_${index}`);
            if (textarea) {
                textarea.style.height = `${textarea.scrollHeight}px`; // 높이 고정
            }
        });
    });
});


//문제 맞았는지 여부 확인
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($problem_id) ?>

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();
    const step = new URLSearchParams(window.location.search).get("step") || "1";
    const problemId = new URLSearchParams(window.location.search).get("problem_id") || "0";
    const key = `answer_status_step${step}_q${index}_pid${problemId}`;


    console.log("제출값:", input);
    console.log("요청 데이터:", {
        answer: input,
        problem_id: problemId,
        index: index
    });

    fetch("../../ajax/check_answer_STEP.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            answer: input,
            problem_id: problemId,
            index: index,
            step: step
        })
    })
    .then(res => {
        if (!res.ok) {
            console.error("서버 오류:", res.status);
            return Promise.reject("서버 오류");
        }
        return res.json();
    })
    .then(data => {
        console.log(data);
        if (data.result === "correct") {
            localStorage.setItem(key, "correct");

            ta.readOnly = true;
            ta.style.backgroundColor = "#d4edda";
            ta.style.border = "1px solid #d4edda";
            ta.style.color = "#155724";
            // btn.style.display = "none";
            check.style.display = "inline";

                // 정답이 맞은 경우 버튼 숨기기
            const answerBtn = document.getElementById(`answer_btn_${index}`);
            const feedbackBtn = document.getElementById(`feedback_btn_${index}`);
            const submitBtn = document.getElementById(`submit_btn_${index}`);

            if (answerBtn) answerBtn.style.display = "none";
            if (feedbackBtn) feedbackBtn.style.display = "none";
            if (submitBtn) submitBtn.style.display = "none";

            const nextIndex = index + 1;
            const nextTa = document.getElementById(`ta_${nextIndex}`);
            const nextBtn = document.getElementById(`btn_${nextIndex}`);

            if (nextTa && nextBtn) {
                nextTa.disabled = false;
                nextBtn.disabled = false;
                nextTa.focus();
            }
        } else {
            ta.style.backgroundColor = "#ffecec";
            ta.style.border = "1px solid #e06060";
            ta.style.color = "#c00";
        }
    })
    .catch(err => {
        console.error("서버 요청 실패:", err);
    });
}

//문제가 되는 특수문자 치환
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

//답안 보여주기
function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();  // 정답 추출
    if (!correctCode) return;

    const escapedCode = escapeHtml(correctCode);  // ← 이걸로 HTML 무해화

    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${escapedCode}</pre>`;
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';
}

function showFeedback(index) {
    const urlParams = new URLSearchParams(window.location.search);
    const problemId = urlParams.get("problem_id") || "0";
    const ta = document.getElementById(`ta_${index}`);
    const blockCode = ta && ta.value.trim() !== "" ? ta.value.trim() : "작성못함";
    const step = urlParams.get("step") || "1";

    const feedbackPanel = document.querySelector('.right-panel');
    feedbackPanel.innerHTML = `
        <style>
            .feedback-panel {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f0f4f8;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                padding: 20px 25px;
                max-width: 350px;
                color: #2c3e50;
                user-select: text;
            }
            .feedback-header {
                font-size: 1.4rem;
                font-weight: 700;
                margin-bottom: 15px;
                border-bottom: 2px solid #3498db;
                padding-bottom: 8px;
                color: #2980b9;
            }
            .feedback-content p {
                font-size: 1rem;
                line-height: 1.5;
                margin: 8px 0;
            }
            .feedback-content strong {
                color: #34495e;
            }
        </style>

        <section class="feedback-panel">
            <header class="feedback-header">📋 피드백 창</header>
            <div class="feedback-content">
                <p>피드백을 가져오는 중입니다...</p>
            </div>
        </section>
    `;
    feedbackPanel.style.display = 'block';

    fetch("../../ajax/aifeedback_request.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            problem_id: problemId,
            index: index,
            block_code: blockCode,
            step: step
        })
    })
    .then(response => response.json())
    .then(data => {
    const feedbackPanel = document.querySelector('.right-panel');

    let feedbackText = data.result;

    // 문장이 끝난 후 줄바꿈 추가
    feedbackText = feedbackText.replace(/([.?!])\s*/g, "$1<br><br>");

    // 피드백 텍스트를 줄바꿈 기준으로 분할
    const feedbackContent = feedbackText;

    feedbackPanel.innerHTML = `
        <style>
            .feedback-panel {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #f0f4f8;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                padding: 20px 25px;
                max-width: 350px;
                color: #2c3e50;
                user-select: text;
            }
            .feedback-header {
                font-size: 1.4rem;
                font-weight: 700;
                margin-bottom: 15px;
                border-bottom: 2px solid #3498db;
                padding-bottom: 8px;
                color: #2980b9;
            }
            .feedback-content p {
                font-size: 1rem;
                line-height: 1.5;
                margin: 8px 0;
            }
            .feedback-content strong {
                color: #34495e;
            }
        </style>

        <section class="feedback-panel">
            <header class="feedback-header">📋 피드백 창</header>
            <div class="feedback-content">
                <div class="feedback-block">
                    <strong>${index + 1}번 줄에 대한 피드백:</strong>
                </div>
                ${feedbackText
                .split("<br><br>")
                .filter(paragraph => paragraph.trim() !== "") // 빈 항목 제거
                .map(paragraph => `
                    <div class="feedback-block">
                        ${paragraph.trim()}
                    </div>
                `).join("")}

            </div>
        </section>
    `;
    feedbackPanel.style.display = 'block';
})


    .catch(err => {
        console.error("서버 요청 실패:", err);
        const feedbackPanel = document.querySelector('.right-panel');
        feedbackPanel.innerHTML = `
            <style>
                .feedback-panel {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: #f8d7da;
                    border-radius: 10px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    padding: 20px 25px;
                    max-width: 350px;
                    color: #721c24;
                    user-select: text;
                }
                .feedback-header {
                    font-size: 1.4rem;
                    font-weight: 700;
                    margin-bottom: 15px;
                    border-bottom: 2px solid #f5c6cb;
                    padding-bottom: 8px;
                    color: #a71d2a;
                }
                .feedback-content p {
                    font-size: 1rem;
                    line-height: 1.5;
                    margin: 8px 0;
                }
            </style>

            <section class="feedback-panel">
                <header class="feedback-header">⚠️ 오류</header>
                <div class="feedback-content">
                    <p>서버 요청 오류: ${err.message}</p>
                </div>
            </section>
        `;
    });
}


//화면 크기 재조절
function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}


let currentTextarea = null;
let animationRunning = false;

//flowchart렌더링 
function updateImageForTextarea(index, ta) {
    // 현재 textarea와 관련된 이미지 업데이트
    currentTextarea = ta;
    
    // 플로우차트 이미지를 가져오기 위한 API 호출
    fetch(`../../get_flowchart1_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            let img = document.getElementById("flowchart_image");
            
            // 이미지가 없으면 동적으로 추가할 수 있지만, 여기서는 기존 이미지를 사용
            if (!img) {
                img = document.createElement("img");
                img.id = "flowchart_image";
                document.body.appendChild(img);  // 필요에 따라 이미지 태그를 동적으로 생성
            }

            img.src = data.url;  // 서버에서 받은 이미지 URL로 설정
            console.log("서버 디버그 데이터:", data.debug);

        });
}


//줄번호에 맞춰서 이미지 fetch(일단 보류)
function fetchImageByLineNumber(lineNumber) {
    const problemId = <?= json_encode($problem_id) ?>;
    fetch(`../../get_flowchart1_image.php?problem_id=${problemId}&index=${lineNumber-1}`) //값을 -1 해줘야 라인이 알맞음
        .then(response => response.json())
        .then(data => {
            let img = document.getElementById("flowchart_image");
            if (data.url) {
                // 이미지가 존재할 때만 보여주기
                img.src = data.url;
                img.style.display = "block";

                console.log("이미지 업데이트:", data.url);

                if (!animationRunning) {
                    animationRunning = true;
                }
            } else {
                // 이미지 없을 때 숨기기
                img.style.display = "none";
                console.log("이미지 없음. 숨김 처리됨.");
            }
        })
        .catch(error => console.error('Error:', error));
}

// textarea 클릭 시 이미지 로드
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
    ta.addEventListener("focus", () => fetchImageByLineNumber(idx)); 
    });
});
document.addEventListener("DOMContentLoaded", function () {
  $('.term-tooltip').popup({
    position: 'top center',
    hoverable: true,
    distanceAway: 50,
    delay: { show: 300, hide: 100 },
    onShow: function () {
      $(this).css('cursor', 'none');
    },
    onHide: function () {
      $(this).css('cursor', 'help'); // 원래대로 복원
    }
  });
});

</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>