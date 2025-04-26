<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <div class="ui large buttons" id="step_buttons">
        <button class="ui blue button" id="step1_button">Step 1</button>
    </div>

    <div id="content_area" class="ui segment" style="margin-top: 2em; padding: 2em;">
        <h3>여기에 단계별 내용을 표시합니다.</h3>
    </div>
</div>

<script>
function loadGuidelineContent(step) {
    var contentArea = document.getElementById('content_area');
    var xhr = new XMLHttpRequest();
    xhr.open('GET', step, true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        contentArea.innerHTML = xhr.responseText;

        // 스크립트 강제 실행
        const scripts = contentArea.querySelectorAll('script');
        scripts.forEach(oldScript => {
          const newScript = document.createElement('script');
          if (oldScript.src) {
            newScript.src = oldScript.src;
          } else {
            newScript.textContent = oldScript.textContent;
          }
          document.body.appendChild(newScript);
        });
      } else {
        contentArea.innerHTML = "Error loading content.";
      }
    };
    xhr.send();
}

document.addEventListener('DOMContentLoaded', function() {
    loadGuidelineContent('guideline1.php');
});

document.getElementById('step1_button').onclick = function() {
    loadGuidelineContent('guideline1.php');
};
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
