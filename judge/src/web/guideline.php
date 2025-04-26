<?php include("template/syzoj/header.php"); ?>
<style>
.step-buttons {
    display: flex;
    gap: 0;
    margin-bottom: 2em;
}
.step-buttons .ui.button {
    border-radius: 0;
    background-color: #2185d0;
    color: white;
}
.step-buttons .ui.button.active {
    background-color: #0d71bb;
}
</style>

<div class="ui container" style="margin-top: 3em;">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content">
        <!-- 🔄 여기에 동적으로 guideline1,2,3의 본문이 들어옵니다 -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    function loadStep(step) {
        content.innerHTML = "<div>로딩 중...</div>"; // ✨ 로딩 표시 추가
        fetch(`guideline${step}.php`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                window.history.replaceState(null, "", `?step=${step}`);
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

    // 기본 Step 1 로딩
    const urlParams = new URLSearchParams(window.location.search);
    const stepParam = urlParams.get('step') || 1;
    document.querySelector(`.step-buttons .ui.button[data-step="${stepParam}"]`).classList.add("active");
    loadStep(stepParam);
});
</script>

<?php include("template/syzoj/footer.php"); ?>
