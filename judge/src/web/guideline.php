<?php include("template/$OJ_TEMPLATE/header.php"); ?>

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
        <!-- 여기에 동적으로 guideline1/2/3.php의 결과가 삽입됩니다 -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    // loadStep 함수는 step 번호에 맞는 PHP 파일을 동적으로 불러옵니다.
    function loadStep(step) {
        fetch(`guideline${step}.php`) // guideline1.php, guideline2.php, guideline3.php를 동적으로 불러옴
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;  // 가이드라인 내용 삽입
                window.history.replaceState(null, "", `?step=${step}`); // URL에 step 파라미터 갱신
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
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
