<?php
$show_title = "$MSG_STATUS - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>

<script src="template/<?php echo $OJ_TEMPLATE ?>/js/textFit.min.js"></script>

<div class="padding">

<?php if ($solution_id <= 0): ?>
  <div class="ui red message">❌ 유효하지 않은 solution_id입니다.</div>

<?php elseif (!$sid): ?>
  <div class="ui red message">❌ 해당 제출을 찾을 수 없습니다.</div>

<?php else: ?>
  <h2>🧾 제출 번호: <code><?= htmlspecialchars($sid) ?></code></h2>
  <h3>📄 소스 코드</h3>
  <pre style="background:#f8f8f8; padding:15px; border:1px solid #ccc; border-radius:6px; font-family:monospace; overflow:auto;">
<?= htmlspecialchars($source) ?>
  </pre>
<?php endif; ?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
