<?php $show_title="$MSG_STATUS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<script src="template/<?php echo $OJ_TEMPLATE?>/js/textFit.min.js"></script>

<div class="ui fluid container" style="padding-top: 2em; padding-bottom: 2em; max-width: 1400px;">


  <!-- 검색 필터 폼 시작 -->
  <form id="simform" class="ui form" method="get" action="status.php">
    <div class="ui stackable equal width grid">

      <!-- 문제 번호 입력 -->
      <div class="column">
        <div class="field">
          <input type="text" name="problem_id" placeholder="<?php echo $MSG_PROBLEM_ID ?>" value="<?php echo isset($problem_id)?htmlspecialchars($problem_id, ENT_QUOTES):'' ?>">
        </div>
      </div>

      <!-- 사용자 ID 입력 -->
      <div class="column">
        <div class="field">
          <input type="text" name="user_id" placeholder="<?php echo $MSG_USER ?>" value="<?php echo isset($user_id)?htmlspecialchars($user_id, ENT_QUOTES):'' ?>">
        </div>
      </div>

      <!-- 학교 이름 입력 -->
      <!-- <div class="column">
        <div class="field">
          <input type="text" name="school" placeholder="<?php echo $MSG_SCHOOL ?>" value="<?php echo isset($school)?htmlspecialchars($school, ENT_QUOTES):'' ?>">
        </div>
      </div> -->

      <!-- 그룹 이름 입력 -->
      <!-- <div class="column">
        <div class="field">
          <input type="text" name="group_name" placeholder="<?php echo $MSG_GROUP_NAME ?>" value="<?php echo isset($group_name)?htmlspecialchars($group_name, ENT_QUOTES):'' ?>">
        </div>
      </div> -->

      <!-- 언어 필터 드롭다운 -->
      <div class="column">
        <div class="field">
          <select name="language" class="ui fluid dropdown">
            <option value="-1"><?php echo $MSG_LANG ?>: All</option>
            <?php
              // 사용 가능한 언어만 출력
              $selectedLang = isset($_GET['language']) ? intval($_GET['language']) : -1;
              $lang_count = count($language_ext);
              $langmask = $OJ_LANGMASK;
              $lang = (~((int)$langmask)) & ((1 << ($lang_count)) - 1);
              for($i = 0; $i < $lang_count; $i++) {
                if($lang & (1 << $i)) {
                  echo "<option value='$i'".($selectedLang == $i ? " selected" : "").">$language_name[$i]</option>";
                }
              }
            ?>
          </select>
        </div>
      </div>

      <!-- 채점 결과 필터 드롭다운 -->
      <div class="column">
        <div class="field">
          <select name="jresult" class="ui fluid dropdown">
            <option value="-1"><?php echo $MSG_RESULT ?>: All</option>
            <?php
              // 채점 결과 순서대로 출력
              $jresult_get = isset($_GET['jresult']) ? intval($_GET['jresult']) : -1;
              for ($j = 0; $j < 12; $j++) {
                $i = ($j + 4) % 12;
                echo "<option value='$i'".($i == $jresult_get ? " selected" : "").">$jresult[$i]</option>";
              }
            ?>
          </select>
        </div>
      </div>

      <!-- 관리자만 볼 수 있는 코드 유사도 필터 -->
      <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'source_browser'])) { ?>
      <div class="column">
        <div class="field">
          <select name="showsim" class="ui fluid dropdown" onchange="document.getElementById('simform').submit();">
            <option value=""><?php echo "코드 유사도"; ?></option>
            <?php
              $showsim = isset($_GET['showsim']) ? intval($_GET['showsim']) : 0;
              foreach ([0, 80, 85, 90, 95, 100] as $val) {
                echo "<option value='$val'".($val == $showsim ? " selected" : "").">$val</option>";
              }
            ?>
          </select>
        </div>
      </div>
      <?php } ?>

      <!-- 검색 버튼과 평균 대기 시간 표시 -->
      <div class="sixteen wide column" style="margin-top: 1em;">
        <button type="submit" class="ui blue labeled icon button">
          <i class="search icon"></i> <?php echo $MSG_SEARCH ?>
        </button>
        <span class="ui basic grey label">AWT: <?php echo round($avg_delay, 2) ?>s</span>
        <script>var AWT = <?php echo round($avg_delay*500, 0); ?>;</script>
      </div>
    </div>
  </form>
  <!-- 검색 필터 폼 끝 -->

<!-- 채점 결과 테이블 -->
<table class="ui celled striped compact center aligned table" id="result-tab">
  <thead>
    <tr>
      <th><?php echo $MSG_RUNID ?></th>
      <th><?php echo $MSG_USER ?></th>
      <th><?php echo $MSG_NICK ?></th>
      <th><?php echo $MSG_PROBLEM_ID ?></th>
      <th><?php echo $MSG_RESULT ?></th>
      <th><?php echo $MSG_MEMORY ?></th>
      <th><?php echo $MSG_TIME ?></th>
      <th><?php echo $MSG_LANG ?></th>
      <th><?php echo $MSG_CODE_LENGTH ?></th>
      <th><?php echo $MSG_SUBMIT_TIME ?></th>
      <?php if (!isset($cid)) echo "<th>$MSG_FEEDBACK</th>"; ?>
      <?php if (!isset($cid) && isset($_SESSION[$OJ_NAME.'_'.'administrator'])) echo "<th>$MSG_JUDGER</th>"; ?>
    </tr>
  </thead>
  <tbody style="font-weight: 500; font-size: 14px;">
    <?php
      // 채점 결과 행 반복 출력
      foreach ($view_status as $row) {
        echo "<tr>";
        $i = 0;
        foreach ($row as $cell) {
          // 결과 컬럼은 특별 스타일 적용
          $class = ($i == 4) ? "td_result" : (($i == 0 || $i > 7 && $i != 9) ? "desktop-only item" : "");
          echo "<td class='$class'>" . $cell . "</td>";
          $i++;
        }
        echo "</tr>";
      }
    ?>
  </tbody>
</table>


  <!-- 페이지 이동 버튼 -->
  <div class="ui center aligned segment" style="margin-top: 2em;">
    <div class="ui pagination menu">
      <a class="icon item" href="<?php echo 'status.php?'.$str2; ?>">Top</a>
      <?php
        if (isset($_GET['prevtop']))
          echo "<a class='item' href='status.php?$str2&top=".intval($_GET['prevtop'])."'>Prev</a>";
        else
          echo "<a class='item' href='status.php?$str2&top=".($top+20)."'>Prev</a>";
      ?>
      <a class="icon item" href="<?php echo 'status.php?'.$str2.'&top='.$bottom.'&prevtop='.$top; ?>">Next</a>
    </div>
  </div>
</div>

<!-- 판정 상태와 스타일 관련 스크립트 변수 정의 -->
<script>
  var judge_result = [<?php foreach ($judge_result as $res) echo "'$res',"; ?> ''];
  var judge_color = [<?php foreach ($judge_color as $col) echo "'$col',"; ?> ''];
  var oj_mark = '<?php echo $OJ_MARK ?>';
  var user_id = "<?php echo isset($_SESSION[$OJ_NAME.'_user_id']) && $OJ_FANCY_RESULT ? $_SESSION[$OJ_NAME.'_user_id'] : ''; ?>";
  var fancy_mp3 = "<?php echo isset($_SESSION[$OJ_NAME.'_user_id']) && $OJ_FANCY_RESULT ? $OJ_FANCY_MP3 : ''; ?>";
</script>

<!-- 자동 새로고침 JS 로딩 -->
<script src="template/<?php echo $OJ_TEMPLATE ?>/auto_refresh.js?v=0.522"></script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
