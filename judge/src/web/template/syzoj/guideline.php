<?php include("template/syzoj/header.php"); ?>

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
    <div class="step-buttons">
        <button class="ui button active" data-step="1">Step 1</button>
        <button class="ui button" data-step="2">Step 2</button>
        <button class="ui button" data-step="3">Step 3</button>
    </div>

    <div id="guideline-content">
        <!-- 이곳에 PHP가 불러온 가이드라인 내용이 삽입됩니다 -->
    </div>
</div>

<!-- 기능 스크립트는 분리된 guideline.php가 담당 -->
<?php 
include("../../guideline.php");
include("template/syzoj/footer.php"); 
?>
