<?php $show_title="$MSG_PROBLEMS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="ui container" style="margin-top: 3em;">
  <div class="ui center aligned">
    <div style="display: inline-flex; gap: 2em;"> <!-- 두 폼 사이 간격 설정 -->
      
      <!-- 검색 폼 -->
      <form class="ui form" method="get" action="">
        <div class="ui action input" style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-radius: 6px;">
          <input 
            type="text" 
            name="search" 
            placeholder="<?php echo $MSG_TITLE; ?> 또는 출처로 검색하세요…"
            value="<?php if (isset($_GET['search'])) echo htmlentities($_GET['search'], ENT_QUOTES, 'UTF-8'); ?>"
            style="border-radius: 6px 0 0 6px;"
          >
          <button type="submit" class="ui blue icon button" style="border-radius: 0 6px 6px 0;">
            <i class="search icon"></i>
          </button>
        </div>
      </form>

      <!-- ID 이동 폼 -->
      <form class="ui form" method="get" action="problem.php">
        <div class="ui action input" style="box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-radius: 6px;">
          <input 
            type="text" 
            name="id" 
            placeholder="문제 ID 입력…"
            value="<?php if (isset($_GET['id'])) echo htmlentities($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>"
            style="border-radius: 6px 0 0 6px;"
          >
          <button type="submit" class="ui blue icon button" style="border-radius: 0 6px 6px 0;">
            <i class="arrow right icon"></i>
          </button>
        </div>
      </form>

    </div>
  </div>
</div>


<!-- prev, next부분 -->
<!-- <?php if (!isset($_GET['list'])){ ?>

<div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
<div style="text-align: center; ">
  <div class="ui pagination menu" style="box-shadow: none; ">
    <a href="problemset.php?page=1" class="icon item">  
      <i class="fast backward icon"></i>
    </a>


    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"problemset.php?page=".$i.htmlentities($postfix,ENT_QUOTES,'UTF-8')."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "problemset.php?page=".($page+1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>  
    <a href="problemset.php?page=<?php echo $view_total_page?>" class="icon item">  
      <i class="fast forward icon"></i>
    </a>
  </div>
</div>
</div>
<?php } ?> -->


<!-- 검색창 아래 여백 추가 -->
<div class="ui row">
  <div class="sixteen wide column">
    <div style="height: 60px; clear: both;"></div>
  </div>
</div>


  <!-- 테이블 부분 -->
  <table class="ui very basic center aligned table">
    <thead>
      <tr>

        <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])){?>
          <th class="one wide"><?php echo $MSG_STATUS?></th>
        <?php } ?>
        <th class="one wide"><?php echo $MSG_PROBLEM_ID?></th>
        <th class="left aligned"><?php echo $MSG_TITLE?></th>
        <th class="one wide"><?php echo $MSG_SOVLED."/".$MSG_SUBMIT?></th>
        
        <th class="one wide"><?php echo $MSG_PASS_RATE?></th>
      </tr>
    </thead>
    <tbody>
    <?php
          $color=array("blue","teal","orange","pink","olive","red","yellow","green","purple");
          $tcolor=0;
          $i=0;
          foreach ($result as $row){
		echo "<tr>";
            if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])){

              if (isset($sub_arr[$row['problem_id']])){
                if (isset($acc_arr[$row['problem_id']])) 
                  echo "<td><span class=\"status accepted\"><i class=\"checkmark icon\"></i></span></td>";
                else 
                  echo "<td><span class=\"status wrong_answer\"><i class=\"remove icon\"></i></span></td>";
              }else{
                echo "<td><span class=\"status\"><i class=\"icon\"></i></span></td>";
              }
            }

             echo  "<td><b>".$row['problem_id']."</b></td>";
             echo "<td class=\"left aligned\">";
             echo "<a style=\"vertical-align: middle; \" href=\"problem.php?id=".$row['problem_id']."\">";
             echo $row['title'];
             echo "</a>";
             if($row['defunct']=='Y')
              {echo "<a href=admin/problem_df_change.php?id=".$row['problem_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".("<span class=\"ui tiny red label\">미공개</span>")."</a>";}

              echo "<div class=\"show_tag_controled\" style=\"float: right; \">";
              //echo "<span class=\"ui header\">";
              echo  $view_problemset[$i][3];
              //echo "</span></div>";
	      echo "</div>";
            echo "</td>";
          echo "<td><a href=\"status.php?problem_id=".$row['problem_id']."&jresult=4\">".$row['accepted']."/".$row['submit']."</a></td>";
           // echo "<td><a href='status.php?problem_id=".$row['problem_id']."'>".$row['submit']."</a></td>";
            if ($row['submit'] == 0) {
    echo '<td><div class="progress" style="margin-bottom:-20px; "><div class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">0.000%</div></div></td>';
} else {
    $percentage = round(100 * $row['accepted'] / $row['submit'], 3);
    echo '<td><div class="progress" style="margin-bottom:-20px;"><div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%;">'.$percentage.'%</div></div></td>';
}
            echo  "</tr>";
            $i++;
          }
        ?>



    </tbody>
  </table><br>
<?php if (!isset($_GET['list'])){ ?>
  <div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
    ?>
<div style="text-align: center; ">

  <!-- 페이지네이션 -->
  <div class="ui pagination menu" style="box-shadow: none; ">
    <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page<>1) echo "problemset.php?page=".($page-1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_prev">  
      <i class="left chevron icon"></i>
    </a>
    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"problemset.php?page=".$i.htmlentities($postfix,ENT_QUOTES,'UTF-8')."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "problemset.php?page=".($page+1).htmlentities($postfix,ENT_QUOTES,'UTF-8'); ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>  
  </div>
</div>
<?php } ?>
<script type="text/javascript" src="include/jquery.tablesorter.js"></script>

</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
   
