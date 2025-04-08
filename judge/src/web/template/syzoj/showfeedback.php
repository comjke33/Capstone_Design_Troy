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
    
    <!-- ✅ 왼쪽: 소스 코드 영역 -->
    <div class="column ten wide">
      <div class="ui segment">
        <h4 class="ui top attached header">제출 번호: <code><?= htmlspecialchars($sid) ?></code></h4>
        <div class="ui attached segment" style="background: #f9f9f9; padding: 15px; border-radius: 0; font-family: monospace; white-space: pre-wrap; overflow-x: auto;">
<?= htmlspecialchars($source) ?>
        </div>
      </div>
    </div>

    <!-- ✅ 오른쪽: 분석/피드백 영역 -->
    <div class="column six wide">
      <div class="ui raised very padded segment" style="background: #f1f1f1; min-height: 200px;">
        <h4 class="ui header">🔍 자동 피드백</h4>
        <p>여기에 분석 결과나 피드백이 표시됩니다.</p>
        <p style="color: gray;">(예: 코드 라인 3에서 반복문 사용 추천)</p>
      </div>
    </div>

  </div>
<?php endif; ?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
