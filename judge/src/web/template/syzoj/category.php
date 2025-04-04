<?php $show_title="$MSG_SOURCE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<!-- 카테고리 목록 렌더링 -->
<div class="padding">
    <div style="margin-top: 0px; margin-bottom: 14px; padding-bottom: 0px; " >
        <p class="transition visible">
           <h1 style="margin-left: 10px; display: inline-block; "><?php echo $MSG_SOURCE?></h1>
        </p>
        <div class="ui existing segment">
        <?php echo $view_category?>
        </div>
    </div>

<!-- 현재 사용중인 $OJ_TEMPLATE의 footer.php를 포함 -->
<?php include("template/$OJ_TEMPLATE/footer.php");?>
