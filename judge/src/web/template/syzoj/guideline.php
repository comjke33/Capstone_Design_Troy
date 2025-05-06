<!-- 일단 비워두기  -->
<?php include("template/syzoj/header.php"); ?>

<!-- 상단 툴바 -->
<div class="top-toolbar">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>
</div>

<!-- 메인 콘텐츠 -->
<div class="layout-container">
    <div class="content-area">
        <!-- 왼쪽 문제 영역 -->
        <div id="guideline-content">
            <!-- 동적으로 삽입됨 -->
        </div>

        <!-- 오른쪽 피드백 창 -->
        <div id="feedback-panel">
            <div class="feedback-title">📋 피드백 창</div>
            <img src="/images/totoro.png" class="feedback-image" alt="Totoro" />
        </div>
    </div>
</div>

<style>
.top-toolbar {
    width: 100%; padding: 15px 30px;
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
.step-buttons {
    display: flex; gap: 10px;
}
.step-buttons .ui.button {
    background-color: #2185d0 !important; color: white !important;
    border-radius: 4px !important; min-width: 100px;
}
.step-buttons .ui.button.active {
    background-color: #0d71bb !important;
}
.layout-container {
    width: 100%; max-width: 1600px; margin: 0 auto;
    padding: 40px; box-sizing: border-box;
    background-color: #fff;
}
.content-area {
    display: flex; gap: 40px;
}
#guideline-content {
    flex-grow: 1; min-width: 900px;
}
#feedback-panel {
    width: 280px; padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
}
.feedback-title {
    font-size: 1.2em; font-weight: bold;
    margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;
}
.feedback-image {
    max-width: 100%; height: auto;
    border-radius: 8px; margin-top: 10px;
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    function loadStep(step) {
        const problemId = 0; // 필요 시 실제 문제 ID로 설정
        fetch(`/guideline.php?step=${step}&problem_id=${problemId}`)
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
            loadStep(btn.dataset.step);
        });
    });

    const step = new URLSearchParams(window.location.search).get("step") || 1;
    loadStep(step);
});
</script>

<?php include("template/syzoj/footer.php"); ?>
