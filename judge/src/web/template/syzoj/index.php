<?php $show_title="$MSG_HOME - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<!-- 외부 CSS 링크 (중복 head 제거) -->
<link rel="stylesheet" href="<?php echo "template/$OJ_TEMPLATE"; ?>/css/slide.css">

<div class="padding" style="padding: 0; background-color: transparent; background-image: url('../../image/bg.jpg');">
  <!-- 내용 -->
</div>

  <div class="ui grid">
    <div class="sixteen wide column">
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
    <!-- 📘 단계별 풀이 안내 (접기/펼치기 카드) -->
<div class="ui styled fluid accordion" style="margin-top: 1em;">
  <div class="title active">
    <i class="dropdown icon"></i>
    🧠 시스템 이용 가이드 (단계별 풀이 / 리포트 / 유사문제)
  </div>
  <div class="content active" style="line-height: 1.6;">
    <p><strong>📌 단계별 풀이 기능</strong></p>
    <ul>
      <li><strong>Step 1:</strong> 한 줄씩 풀이 – 코드의 의미와 동작 이해</li>
      <li><strong>Step 2:</strong> 문단 단위 풀이 – 흐름 중심의 학습</li>
      <li><strong>Step 3:</strong> 전체 블록 직접 구현 – 자유로운 스타일 작성</li>
      <li><span style="color:#0077cc;">※ 중괄호 `{}`는 여는 괄호만 작성하세요.</span></li>
      <li>좌측에 흐름도(Flowchart)가 자동 생성되어 구조 이해에 도움을 줍니다.</li>
      <li>피드백 보기 버튼으로 AI 힌트도 확인할 수 있습니다.</li>
    </ul>

    <p><strong>📊 개인별 문법 리포트</strong></p>
    <ul>
      <li>우측 상단 종 아이콘 클릭 시, 최근 자주 틀린 문법을 확인할 수 있습니다.</li>
      <li>5일간 15회 이상 제출 시 자동 분석됩니다.</li>
    </ul>

    <p><strong>🔁 유사 문제 추천 / 문법 오류 확인</strong></p>
    <ul>
      <li>정답 제출 시, Codeup의 유사 문제 풀이 페이지로 연결됩니다.</li>
      <li>오답 시, 문법 오류 분석과 함께 관련 개념 링크가 제공됩니다.</li>
    </ul>
  </div>
</div>

<script>
  $('.ui.accordion').accordion();  // Semantic UI accordion 초기화
</script>
      <!-- 검색 -->
    <h4 class="ui top attached block header" style="color: white;">
      <i class="ui search icon"></i> 문제 검색
    </h4>
    <div class="ui bottom attached segment">
      <form action="problem.php" method="get" class="ui form" onsubmit="return validateForm()">
        <div class="ui fluid action input">
          <input type="text" id="problemInput" name="id" placeholder="문제 ID 또는 번호를 입력하세요…" />
          <button type="submit" class="ui icon button" style="border-radius: 0 6px 6px 0; background-color: #003366;">
            <i class="search icon" style="color: white;"></i>
          </button>
        </div>
        <div id="warningMessage" style="display:none; color:red; margin-top:10px;">재입력을 하세요</div>
      </form>
    </div>

      <!-- 최근 문제 -->
      <h4 class="ui top attached block header" style="color: white;"><i class="ui rss icon"></i> <?php echo $MSG_RECENT_PROBLEM; ?> </h4>
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

      <!-- 이달의 우수생 -->
      <?php
        $month_id = mysql_query_cache("SELECT solution_id FROM solution WHERE in_date<DATE_ADD(CURDATE(), INTERVAL -DAY(CURDATE())+1 DAY) ORDER BY solution_id DESC LIMIT 1;");
        $month_id = (!empty($month_id) && isset($month_id[0][0])) ? $month_id[0][0] : 0;

        if(isset($NOIP_flag[0]) && $NOIP_flag[0]==0) {
          $view_month_rank = mysql_query_cache("SELECT user_id,nick,COUNT(DISTINCT problem_id) ac FROM solution WHERE solution_id>$month_id AND problem_id>0 AND user_id NOT IN ($OJ_RANK_HIDDEN) AND result=4 GROUP BY user_id,nick ORDER BY ac DESC LIMIT 5"); //상위 5명만 출력
          if(!empty($view_month_rank)) {
      ?>
            <h4 class="ui top attached block header" style="color: white;"><i class="ui star icon"></i>이달의 우수생</h4>
                  <div class="ui bottom attached segment">
        <table class="ui very basic center aligned table">
          <thead>
            <tr>
              <th>순위</th>
              <th>사용자 ID</th>
              <th>닉네임</th>
              <th>정답 문제 수</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $medals = ['🥇', '🥈', '🥉'];
            $rank = 1;
            foreach ($view_month_rank as $row) {
                $user_id = htmlentities($row[0], ENT_QUOTES, "UTF-8");
                $nick = htmlentities($row[1], ENT_QUOTES, "UTF-8");
                $ac_count = intval($row[2]);
                $medal = ($rank <= 3) ? $medals[$rank - 1] : $rank;

                echo "<tr>
                        <td>{$medal}</td>
                        <td><a href='userinfo.php?user={$user_id}'>{$user_id}</a></td>
                        <td>{$nick}</td>
                        <td>{$ac_count}</td>
                      </tr>";
                $rank++;
            }
            ?>
          </tbody>
        </table>
      </div>

      <?php }} ?>


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

//검색창 빈 제출 입력란 처리
 function validateForm() {
    const input = document.getElementById("problemInput").value.trim();
    const warning = document.getElementById("warningMessage");
    
    if (input === "") {
      warning.style.display = "block";
      return false; // 제출 막기
    } else {
      warning.style.display = "none";
      return true; // 정상 제출
    }
  }

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

  // 조건을 추가하여 차트가 그려지지 않도록 함
  if (false) {  // false 조건을 넣어 차트를 그리지 않도록 함
    $.plot($('#submission'), [
      { label: "<?php echo $MSG_SUBMIT ?>", data: d1, lines: { show: true } },
      { label: "<?php echo $MSG_SOVLED ?>", data: d2, bars: { show: true } }
    ], {
      grid: { backgroundColor: { colors: ["#fff", "#eee"] } },
      xaxis: { mode: "time" }
    });
  }
});


</script>