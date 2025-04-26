<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <!-- Step 버튼들을 위한 UI -->
    <div class="ui large buttons" id="step_buttons">
        <button class="ui blue button" id="step1_button">Step 1</button>
        <button class="ui blue button" id="step2_button">Step 2</button>
        <button class="ui blue button" id="step3_button">Step 3</button>
    </div>

    <div id="content_area" class="ui segment" style="margin-top: 2em; padding: 2em;">
        <h3>여기에 단계별 내용을 표시합니다.</h3>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 처음에는 Step1 내용을 불러옴
    loadGuidelineContent('guideline1.php');
});

document.getElementById('step1_button').onclick = function() {
    loadGuidelineContent('guideline1.php');
};

document.getElementById('step2_button').onclick = function() {
    loadGuidelineContent('guideline2.php');
};

document.getElementById('step3_button').onclick = function() {
    loadGuidelineContent('guideline3.php');
};

// guideline1, 2, 3의 내용을 동적으로 불러오는 함수
function loadGuidelineContent(step) {
    var contentArea = document.getElementById('content_area');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', step, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            contentArea.innerHTML = xhr.responseText;

            // 🔥 중요: 불러온 내용 중 <script> 태그를 강제로 실행시킨다
            executeScripts(contentArea);
        } else {
            contentArea.innerHTML = "Error loading content.";
        }
    };
    xhr.send();
}

// 🔥 불러온 content 안에 있는 <script> 들을 실행하는 함수
function executeScripts(element) {
    const scripts = element.querySelectorAll('script');
    scripts.forEach(oldScript => {
        const newScript = document.createElement('script');
        if (oldScript.src) {
            newScript.src = oldScript.src;
        } else {
            newScript.textContent = oldScript.textContent;
        }
        document.body.appendChild(newScript);
    });
}
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
