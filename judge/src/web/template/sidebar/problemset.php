<?php $show_title="$MSG_PROBLEMS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <div class="ui grid" style="margin-bottom: 10px; ">
    <div class="row" style="white-space: nowrap; ">
      <div class="seven wide column">
          <form action="" method="get">
            <div class="ui search" style="width: 280px; height: 28px; margin-top: -5.3px;float:left ">
              <div class="ui left icon input" style="width: 100%; ">
                <input class="prompt" style="width: 100%; " type="text" placeholder=" <?php echo $MSG_TITLE;?> …" name="search"
		       value="<?php if(isset($_GET['search']))echo htmlentities($_GET['search'],ENT_QUOTES,'UTF-8') ?>"
                >

                <i class="search icon"></i>
              </div>
              <div class="results" style="width: 100%; "></div>
            </div>
          </form>

          <form action="problem.php" method="get">
            <div class="ui search" style="width: 120px; height: 28px; margin-top: -5.3px; ">
              <div class="ui icon input" style="width: 100%; ">
                <input class="prompt" style="width: 100%; " type="text" value="" placeholder="ID" name="id">
                <i class="search icon"></i>
              </div>
              <div class="results" style="width: 100%; "></div>
            </div>
          </form>

      </div>



      </div> 

    </div>
  </div>

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
  <div class="ui pagination menu" style="box-shadow: none; ">
    <a href="problemset.php?page=1" class="icon item">  
      <i class="fast backward icon"></i>
    </a>
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
    <a href="problemset.php?page=<?php echo $view_total_page?>" class="icon item">  
      <i class="fast forward icon"></i>
    </a>
  </div>
</div>
</div>
<?php } ?>


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
              {echo "<a href=admin/problem_df_change.php?id=".$row['problem_id']."&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">".("<span class=\"ui tiny red label\">未公开</span>")."</a>";}

              echo "<div class=\"show_tag_controled\" style=\"float: right; \">";
              //echo "<span class=\"ui header\">";
              // echo  $view_problemset[$i][3];
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
   
