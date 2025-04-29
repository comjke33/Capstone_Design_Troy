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
        <!-- 🔄 여기에 동적으로 guideline1/2/3.php의 결과가 삽입됩니다 -->
    </div>
</div>

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
            });
    }

    // 버튼 클릭 이벤트
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const step = btn.dataset.step;
            loadStep(step);
        });
    });

    // URL에 step이 이미 있으면 그걸 로딩, 아니면 기본 1로
    const urlParams = new URLSearchParams(window.location.search);
    const initialStep = urlParams.get('step') || 1;
    loadStep(initialStep);

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