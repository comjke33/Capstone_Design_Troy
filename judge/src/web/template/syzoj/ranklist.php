<?php $show_title="$MSG_RANKLIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<!-- 특정 기간동안의 랭킹 확인 -->
<div class="padding">
	<a href="ranklist.php?scope=d" class="ui mini button"><?php echo $MSG_DAY?></a>
	<a href="ranklist.php?scope=w" class="ui mini button"><?php echo $MSG_WEEK?></a>
	<a href="ranklist.php?scope=m" class="ui mini button"><?php echo $MSG_MONTH?></a>
	<a href="ranklist.php?scope=y" class="ui mini button"><?php echo $MSG_YEAR?></a>
  <form action="ranklist.php" class="ui mini form" method="get" role="form" style="margin-bottom: 25px; text-align: right; ">
    <div class="ui action left icon input inline" style="width: 180px; margin-right: 77px; ">
      <i class="search icon"></i><input name="prefix" placeholder="<?php echo $MSG_USER?>" type="text" value="<?php echo htmlentities(isset($_GET['prefix'])?$_GET['prefix']:"",ENT_QUOTES,"utf-8") ?>">
      <button class="ui mini button" type="submit"><?php echo $MSG_SEARCH?></button>
    </div>
     <div class="ui action left icon input inline" style="width: 180px; margin-right: 77px; ">
      <i class="search icon"></i><input name="group_name" placeholder="<?php echo $MSG_GROUP_NAME ?>" type="text" value="<?php echo htmlentities(isset($_GET['group_name']) ? $_GET['group_name'] : "", ENT_QUOTES, "utf-8") ?>">
      <button class="ui mini button" type="submit"><?php echo $MSG_SEARCH ?></button>
    </div>
  </form>

      <!-- 순위 테이블(순위, 사용자, 닉네임, 그룹 이름, 문제 해결 수, 제출 수 비율) -->
	    <table class="ui very basic center aligned table" 
      style="table-layout: fixed; ">
	        <thead>
	        <tr>
	            <th style="width: 60px; "><?php echo $MSG_Number?></th>
	            <th style="width: 180px; "><?php echo $MSG_USER?></th>
	            <th><?php echo $MSG_NICK?></th>
			<th><?php echo $MSG_GROUP_NAME?></th>
              <th style="width: 100px; "><?php echo $MSG_SOVLED?></th>
              <th style="width: 100px; "><?php echo $MSG_SUBMIT?></th>
              <th style="width: 100px; "><?php echo $MSG_RATIO?></th>
	        </tr>
	        </thead>
	        <tbody>
          <?php
          foreach($view_rank as $row){
          echo "<tr>";
          foreach($row as $table_cell){
          echo "<td>";
          echo "\t".$table_cell;
          echo "</td>";
          }
          echo "</tr>";
          }
          ?>
	        </tbody>
	    </table>
    <br>
    <div style="margin-bottom: 30px; ">
  
  <div style="text-align: center; ">
	<div class="ui pagination" style="box-shadow: none; ">      
    <?php
    for($i = 0; $i <$view_total ; $i += $page_size) {
    $str= "<a class=\"ui button\" href='./ranklist.php?start=" . strval ( $i ).($scope?"&scope=$scope":"") . "'>";
    $str.= strval ( $i + 1 );
    $str.= "-";
    $str.= strval ( $i + $page_size );
    $str.= "</a>";
    echo $str;
    }
    ?>
	</div>
  </div>
</div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
