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
  // 페이지가 로드된 후에 실행될 스크립트
  document.addEventListener('DOMContentLoaded', function() {
    loadGuidelineContent('guideline1.php');  // 기본적으로 guideline1을 로드
  });

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

        // 텍스트 영역과 제출 버튼을 포함한 새로운 콘텐츠
        var submitButton = document.createElement('button');
        submitButton.classList.add('ui', 'blue', 'button');
        submitButton.textContent = '제출';
        submitButton.addEventListener('click', function() {
            submitAnswer('1000');  // 문제 ID를 제출 함수에 전달
        });

        var textarea = document.createElement('textarea');
        textarea.id = 'user_input';
        textarea.rows = 10;
        textarea.style.width = '100%';

        // 기존 content_area에 제출 버튼과 텍스트 영역 추가
        contentArea.appendChild(textarea);
        contentArea.appendChild(submitButton);
      } else {
        contentArea.innerHTML = "Error loading content.";  // 오류 처리
      }
    };
    xhr.send();
  }

  // 제출 버튼 클릭 시 처리할 함수
  function submitAnswer(problem_id) {
    var userInput = document.getElementById('user_input').value;

    // 입력값이 비어있지 않으면 서버로 제출
    if (userInput.trim() !== '') {
      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'submit_answer.php', true);  // 서버의 처리 파일로 POST 요청
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      xhr.onload = function() {
        if (xhr.status === 200) {
          // 서버 응답 처리 (예: 성공 메시지)
          alert('제출되었습니다!');
        } else {
          alert('서버에 문제가 발생했습니다.');
        }
      };
      xhr.send('user_input=' + encodeURIComponent(userInput) + '&problem_id=' + problem_id);  // 사용자 입력값과 문제 ID를 서버로 전송
    } else {
      alert('내용을 입력해주세요.');
    }
  }
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
