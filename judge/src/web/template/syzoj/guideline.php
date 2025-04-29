<?php include("template/$OJ_TEMPLATE/header.php");?>

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

    function loadStep(step) {
        // 각 단계에 해당하는 guideline1.php, guideline2.php, guideline3.php 파일을 동적으로 불러옵니다.
        fetch(`guideline${step}.php`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('파일을 불러올 수 없습니다: ' + res.statusText);
                }
                return res.text();
            })
            .then(html => {
                content.innerHTML = html; // 가이드라인 내용을 삽입
                window.history.replaceState(null, "", `?step=${step}`); // URL에 step 파라미터 추가
            })
            .catch(error => {
                console.error('가이드라인 로딩 오류:', error); // 콘솔에서 오류 확인
                content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
            });
    }

    // 버튼 클릭 이벤트
    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const step = btn.dataset.step;
            loadStep(step); // 해당 step 로드
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

<?php include("template/$OJ_TEMPLATE/footer.php");?>
