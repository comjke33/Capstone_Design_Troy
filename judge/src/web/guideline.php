<?php include("template/syzoj/header.php");//렌더링 파일 불러오기 ?>

<div class="ui container" style="margin-top: 3em; display: flex; align-items: flex-start;">
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content" style="margin-left: 2em; flex-grow: 1;">
        <!-- 이곳에 동적으로 내용이 삽입됩니다 -->
    </div>
</div>

<div class="layout-container">
    <!-- 좌측 상단 버튼 -->
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <!-- 가운데 가이드라인 출력 영역 -->
    <div id="guideline-content">
        <!-- 여기에 동적으로 PHP 출력 내용이 삽입됨 -->
    </div>
</div>

<style>
/* 전체 레이아웃을 가로로 구성 */
.layout-container {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    gap: 30px;
}

/* 버튼 가로 정렬 + 왼쪽 상단 고정 */
.step-buttons {
    display: flex;
    flex-direction: row;
    gap: 10px;
    margin-top: 0;
    margin-left: 0;
}

/* 버튼 스타일 강화 */
.step-buttons .ui.button {
    border-radius: 0 !important;
    background-color: #2185d0 !important;
    color: white !important;
    min-width: 100px;
}

.step-buttons .ui.button.active {
    background-color: #0d71bb !important;
}

/* 가이드라인 내용 출력 영역을 넓게 */
#guideline-content {
    flex-grow: 1;
    max-width: 100%;
    min-width: 700px;
}
</style>


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

<?php include("template/syzoj/footer.php");//렌더링 파일 불러오기 ?>
