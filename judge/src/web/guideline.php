<?php include("template/syzoj/header.php"); ?>

<!-- ✅ 상단 고정 툴바 (버튼 영역) -->
<div class="top-toolbar">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>
</div>

<!-- ✅ 본문 가이드라인 출력 영역 -->
<!-- 전체 메인 콘텐츠 (좌: 문제 / 우: 피드백) -->
<div class="layout-container">
  <div class="content-area">
    <!-- 왼쪽 문제 풀이 영역 -->
    <div id="guideline-content">
      <!-- JS로 문제 동적 삽입 -->
    </div>

    <!-- 오른쪽 피드백 영역 -->
    <div id="feedback-panel">
      <div class="feedback-title">📋 피드백 창</div>
      <img src="/images/totoro.png" alt="Totoro" class="feedback-image">
    </div>
  </div>
</div>


<style>
/* ✅ 상단 툴바 스타일 */
.top-toolbar {
    width: 100%;
    padding: 15px 30px;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: relative;
    z-index: 10;
}

.step-buttons {
    display: flex;
    flex-direction: row;
    gap: 10px;
}

.step-buttons .ui.button {
    border-radius: 4px !important;
    background-color: #2185d0 !important;
    color: white !important;
    min-width: 100px;
}

.step-buttons .ui.button.active {
    background-color: #0d71bb !important;
}

/* 전체 콘텐츠 컨테이너: 좌우 여백 포함해 넓게 설정 */
.layout-container {
  width: 100%;
  max-width: 1600px;
  margin: 0 auto;
  padding: 40px 40px;
  box-sizing: border-box;
  background-color: #ffffff; /* 필요 시 배경 조정 가능 */
}

/* 문제 영역 + 피드백 영역 나란히 배치 */
.content-area {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: flex-start;
  gap: 40px;
}

/* 문제 풀이 공간: 왼쪽에 최대한 넓게 배치 */
#guideline-content {
  flex-grow: 1;
  min-width: 900px;
  max-width: 100%;
}

/* 피드백 박스: 고정 너비 + 박스형 카드 스타일 */
#feedback-panel {
  width: 280px;
  background-color: #ffffff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  text-align: center;
}

/* 피드백 제목 */
.feedback-title {
  font-size: 1.2em;
  font-weight: bold;
  margin-bottom: 10px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 5px;
}

/* 이미지: 박스 안에서 반응형으로 표시 */
.feedback-image {
  max-width: 100%;
  height: auto;
  margin-top: 10px;
  border-radius: 8px;
}

</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    function loadStep(step) {
        fetch(`guideline${step}.php`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                window.history.replaceState(null, "", `?step=${step}`);
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                console.error("가이드라인 로딩 오류:", error);
            });
    }

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            const step = btn.dataset.step;
            loadStep(step);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    const initialStep = urlParams.get('step') || 1;
    loadStep(initialStep);

    buttons.forEach(btn => {
        if (btn.dataset.step == initialStep) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
});
</script>

<?php include("template/syzoj/footer.php"); ?>
