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
  <div class="ui segment">
    <h3 class="ui header">제출 번호: <code><?= htmlspecialchars($sid) ?></code></h3>

    <div class="ui top attached header">소스 코드</div>
    <pre class="ui attached segment" style="background: #f9f9f9; font-family: monospace; overflow-x: auto; white-space: pre-wrap; border-radius: 0;">
<?= htmlspecialchars($source) ?>
    </pre>
  </div>
<?php endif; ?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
