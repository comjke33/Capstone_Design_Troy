<!-- 일단 비워두기  -->
<?php include("template/syzoj/header.php"); ?>

<!-- ✅ 상단 고정 툴바 -->
<div class="top-toolbar">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>
</div>

<!-- ✅ 본문 영역 -->
<div class="layout-container">
  <div class="content-area">
    <div id="guideline-content">
      <!-- JS로 동적 로딩됨 -->
    </div>
  </div>
</div>

<!-- ✅ 스타일 정의 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.css">
<style>
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

.layout-container {
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    padding: 40px;
    box-sizing: border-box;
    background-color: #ffffff;
}

.content-area {
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

#guideline-content {
    flex-grow: 1;
    min-width: 900px;
    max-width: 100%;
}
</style>

<!-- ✅ 기능 스크립트 -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    function loadStep(step) {
        fetch("/src/web/guideline.php?step=" + step)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                window.history.replaceState(null, "", "?step=" + step);
            })
            .catch(error => {
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                console.error("로드 오류:", error);
            });
    }

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
            loadStep(btn.dataset.step);
        });
    });

    const step = new URLSearchParams(window.location.search).get("step") || 1;
    loadStep(step);
});
</script>

<?php include("template/syzoj/footer.php"); ?>
