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
   <!-- 시스템 설명서 안내 (리팩토링 UI 적용) -->
   <div class="ui raised very padded text container segment" style="margin-top: 2em;">
  <h2 class="ui header" style="color:#003366;">
    <i class="book icon"></i>
    <div class="content">
      TROY OJ 사용 가이드
      <div class="sub header">단계별 풀이 · 문법 리포트 · 유사 문제 추천</div>
    </div>
  </h2>

  <div class="ui styled fluid accordion">
    <!-- 단계별 풀이 -->
    <div class="title active"><i class="dropdown icon"></i><b>📘 단계별 풀이 가이드</b></div>
    <div class="content active">
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item">문제 페이지의 <b>[단계적 풀기]</b> 버튼으로 학습을 시작하세요.</div>
        <div class="item"><code>#include &lt;stdio.h&gt;</code>는 자동 포함되므로 따로 선언하지 않아도 됩니다.</div>
        <div class="item ui warning message" style="color:#d72638; background-color: #fff8f6;">
          ⚠ <b>중요:</b> 조건문/반복문/함수 선언 시 <code>{</code>는 <u>직접 작성</u>, <code>}</code>는 <u>작성 금지</u> (자동 처리)
        </div>
        <div class="item"><b>Step 1:</b> 한 줄씩 입력 – 코드 의미 및 동작 이해</div>
        <div class="item"><b>Step 2:</b> 문단 단위 – 흐름 중심 연습</div>
        <div class="item"><b>Step 3:</b> 전체 구성 – 자신만의 스타일로 완성 (제출 없음)</div>
        <div class="item">Step 1·2 진행 시 좌측 <b>Flowchart</b>로 구조를 시각화합니다.</div>
        <div class="item"><b>[피드백 보기]</b> 버튼으로 AI 힌트를 받을 수 있습니다.</div>
        <div class="item ui negative message" style="background-color: #fff6f6;">
          ⚠ <b>주의:</b> <u>코드 스타일(들여쓰기, 줄바꿈 등)</u>은 정답 기준이 아니지만,<br />
          <span style="color: #c62828;">가이드라인의 <u>변수명 및 흐름을 따르지 않으면 오답 처리될 수 있습니다.</u></span>
        </div>
      </div>
    </div>

    <!-- 문법 오류 리포트 -->
    <div class="title"><i class="dropdown icon"></i><b>📊 개인별 문법 오류 리포트</b></div>
    <div class="content">
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item">우측 상단의 <b>종 아이콘</b> 클릭 → 문법 오류 리포트 확인</div>
        <div class="item">5일간 <b>15회 이상 제출</b> 시 AI가 주요 오류를 분석해 통계 제공합니다.</div>
        <div class="item">이 기능으로 <b>자신의 약한 개념을 파악하고 학습하세요.</b></div>
      </div>
    </div>

    <!-- 유사 문제 -->
    <div class="title"><i class="dropdown icon"></i><b>🔁 유사 문제 추천 및 문법 오류 확인</b></div>
    <div class="content">
      <div class="ui list" style="font-size: 1.1em; line-height: 1.8;">
        <div class="item"><b>정답 제출 시</b> → Codeup 유사 문제 풀이 페이지로 이동</div>
        <div class="item"><b>오답 제출 시</b> → <b>[문법 오류 확인]</b> 버튼 생성, 개념 링크 제공</div>
      </div>
    </div>
  </div>

  <div class="ui info message" style="margin-top: 2em;">
    <i class="info icon"></i>
    <b>이 기능들을 활용하여 실력을 체계적으로 쌓아보세요!</b>
  </div>
<script>
  $(document).ready(function () {
    $('.ui.accordion').accordion();
  });
</script>      <!-- 검색 -->
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

        //if(isset($NOIP_flag[0]) && $NOIP_flag[0]==0) {
          if(true){
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
<!-- jQuery 먼저 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Semantic UI JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>

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