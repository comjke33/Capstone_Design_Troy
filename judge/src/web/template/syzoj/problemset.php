<?php $show_title="$MSG_PROBLEMS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<!-- ID 검색 처리 -->
<?php
$single_result = null;
$searching_by_id = false;

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
  $searching_by_id = true;
  $id = $_GET['id'];
  $sql = "SELECT * FROM problem WHERE problem_id = ?";
  $single_result = pdo_query($sql, $id);

  if (empty($single_result)) {
    echo "<div class='ui red message'>문제 ID {$id}는 존재하지 않습니다.</div>";
    $searching_by_id = false;
  }
}
?>

<!-- 검색/이동 폼 -->
<div class="ui container" style="margin-top: 3em;">
  <div class="ui center aligned">
    <div style="display: inline-flex; gap: 10em;">
      <div class="ui center aligned">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 1em;">

          <!-- 제목/출처 검색 -->
          <form class="ui form" method="get" action="">
            <div class="ui action input" style="width: 500px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-radius: 6px;">
              <input type="text" name="search" placeholder="<?php echo $MSG_TITLE; ?> 또는 출처로 검색하세요…"
                     value="<?php if (isset($_GET['search'])) echo htmlentities($_GET['search'], ENT_QUOTES, 'UTF-8'); ?>"
                     style="border-radius: 6px 0 0 6px; width: calc(100% - 44px);">
              <button type="submit" class="ui icon button" style="border-radius: 0 6px 6px 0; background-color: #003366;">
                <i class="search icon" style="color: white;"></i>
              </button>
            </div>
          </form>

          <!-- ID 검색 -->
          <form class="ui form" method="get" action="">
            <div class="ui action input" style="width: 500px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); border-radius: 6px;">
              <input type="text" name="id" placeholder="문제 ID 입력…"
                     value="<?php if (isset($_GET['id'])) echo htmlentities($_GET['id'], ENT_QUOTES, 'UTF-8'); ?>"
                     style="border-radius: 6px 0 0 6px; width: calc(100% - 44px);">
              <button type="submit" class="ui icon button" style="border-radius: 0 6px 6px 0; background-color: #003366;">
                <i class="search icon" style="color: white;"></i>
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>

  <!-- 아래 여백 -->
  <div class="ui row">
    <div class="sixteen wide column">
      <div style="height: 60px; clear: both;"></div>
    </div>
  </div>

  <!-- 문제 리스트 테이블 -->
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

      $problem_list = $searching_by_id ? $single_result : $result;

      foreach ($problem_list as $row){
        echo "<tr>";
        if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])){
          if (isset($sub_arr[$row['problem_id']])){
            if (isset($acc_arr[$row['problem_id']])) 
              echo "<td><span class=\"status accepted\"><i class=\"checkmark icon\"></i></span></td>";
            else 
              echo "<td><span class=\"status wrong_answer\"><i class=\"remove icon\"></i></span></td>";
          } else {
            echo "<td><span class=\"status\"><i class=\"icon\"></i></span></td>";
          }
        }

        echo "<td><b>{$row['problem_id']}</b></td>";
        echo "<td class=\"left aligned\">";
        echo "<a href=\"problem.php?id={$row['problem_id']}\">{$row['title']}</a>";

        if ($row['defunct'] == 'Y' && isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
          echo " <a href=admin/problem_df_change.php?id={$row['problem_id']}&getkey=".$_SESSION[$OJ_NAME.'_'.'getkey'].">";
          echo "<span class=\"ui tiny red label\">미공개</span></a>";
        }

        echo "<div class=\"show_tag_controled\" style=\"float: right;\">".$view_problemset[$i][3]."</div>";
        echo "</td>";

        // echo "<td><a href=\"status.php?problem_id={$row['problem_id']}&jresult=4\">{$row['accepted']}/{$row['submit']}</a></td>";

        if ($row['submit'] == 0) {
          echo '<td><div class="progress" style="margin-bottom:-20px;"><div class="progress-bar progress-bar-danger" style="width:0%;">0.000%</div></div></td>';
        } else {
          $percentage = round(100 * $row['accepted'] / $row['submit'], 3);
          echo "<td><div class=\"progress\" style=\"margin-bottom:-20px;\"><div class=\"progress-bar progress-bar-success progress-bar-striped\" style=\"width:{$percentage}%\">{$percentage}%</div></div></td>";
        }

        echo "</tr>";
        $i++;
      }
    ?>
    </tbody>
  </table><br>

  <!-- 페이지네이션: ID 검색 중이 아니면 출력 -->
  <?php if (!$searching_by_id && !isset($_GET['list'])){ ?>
    <div style="margin-bottom: 30px; text-align: center;">
      <?php
        if(!isset($page)) $page=1;
        $page=intval($page);
        $section=8;
        $start=$page>$section?$page-$section:1;
        $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
      ?>
      <div class="ui pagination menu" style="box-shadow: none;">
        <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page>1) echo "problemset.php?page=".($page-1).htmlentities($postfix, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="left chevron icon"></i>
        </a>
        <?php
          for ($i=$start;$i<=$end;$i++){
            echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"problemset.php?page={$i}".htmlentities($postfix, ENT_QUOTES, 'UTF-8')."\">{$i}</a>";
          }
        ?>
        <a class="<?php if($page==$view_total_page) echo "disabled "; ?>icon item" href="<?php if($page<$view_total_page) echo "problemset.php?page=".($page+1).htmlentities($postfix, ENT_QUOTES, 'UTF-8'); ?>">
          <i class="right chevron icon"></i>
        </a>
      </div>
    </div>
  <?php } ?>

  <script type="text/javascript" src="include/jquery.tablesorter.js"></script>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
