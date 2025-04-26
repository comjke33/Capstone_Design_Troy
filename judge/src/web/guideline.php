<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ 현재 step 파라미터
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// ✅ 렌더링할 파일 결정
switch ($step) {
    case 1:
        $include_file = "guideline1.php";
        break;
    case 2:
        $include_file = "guideline2.php";
        break;
    case 3:
        $include_file = "guideline3.php";
        break;
    default:
        die("Invalid step");
}

// ✅ 출력 버퍼링으로 내용 받아오기
ob_start();
include($include_file);
$guideline_content = ob_get_clean();
?>

<style>
.step-buttons {
    display: flex;
    gap: 0;
    margin-bottom: 2em;
}
.step-buttons .ui.button {
    border-radius: 0;
    background-color: #2185d0;
    color: white;
}
.step-buttons .ui.button.active {
    background-color: #0d71bb;
}
</style>

<div class="ui container" style="margin-top: 3em;">
    <!-- ✅ Step 탭 버튼 UI -->
    <div class="step-buttons">
        <a href="?step=1" class="ui button <?= $step == 1 ? 'active' : '' ?>">Step 1</a>
        <a href="?step=2" class="ui button <?= $step == 2 ? 'active' : '' ?>">Step 2</a>
        <a href="?step=3" class="ui button <?= $step == 3 ? 'active' : '' ?>">Step 3</a>
    </div>

    <!-- ✅ 선택된 파일 렌더링 결과 출력 -->
    <?= $guideline_content ?>
</div>

<?php include("template/syzoj/footer.php"); ?>
