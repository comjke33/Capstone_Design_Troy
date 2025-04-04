<!-- 상단 페이지 출력 -->
<?php 
  $show_title="$MSG_ERROR_INFO - $OJ_NAME";
  include("template/$OJ_TEMPLATE/header.php");
?>
 <!-- UI 메세지 박스 구조 -->
<div class="ui positive icon message">
  <div class="content">
    <div class="header" style="margin-bottom: 10px; " ondblclick='$(this).load("refresh-privilege.php")'>
  
    <!-- cid(constest ID) 입력받아 정보 조회 -->
	<form  style="float:left;display:inline" action="balloon.php" method="get">
                Contest ID:<input type="text" name="cid" value="<?php echo $cid?>" >
                <input type="submit" class="btn btn-primary" value="Check">
	</form>
	<div style="float:right;display:inline">

  <!-- 사용자에게 JS confirm확인 받음
  POST방식으로 요청 -> CSRF보호 위해 set_post_key.php 포함 -->
	 <form  style="float:left;" action="balloon.php?cid=<?php echo $cid?>" method="post" onsubmit="return confirm('Delete All Tasks?');">
                <input type="hidden" name="cid" value="<?php echo $cid?>" >
                <input type="hidden" name="clean" >
                <input type="submit" class='btn btn-danger' value="Clean">
		<?php require_once(dirname(__FILE__)."/../../include/set_post_key.php")?>
        </form>
	</div>
	<table class="table table-striped content-box-header">
<tr><td>id<td><?php echo $MSG_USER_ID?><td><?php echo $MSG_COLOR?><td><?php echo $MSG_STATUS?><td></tr>
<?php

// 각 행에 대해 출력
foreach($view_balloon as $row){
	echo "<tr>\n";
	foreach($row as $table_cell){
		echo "<td>";
		echo $table_cell;
		echo "</td>";
	}
		$i++;
	echo "</tr>\n";
}
?>
</table>

        <p>
        </p>
      </div>
    </div>
      <!-- <p><%= err.details %></p> -->
    <p>
      <!-- <a href="<%= err.nextUrls[text] %>" style="margin-right: 5px; "><%= text %></a> -->
      
      <!-- 뒤로가기 링크 -->
      <a href="javascript:history.go(-1)"><?php echo $MSG_BACK;?></a>
    </p>
  </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
