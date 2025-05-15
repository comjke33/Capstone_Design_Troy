<?php $show_title="$MSG_HOME - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<!-- 외부 CSS 링크 (중복 head 제거) -->
<link rel="stylesheet" href="<?php echo "template/$OJ_TEMPLATE"; ?>/css/slide.css">

<div class="padding">
  <div class="ui three column grid" style="background-color: none;">
    <div class="eleven wide column">
      <?php if(file_exists("image/slide1.jpg")) { ?>
        <h4 class="ui top attached block header" style='margin-top: 10px;'><i class="th icon"></i><?php echo $OJ_NAME ?></h4>
        <div class="ui bottom attached center aligned segment carousel">
          <div class="carousel-arrow left" onclick="prevSlide()">&lt;</div>
          <?php for($i = 1; file_exists("image/slide$i.jpg"); $i++) { ?>
            <div class="carousel-slide <?php if($i==1) echo "active"; ?>" style="background-image: url('image/slide<?php echo $i ?>.jpg')"></div>
          <?php } ?>
          <div class="carousel-arrow right" onclick="nextSlide()">&gt;</div>
          <div class="carousel-dots">
            <?php for($j=0; $j<$i-1; $j++) { ?>
              <span class="carousel-dot <?php echo $j==0 ? 'active' : ''; ?>" data-index="<?php echo $j ?>"></span>
            <?php } ?>
          </div>
        </div>
      <?php } ?>

      <!-- <h4 class="ui top attached block header"><i class="ui info icon"></i><?php echo $MSG_NEWS; ?></h4> -->
      <!-- 그래프 부분 -->
      <!-- <div class="ui bottom attached segment">
        <table class="ui very basic table">
          <thead>
            <tr><th><?php echo $MSG_TITLE; ?></th><th><?php echo $MSG_TIME; ?></th></tr>
          </thead>
          <tbody>
            <?php
              $sql_news_main = "SELECT * FROM news WHERE defunct!='Y' AND title!='faqs.cn' ORDER BY importance DESC, time DESC LIMIT 10";
              $result_news = mysql_query_cache($sql_news_main);
              if ($result_news) {
                foreach ($result_news as $row) {
                  echo "<tr><td><a href='viewnews.php?id={$row['news_id']}'>{$row['title']}</a></td><td>{$row['time']}</td></tr>";
                }
              } else {
                echo "<tr><td colspan='2'>check database connection or account!</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div> -->

      <?php
        $month_id = mysql_query_cache("SELECT solution_id FROM solution WHERE in_date<DATE_ADD(CURDATE(), INTERVAL -DAY(CURDATE())+1 DAY) ORDER BY solution_id DESC LIMIT 1;");
        $month_id = (!empty($month_id) && isset($month_id[0][0])) ? $month_id[0][0] : 0;

        if(isset($NOIP_flag[0]) && $NOIP_flag[0]==0) {
          $view_month_rank = mysql_query_cache("SELECT user_id,nick,COUNT(DISTINCT problem_id) ac FROM solution WHERE solution_id>$month_id AND problem_id>0 AND user_id NOT IN ($OJ_RANK_HIDDEN) AND result=4 GROUP BY user_id,nick ORDER BY ac DESC LIMIT 10");
          if(!empty($view_month_rank)) {
      ?>
            <h4 class="ui top attached block header"><i class="ui star icon"></i>이달의 우수생</h4>
            <div class="ui bottom attached segment">
              <table class="ui very basic center aligned table">
                <tbody>
                <?php foreach ($view_month_rank as $row) {
                    echo "<tr><td><a href='userinfo.php?user=".htmlentities($row[0], ENT_QUOTES, "UTF-8")."'>".htmlentities($row[0], ENT_QUOTES, "UTF-8")."</a></td><td>{$row[1]}</td><td>{$row[2]}</td></tr>";
                } ?>
                </tbody>
              </table>
            </div>
      <?php }} ?>

      <!-- 공지사항 부분 -->
      <!-- <h4 class="ui top attached block header"><i class="ui star icon"></i><?php echo $OJ_INDEX_NEWS_TITLE; ?></h4>
      <div class="ui bottom attached segment">
        <table class="ui very basic left aligned table">
          <tbody>
            <?php
              $sql_news_index = "SELECT * FROM news WHERE defunct!='Y' AND title='$OJ_INDEX_NEWS_TITLE' ORDER BY importance ASC, time DESC LIMIT 10";
              $result_news = mysql_query_cache($sql_news_index);
              if ($result_news) {
                foreach ($result_news as $row) {
                  echo "<tr><td>".bbcode_to_html($row['content'])."</td></tr>";
                }
              }
            ?>
            <tr><td>
              <center>Recent submission: <?php echo $speed; ?>
              <div id="submission" style="width:80%;height:300px"></div></center>
            </td></tr>
          </tbody>
        </table>
      </div>
    </div> -->

    <!-- 오른쪽 사이드 섹션 -->
    <div class="right floated five wide column">

      <h4 class="ui top attached block header"><i class="ui rss icon"></i> <?php echo $MSG_RECENT_PROBLEM; ?> </h4>
      <div class="ui bottom attached segment">
        <table class="ui very basic center aligned table">
          <thead><tr><th><?php echo $MSG_TITLE; ?></th><th><?php echo $MSG_TIME; ?></th></tr></thead>
          <tbody>
            <?php
              $noip_problems = array_merge(...mysql_query_cache("SELECT problem_id FROM contest c LEFT JOIN contest_problem cp ON start_time<'$now' AND end_time>'$now' AND (c.title LIKE ? OR (c.contest_type & 20) > 0) AND c.contest_id=cp.contest_id", "%$OJ_NOIP_KEYWORD%"));
              $noip_problems = array_map('strval', array_unique($noip_problems));
              $user_id = $_SESSION[$OJ_NAME.'_user_id'] ?? 'guest';
              $sql_problems = "SELECT p.problem_id, title, max_in_date FROM (SELECT problem_id, MIN(result) best, MAX(in_date) max_in_date FROM solution WHERE user_id=? AND result>=4 AND problem_id>0 GROUP BY problem_id) s INNER JOIN problem p ON s.problem_id=p.problem_id WHERE s.best>4 ORDER BY max_in_date DESC LIMIT 5";
              $result_problems = mysql_query_cache($sql_problems, $user_id);
              if (!empty($result_problems)) {
                foreach ($result_problems as $row) {
                  if(in_array(strval($row['problem_id']), $noip_problems)) continue;
                  echo "<tr><td><a href='problem.php?id={$row['problem_id']}'>{$row['title']}</a></td><td>".substr($row['max_in_date'],5,5)."</td></tr>";
                }
              }
            ?>
          </tbody>
        </table>
      </div>

      <h4 class="ui top attached block header"><i class="ui search icon"></i><?php echo $MSG_SEARCH; ?></h4>
      <div class="ui bottom attached segment">
        <form action="problem.php" method="get">
          <div class="ui search">
            <div class="ui left icon input">
              <input class="prompt" type="text" placeholder="<?php echo $MSG_PROBLEM_ID; ?> …" name="id">
              <i class="search icon"></i>
            </div>
          </div>
        </form>
      </div>

      <h4 class="ui top attached block header"><i class="ui calendar icon"></i><?php echo $MSG_RECENT_CONTEST; ?></h4>
      <div class="ui bottom attached center aligned segment">
        <table class="ui very basic center aligned table">
          <thead><tr><th><?php echo $MSG_CONTEST_NAME; ?></th><th><?php echo $MSG_START_TIME; ?></th></tr></thead>
          <tbody>
            <?php
              $sql_contests = "SELECT * FROM contest WHERE defunct='N' ORDER BY contest_id DESC LIMIT 5";
              $result_contests = mysql_query_cache($sql_contests);
              foreach ($result_contests as $row) {
                echo "<tr><td><a href='contest.php?cid={$row['contest_id']}'>{$row['title']}</a></td><td>".substr($row['start_time'],5,5)."</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>

<?php if(file_exists("image/slide1.jpg")) { ?>
<script>
const slides = document.querySelectorAll('.carousel-slide');
const dots = document.querySelectorAll('.carousel-dot');
let currentIndex = 0;
let autoPlayInterval;

function showSlide(index) {
  slides.forEach((slide, i) => slide.classList.toggle('active', i === index));
  dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
}
function nextSlide() {
  currentIndex = (currentIndex + 1) % slides.length;
  showSlide(currentIndex);
}
function prevSlide() {
  currentIndex = (currentIndex - 1 + slides.length) % slides.length;
  showSlide(currentIndex);
}
autoPlayInterval = setInterval(nextSlide, 5000);
dots.forEach(dot => {
  dot.addEventListener('click', () => {
    currentIndex = parseInt(dot.dataset.index);
    showSlide(currentIndex);
    clearInterval(autoPlayInterval);
    autoPlayInterval = setInterval(nextSlide, 5000);
  });
});
document.querySelector('.carousel').addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
document.querySelector('.carousel').addEventListener('mouseleave', () => autoPlayInterval = setInterval(nextSlide, 5000));
</script>
<?php } ?>

<script src="<?php echo $OJ_CDN_URL ?>include/jquery.flot.js"></script>
<script>
$(function () {
  var d1 = <?php echo json_encode($chart_data_all ?? []); ?>;
  var d2 = <?php echo json_encode($chart_data_ac ?? []); ?>;
  $.plot($('#submission'), [
    { label: "<?php echo $MSG_SUBMIT ?>", data: d1, lines: { show: true } },
    { label: "<?php echo $MSG_SOVLED ?>", data: d2, bars: { show: true } }
  ], {
    grid: { backgroundColor: { colors: ["#fff", "#eee"] } },
    xaxis: { mode: "time" }
  });
});
</script>