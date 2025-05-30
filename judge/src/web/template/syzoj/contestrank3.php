<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <link rel="stylesheet" type="text/css" href="template/<?php echo $OJ_TEMPLATE?>/scrollboard.css">
    <title><?php echo $OJ_NAME?></title>
    <?php 
	$OJ_TEMPLATE="bs3";
	include("template/$OJ_TEMPLATE/css.php");?>

</head>

<body>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<?php include("template/$OJ_TEMPLATE/js.php");?>
<script type="text/javascript" src="include/jquery.tablesorter.js?v=0.1"></script>
<script type="text/javascript" src="template/<?php echo $OJ_TEMPLATE?>/scrollboard.js?v=0.09"></script>
<script type="text/javascript">

    var board = new Board(<?php echo $problem_num?>, new Array(<?php echo $gold_num?>, <?php echo $silver_num?>, <?php echo $bronze_num?>),StringToDate("<?php echo  $start_time_str?>"), StringToDate("<?php echo $lock_time_str?>"),<?php echo $cid?>);

    board.showInitBoard();
    //페이지 click시 board.keydown()호출하여 순위판 갱신
    $('html').click(function(e) {
            board.keydown();
    });
    //keydown되면 순위판 갱신, shift+Enter 누르면 갱신주기 3초
    $('html').keydown(function(e) {
	if(e.keyCode==13){
                if(e.shiftKey)
                        window.setInterval("board.keydown()",3000);
                else
                board.keydown();
        }

    });
</script>
</body>
</html>
