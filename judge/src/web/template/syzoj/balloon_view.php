<?php 
  $show_title="$MSG_ERROR_INFO - $OJ_NAME";
  include("template/$OJ_TEMPLATE/header.php");
?>

<!-- Balloon Ticket 시각 구성
Semantic UI 메세지 박스 사용
자세한 설명: https://blog.naver.com/PostView.nhn?isHttpsRedirect=true&blogId=cultureup&logNo=222187148030
내부에 티켓 정보를 출력 -->
<div class="ui positive icon message">
  <i class="check icon"></i>
  <div class="content">
<h1>Balloon Ticket</h1>
<?php
// 참가자 정보 출력
echo "<h2>".htmlentities(str_replace("\n\r","\n",$view_user),ENT_QUOTES,"utf-8")."\n";
echo "-".htmlentities(str_replace("\n\r","\n",$view_school),ENT_QUOTES,"utf-8")."-".htmlentities(str_replace("\n\r","\n",$view_nick),ENT_QUOTES,"utf-8")."\n"."</h2>";

// 맞힌 문제 ID 출력 풍선 색깔&이름은 해당 문제에 따라 결정
echo "Problem ".$PID[$view_pid]."<br>";
if(isset($_GET['fb']) && intval($_GET['fb'])==1){
echo "Balloon Color: <font color='".$ball_color[$view_pid]."'>".$ball_name[$view_pid]." First Blood! </font><br>";
}else{
echo "Balloon Color: <font color='".$ball_color[$view_pid]."'>".$ball_name[$view_pid]."</font><br>";
}
?>

//풍선 티켓 인쇄 버튼
//풍선 배달 완료 후 돌아가는 버튼 (ballon.php로 이동)
<input onclick="window.print();" type="button" value="<?php echo $MSG_PRINTER?>">
<input onclick="location.href='balloon.php?id=<?php echo $id?>&cid=<?php echo $cid?>';" type="button" value="<?php echo $MSG_PRINT_DONE?>">

<!-- wx.jpg: QR 코드 또는 공지용 이미지로 추정됨
$view_map: 참가자의 좌석 위치/배치도 등을 담은 HTML 출력 -->
<img src="image/wx.jpg" height="100px" width="100px">
<?php echo $view_map?>
<script src="<?php echo $OJ_CDN_URL?>template/bs3/jquery.min.js"></script>
<script>
  
  // 배경색 문제의 풍선색으로 변경(풍선색에 따라 문제 여부확인)
  $("td:contains(<?php echo $view_user?>)").css("background-color","<?php echo $ball_color[$view_pid]?>");

</script>
  </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
