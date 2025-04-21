<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
  <!-- Step 버튼들을 위한 UI -->
  <div class="ui large buttons" id="step_buttons">
    <button class="ui blue button" id="step1_button">Step 1</button>
    <button class="ui blue button" id="step2_button">Step 2</button>
    <button class="ui blue button" id="step3_button">Step 3</button>
  </div>

  <!-- 동적으로 내용이 로드될 부분 -->
  <div id="content_area" class="ui segment" style="margin-top: 2em; padding: 2em;">
    <h3>여기에 단계별 내용을 표시합니다.</h3>
  </div>
</div>

<script>
  // 각 단계별 버튼 클릭 시 동적으로 내용을 로드하는 AJAX 기능
  document.getElementById('step1_button').onclick = function() {
    loadGuidelineContent('guideline1.php');
  };

  document.getElementById('step2_button').onclick = function() {
    loadGuidelineContent('guideline2.php');
  };

  document.getElementById('step3_button').onclick = function() {
    loadGuidelineContent('guideline3.php');
  };

  // guideline1, guideline2, guideline3의 내용을 동적으로 불러오는 함수
  function loadGuidelineContent(step) {
    var contentArea = document.getElementById('content_area');

    // AJAX 요청
    var xhr = new XMLHttpRequest();
    xhr.open('GET', step, true);
    xhr.onload = function() {
      if (xhr.status === 200) {
        contentArea.innerHTML = xhr.responseText;  // 로드된 내용을 content_area에 삽입
      } else {
        contentArea.innerHTML = "Error loading content.";  // 오류 처리
      }
    };
    xhr.send();
  }
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
