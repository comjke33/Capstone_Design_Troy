<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>TROY OJ 사용 가이드</title>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
</head>
<body>
<div class="ui container" style="margin-top: 5vh;">
  <div class="ui active modal" id="guideModal">
    <div class="header"><i class="book icon"></i> TROY OJ 사용 가이드</div>
    <div class="scrolling content" style="max-height: 70vh; overflow-y: auto;">
      
      <!-- 단계별 풀이 -->
      <h4 class="ui dividing header">📘 단계별 풀이 가이드</h4>
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item">문제 페이지의 <b>[단계적 풀기]</b> 버튼으로 학습을 시작하세요.</div>
        <div class="item"><code>#include &lt;stdio.h&gt;</code>는 자동 포함되므로 따로 선언하지 않아도 됩니다.</div>
        <div class="item ui warning message" style="color:#d72638; background-color: #fff8f6;">
          ⚠ <b>중요:</b> 조건문/반복문/함수 선언 시 <code>{</code>는 <u>직접 작성</u>, <code>}</code>는 <u>작성 금지</u> (자동 처리)
        </div>
        <div class="item"><b>Step 1:</b> 한 줄씩 입력 – 코드 의미 및 동작 이해</div>
        <div class="item"><b>Step 2:</b> 문단 단위 – 흐름 중심 연습</div>
        <div class="item"><b>Step 3:</b> 전체 구성 – 자신만의 스타일로 완성 (제출 없음)</div>
        <div class="item">Step 1·2 진행 시 좌측 <b>Flowchart</b>로 구조를 시각화합니다.</div>
        <div class="item"><b>[피드백 보기]</b> 버튼으로 AI 힌트를 받을 수 있습니다.</div>
        <div class="item ui negative message" style="background-color: #fff6f6;">
          ⚠ <b>주의:</b> <u>코드 스타일(들여쓰기, 줄바꿈 등)</u>은 정답 기준이 아니지만,<br />
          <span style="color: #c62828;">가이드라인의 <u>변수명 및 흐름을 따르지 않으면 오답 처리될 수 있습니다.</u></span>
        </div>
      </div>

      <!-- 문법 오류 리포트 -->
      <h4 class="ui dividing header">📊 개인별 문법 오류 리포트</h4>
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item">우측 상단의 <b>종 아이콘</b> 클릭 → 문법 오류 리포트 확인</div>
        <div class="item">5일간 <b>15회 이상 제출</b> 시 AI가 주요 오류를 분석해 통계 제공합니다.</div>
        <div class="item">이 기능으로 <b>자신의 약한 개념을 파악하고 학습하세요.</b></div>
      </div>

      <!-- 유사 문제 추천 -->
      <h4 class="ui dividing header">🔁 유사 문제 추천 및 문법 오류 확인</h4>
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item"><b>정답 제출 시</b> → Codeup 유사 문제 풀이 페이지로 이동</div>
        <div class="item"><b>오답 제출 시</b> → <b>[문법 오류 확인]</b> 버튼 생성, 개념 링크 제공</div>
      </div>

      <!-- 안내 메시지 -->
      <div class="ui info message" style="margin-top: 2em;">
        <i class="info icon"></i>
        <b>이 기능들을 활용하여 실력을 체계적으로 쌓아보세요!</b>
      </div>

    </div>
    <div class="actions">
      <div class="ui checkbox">
        <input type="checkbox" id="agreeCheck">
        <label>위 내용을 모두 읽고 이해했습니다.</label>
      </div>
      <button class="ui primary button" id="startBtn" disabled>확인하고 시작하기</button>
    </div>
  </div>
</div>

<script>
  $('#agreeCheck').change(function () {
    $('#startBtn').prop('disabled', !this.checked);
  });

  $('#startBtn').click(function () {
    localStorage.setItem('troy_help_read', 'yes');
    window.location.href = 'index.php';
  });
</script>
</body>
</html>