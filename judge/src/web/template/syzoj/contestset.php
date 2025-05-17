<?php $show_title="$MSG_CONTEST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
<div class="ui grid" style="margin-bottom: 10px; ">
    <div class="row" style="white-space: nowrap; ">
      <div class="seven wide column">
          <form method="post" action="contest.php" class="ui form" style="margin-top: 2em; margin-bottom: 2em;">
            <div class="ui stackable grid">
              
              <!-- 검색창과 버튼 -->
              <div class="twelve wide column">
                <div class="ui fluid action input" style="max-width: 100%; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07); border-radius: 6px;">
                  <input 
                    class="prompt" 
                    type="text" 
                    name="keyword" 
                    value="<?php if (isset($_POST['keyword'])) echo htmlentities($_POST['keyword'], ENT_QUOTES, 'UTF-8'); ?>" 
                    placeholder="<?php echo $MSG_CONTEST_NAME ?> 검색…" 
                    style="border-radius: 6px 0 0 6px;"
                  >
                  <button type="submit" class="ui blue icon button" style="border-radius: 0 6px 6px 0;">
                    <i class="search icon"></i>
                  </button>
                </div>
              </div>

              <!-- 내 숙제/전체 보기 토글 버튼 -->
              <div class="four wide column right aligned">
                <?php if (isset($_GET['my'])) { ?>
                  <a href="contest.php" class="ui small basic grey button">
                    <i class="list icon"></i> <?php echo $MSG_VIEW_ALL_CONTESTS ?>
                  </a>
                <!-- <?php } else { ?>
                  <a href="contest.php?my" class="ui small basic teal button">
                    <i class="user icon"></i> 나의 숙제 경기 보기
                  </a>
                <?php } ?> -->
              </div>

            </div>
          </form>


      </div>

      <div class="nine wide right aligned column">

      </div>
    </div>
  </div>

      <div style="margin-bottom: 30px; ">
    
    <?php
      if(!isset($page)) $page=1;
      $page=intval($page);
      $section=8;
      $start=$page>$section?$page-$section:1;
      $end=$page+$section>$view_total_page?$view_total_page:$page+$section;
      $MY=isset($_GET['my'])?"&my":"";
    ?>

<!-- 페이지네이션 기능 대회 목록 탐색 -->
<!-- 총 페이지 개수
화면에 보여질 페이지 그룹
화면에 보여질 페이지의 첫번째 페이지 번호
화면에 보여질 페이지의 마지막 페이지 번호 -->
<div style="text-align: center; ">
  <div class="ui pagination menu" style="box-shadow: none; ">
    <a class="<?php if($page==1) echo "disabled "; ?>icon item" href="<?php if($page<>1) echo "contest.php?page=".strval($page-1).$MY ?>" id="page_prev">
      <i class="left chevron icon"></i>
    </a>
    <?php
      for ($i=$start;$i<=$end;$i++){
        echo "<a class=\"".($page==$i?"active ":"")."item\" href=\"contest.php?page=".$i.$MY."\">".$i."</a>";
      }
    ?>
    <a class="<?php if($page==$view_total_page) echo "disabled "; ?> icon item" href="<?php if($page<>$view_total_page) echo "contest.php?page=".strval($page+1).$MY; ?>" id="page_next">
    <i class="right chevron icon"></i>
    </a>
  </div>
</div>


</div>
    <!-- 대회 목록 출력 -->
    <table class="ui very basic center aligned table">
      <thead>
        <tr>
          <th><?php echo $MSG_CONTEST_ID?></th>
          <th><?php echo $MSG_CONTEST_NAME?></th>
          <th><?php echo $MSG_TIME?></th>
          <th><?php echo $MSG_CONTEST_OPEN?></th>
          <th><?php echo $MSG_CONTEST_CREATOR?></th>
        </tr>
      </thead>
      <tbody>
          <?php
            foreach($view_contest as $row){
              echo "<tr>";
              foreach($row as $table_cell){
                echo "<td>";
                echo "\t".$table_cell;
                echo "</td>";
              }
              echo "</tr>";
            }
          ?>
          
          

          <!-- <td><a href="<%= syzoj.utils.makeUrl(['contest', contest.id]) %>"><%= contest.title %> <%- tag %></a></td>
          <td><%= syzoj.utils.formatDate(contest.start_time) %></td>
          <td><%= syzoj.utils.formatDate(contest.end_time) %></td>
          <td class="font-content"><%- contest.subtitle %></td> -->
      </tbody>
    </table>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
