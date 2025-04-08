<?php
$show_title = "$MSG_STATUS - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>

<script src="template/<?php echo $OJ_TEMPLATE ?>/js/textFit.min.js"></script>

<div class="ui container" style="margin-top: 2em;">

<?php if ($solution_id <= 0): ?>
  <div class="ui negative message">
    <div class="header">잘못된 요청입니다</div>
    <p>solution_id 값이 유효하지 않습니다.</p>
  </div>

<?php elseif (!$sid): ?>
  <div class="ui warning message">
    <div class="header">제출을 찾을 수 없습니다</div>
    <p>해당 solution_id에 대한 소스코드가 존재하지 않습니다.</p>
  </div>

<?php else: ?>
  <div class="ui two column stackable grid">
    
    <!-- ✅ 왼쪽: 전체 코드 입력창 -->
    <div class="column ten wide">
      <div class="ui segment">
        <h4 class="ui dividing header">전체 코드 입력</h4>
        <form method="post" class="ui form">
          <div class="field">
            <label>  'Hello World'를 출력하시오.</label>
            <textarea name="full_source" rows="20" style="font-family: monospace;"><?= htmlspecialchars($_POST['full_source'] ?? '') ?></textarea>
          </div>
          <button class="ui blue button" type="submit">제출</button>
        </form>
      </div>
    </div>

    <!-- ✅ 오른쪽: 깔끔한 HUSTOJ 스타일 피드백 -->
    <div class="column six wide">
      <div class="ui segment">
        <h4 class="ui dividing header">피드백</h4>
        <div class="ui info message">
          <p><strong>출력 부분에서 출력 양식이 틀렸습니다.</strong></p>
          <p>문제 양식을 확인하고 알맞게 제출해주세요!</p>
        </div>
      </div>
    </div>

  </div>
<?php endif; ?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
