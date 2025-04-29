<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title><?php echo $OJ_NAME?></title>  
    <?php include("template/$OJ_TEMPLATE/css.php");?>	    

    <!-- Rank	팀 순위
    Team	팀 이름 (학교명 등)
    Num	소속 유저 수
    Solved	총 해결 문제 수
    Average	1인당 평균 해결 수
    Penalty	총 소요 시간
    PID[i]	각 문제의 정답/오답 인원 수 -->

    <!--[if lt IE 9]>
      <script src="template/<?php echo $OJ_TEMPLATE?>/js/html5shiv.js"></script>
      <script src="template/<?php echo $OJ_TEMPLATE?>/js/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
    <?php include("template/$OJ_TEMPLATE/nav.php");?>	    
      <!-- Main component for a primary marketing message or call to action -->
      <div class="jumbotron">
<?php
$rank=1;
?>

<!-- 대회 명 및 제어 링크 -->
<!-- 관리자일 경우 contestrank3.php (롤링 방식 랭킹), contestrank2.php (리플레이) 링크 노출 -->
<center><h3>Contest Team RankList -- <?php echo $title?></h3>
<!-- <a href="contestrank.xls.php?cid=<?php echo $cid?>" >Download</a> -->
<?php
if($OJ_MEMCACHE)
{
    if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
        echo ' | <a href="contestrank3.php?cid='.$cid.'">滚榜</a>';
    }
    echo '<a href="contestrank2.php?cid='.$cid.'">Replay</a>';
}
 ?>
</center>
<div style="overflow: auto">
<table id=rank><thead><tr class=toprow align=center><td class="{sorter:'false'}" width=5%>Rank<th width=10%>Team</th><th width=5%>Num</th><th width=5%>Solved</th><th width=5%>Average</th><th width=5%>Penalty</th>
<?php
for ($i=0;$i<$pid_cnt;$i++)
echo "<td><a href=problem.php?cid=$cid&pid=$i>$PID[$i]</a></td>";
echo "</tr></thead>\n<tbody>";

// school -> 학교(팀 이름)
// renshu -> 소속 유저 수
// solved -> 팀 전체 정답 수
// time -> 총 소요시간의 합
// p_ac_num[$j]-> 해당 팀에서 j번째 문제를 맞힌 사람 수
// p_wa_num[$j]-> 해당 팀에서 j번째 문제를 오답 낸 사람 수 
for ($i=0;$i<$school_cnt;$i++){
if ($i&1) echo "<tr class=oddrow align=center>\n";
else echo "<tr class=evenrow align=center>\n";
echo "<td>";
$school=$S[$i]->school;
$renshu=$S[$i]->renshu;
$usolved=$S[$i]->solved;
$avg=intval($usolved)/intval($renshu);
$format_avg = sprintf("%.2f",$avg);
if($school[0]!="*")
echo $rank++;
else
echo "*";
echo"<td>";
echo "$school";
echo "<td>$renshu";
echo "<td>$usolved";
echo "<td>$format_avg";
echo "<td>".sec2str($S[$i]->time);
for ($j=0;$j<$pid_cnt;$j++){
    $bg_color="eeeeee";

    // 정답 있음 (AC)
    if (isset($S[$i]->p_ac_num[$j])&&$S[$i]->p_ac_num[$j]>0){
      $aa=0x33+$S[$i]->p_wa_num[$j]*32;
      $aa=$aa>0xaa?0xaa:$aa;
      $aa=dechex($aa);
      $bg_color="$aa"."ff"."$aa";
      //$bg_color="aaffaa";
    }

    // 오답만 있음 (WA)
    else if(isset($S[$i]->p_wa_num[$j])&&$S[$i]->p_wa_num[$j]>0) {
      $aa=0xaa-$S[$i]->p_wa_num[$j]*10;
      $aa=$aa>16?$aa:16;
      $aa=dechex($aa);
      $bg_color="ff$aa$aa";
    }
    echo "<td class=well style='background-color:#$bg_color'>";
    if(isset($S[$i])){
      if (isset($S[$i]->p_ac_num[$j])&&$S[$i]->p_ac_num[$j]>0)
        echo $S[$i]->p_ac_num[$j]."/".$renshu;
      else if (isset($S[$i]->p_wa_num[$j])&&$S[$i]->p_wa_num[$j]>0)
        echo "(-".$S[$i]->p_wa_num[$j].")";
    }
}
echo "</tr>\n";
}
echo "</tbody></table>";
?>
</div>
      </div>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <?php include("template/$OJ_TEMPLATE/js.php");?>	    
<script type="text/javascript" src="include/jquery.tablesorter.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
        // penalty 시간 (hh:mm:ss) 또는 "(-3)" 형식 데이터를 정수/실수로 변환하여 정렬 가능하게 함

        $.tablesorter.addParser({
              // set a unique id
              id: 'punish',
              is: function(s) {
              // return false so this parser is not auto detected
              return false;
              },
              format: function(s) {
              // format your data for normalization
              var v=s.toLowerCase().replace(/\:/,'').replace(/\:/,'').replace(/\(-/,'.').replace(/\)/,'');
              //alert(v);
              v=parseFloat('0'+v);
              return v>1?v:v+Number.MAX_VALUE-1;
              },
              // set type, either numeric or text
              type: 'numeric'
              });
              $("#rank").tablesorter({
              headers: {
              4: {
              sorter:'punish'
              }
              <?php
              for ($i=0;$i<$pid_cnt;$i++){
              echo ",".($i+5).": { ";
              echo " sorter:'punish' ";
              echo "}";
              }
              ?>
              }
        });
  metal();
}
);
</script>
<script>
function getTotal(rows){
var total=0;
for(var i=0;i<rows.length&&total==0;i++){
try{
total=parseInt(rows[rows.length-i].cells[0].innerHTML);
if(isNaN(total)) total=0;
}catch(e){
}
}
return total;
}
function metal(){
var tb=window.document.getElementById('rank');
var rows=tb.rows;
try{
  <?php 
   // 어떤 팀도 제출을 하지 않았을 경우, 해당 팀은 solution 테이블에 데이터가 없어 순위표에 나타나지 않음.
  // 따라서 실제 참가 팀 수와 차트상 참가 팀 수가 다를 수 있으며, 메달 비율 계산에 오류가 생김.
  // 이를 해결하기 위해 $OJ_ON_SITE_TEAM_TOTAL 값을 설정할 수 있으며, 값이 0이면 실제 랭킹 테이블의 수를 사용함.

    if($OJ_ON_SITE_TEAM_TOTAL!=0)
      echo "var total=".$OJ_ON_SITE_TEAM_TOTAL.";";
    else
      echo "var total=getTotal(rows);";
  ?>
//alert(total);
for(var i=1;i<rows.length;i++){
var cell=rows[i].cells[0];
var acc=rows[i].cells[3];
var ac=parseInt(acc.innerText);
if (isNaN(ac)) ac=parseInt(acc.textContent);
if(cell.innerHTML!="*"&&ac>0){
var r=parseInt(cell.innerHTML);
if(r==1){
cell.innerHTML="Winner";
//cell.style.cssText="background-color:gold;color:red";
cell.className="badge btn-warning";
}
if(r>1&&r<=total*.05+1)
cell.className="badge btn-warning";
if(r>total*.05+1&&r<=total*.20+1)
cell.className="badge";
if(r>total*.20+1&&r<=total*.45+1)
cell.className="badge btn-danger";
if(r>total*.45+1&&ac>0)
cell.className="badge badge-info";
}
}
}catch(e){
//alert(e);
}
}

</script>
<style>
.well{
   background-image:none;
   padding:1px;
}
td{
   white-space:nowrap;

}
</style>
  </body>
</html>
