<?php
  // 파일 제목
  $show_title="$MSG_ERROR_INFO - $OJ_NAME";
  if(isset($OJ_MEMCACHE)) include(dirname(__FILE__)."/header.php");

  // postive,check -> 성공 
  // negative, remove -> 실패
  if(isset($mark)&&$mark==100) {
        $ui_class="positive";
        $ui_icon="check";
  }else{
        $ui_class="negative";
        $ui_icon="remove";
  }
 
?>
   <div class="ui <?php echo $ui_class?> icon message">
   <i class="<?php echo $ui_icon?> icon"></i>

  <div class="content">
    <div class="header" style="margin-bottom: 10px; " ondblclick='$(this).load("refresh-privilege.php")'>
      <?php echo $view_errors;?>
      <?php if ($OJ_LANG=="cn" && isset($spj[0][0]) && $spj[0][0]!=2 )  echo "<br>만약 당신이 관리자라면, 이 문제를 해결하려면 여십시오.
                        <a href='http://hustoj.com' target='_blank'>HUSTOJ 자주 묻는 질문</a>，Ctrl+F를 눌러 위의 오류 메시지의 키워드를 찾습니다.$OJ_ADMIN 。";?>
    </div>
      <!-- <p><%= err.details %></p> -->
    <p>
      <!-- <a href="<%= err.nextUrls[text] %>" style="margin-right: 5px; "><%= text %></a> -->

      <a href="javascript:history.go(-1)"><?php echo $MSG_BACK;?></a>
    </p>
  </div>
</div>

<?php include(dirname(__FILE__)."/footer.php");?>
