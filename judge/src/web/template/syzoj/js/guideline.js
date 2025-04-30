document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".step-buttons .ui.button");
    const content = document.getElementById("guideline-content");

    // content 요소가 없으면 에러를 발생시키지 않도록 방어 코드 추가
    if (!content) {
        console.error("content 요소가 페이지에 없습니다.");
        // 대체 텍스트나 메시지를 추가할 수 있습니다.
        const body = document.body;
        const errorMessage = document.createElement('div');
        errorMessage.style.color = 'red';
        errorMessage.innerHTML = "⚠️ 가이드라인 콘텐츠를 불러올 수 없습니다. 페이지에 content 요소가 없습니다.";
        body.appendChild(errorMessage);  // 화면에 표시
        return; // 이후 코드 실행을 중단합니다.
    }

    // 파일 로딩 함수 (step에 해당하는 guideline1.php, guideline2.php, guideline3.php를 불러옴)
    function loadStep(step) {
        fetch(`guideline${step}.php`)  // guideline1.php, guideline2.php, guideline3.php를 동적으로 불러옴
            .then(res => res.text())
            .then(html => {
                if (content) {
                    content.innerHTML = html;  // 가이드라인 내용을 삽입
                    window.history.pushState(null, "", `?step=${step}`);  // URL에 step 파라미터 추가
                }
            })
            .catch(error => {
                if (content) {
                    content.innerHTML = "<div class='ui red message'>⚠️ 가이드라인을 불러올 수 없습니다.</div>";
                }
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
