<?php
$show_title = "$MSG_STATUS - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");
?>
<script src="template/<?php echo $OJ_TEMPLATE ?>/js/textFit.min.js"></script>
<div class="padding">

<form id="simform" class="ui mini form" action="status.php" method="get">
  <div class="inline fields" style="margin-bottom: 25px; white-space: nowrap;">
    <label style="font-size: 1.2em; margin-right: 1px;"><?php echo $MSG_PROBLEM_ID ?>：</label>
    <div class="field"><input name="problem_id" style="width: 50px;" type="text" value="<?php echo isset($problem_id) ? htmlspecialchars($problem_id, ENT_QUOTES) : '' ?>"></div>

    <label style="font-size: 1.2em; margin-right: 1px;"><?php echo $MSG_USER ?>：</label>
    <div class="field"><input name="user_id" style="width: 50px;" type="text" value="<?php echo isset($user_id) ? htmlspecialchars($user_id, ENT_QUOTES) : '' ?>"></div>

    <label style="font-size: 1.2em; margin-right: 1px;"><?php echo $MSG_SCHOOL ?>：</label>
    <div class="field"><input name="school" style="width: 50px;" type="text" value="<?php echo isset($school) ? htmlspecialchars($school, ENT_QUOTES) : '' ?>"></div>

    <label style="font-size: 1.2em; margin-right: 1px;"><?php echo $MSG_GROUP_NAME ?>：</label>
    <div class="field"><input name="group_name" style="width: 50px;" type="text" value="<?php echo isset($group_name) ? htmlspecialchars($group_name, ENT_QUOTES) : '' ?>"></div>

    <label style="font-size: 1.2em; margin-right: 1px;"><?php echo $MSG_LANG ?>：</label>
    <select name="language" style="width: 110px; font-size: 1em">
      <option value="-1">All</option>
      <?php
      $selectedLang = isset($_GET['language']) ? intval($_GET['language']) : -1;
      $lang_count = count($language_ext);
      $langmask = $OJ_LANGMASK;
      $lang = (~((int)$langmask)) & ((1 << $lang_count) - 1);
      for ($i = 0; $i < $lang_count; $i++) {
        if ($lang & (1 << $i)) {
          $selected = $selectedLang == $i ? "selected" : "";
          echo "<option value='$i' $selected>{$language_name[$i]}</option>";
        }
      }
      ?>
    </select>

    <label style="font-size: 1.2em; margin-left: 10px;">상태：</label>
    <select name="jresult" style="width: 110px;">
      <?php
      $jresult_get = isset($_GET['jresult']) ? intval($_GET['jresult']) : -1;
      $jresult_get = ($jresult_get >= 12 || $jresult_get < 0) ? -1 : $jresult_get;
      echo "<option value='-1'" . ($jresult_get == -1 ? " selected" : "") . ">All</option>";
      for ($j = 0; $j < 12; $j++) {
        $i = ($j + 4) % 12;
        $selected = $i == $jresult_get ? "selected" : "";
        echo "<option value='$i' $selected>{$jresult[$i]}</option>";
      }
      ?>
    </select>

    <?php if (isset($_SESSION[$OJ_NAME . '_administrator']) || isset($_SESSION[$OJ_NAME . '_source_browser'])): ?>
      <?php $showsim = isset($_GET['showsim']) ? intval($_GET['showsim']) : 0; ?>
      <label style="font-size: 1.2em; margin-left: 10px;">코드 유사도：</label>
      <select name="showsim" onchange="document.getElementById('simform').submit();" style="width: 110px;">
        <?php foreach ([0, 80, 85, 90, 95, 100] as $sim): ?>
          <option value="<?php echo $sim ?>" <?php echo $showsim == $sim ? 'selected' : '' ?>><?php echo $sim === 0 ? 'All' : $sim ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>

    <button class="ui labeled icon mini green button" type="submit" style="margin-left: 20px;">
      <i class="search icon"></i><?php echo $MSG_SEARCH; ?>
    </button>
    <span class='ui mini grey button'>AWT:<?php echo round($avg_delay, 2) ?>s</span>
    <script>var AWT = <?php echo round($avg_delay * 500, 0) ?>;</script>
  </div>
</form>

<table id="result-tab" class="ui very basic center aligned table" style="white-space: nowrap;">
  <thead>
    <tr>
      <th class='desktop-only item'><?php echo $MSG_RUNID ?></th>
      <th><?php echo $MSG_USER ?></th>
      <th><?php echo $MSG_NICK ?></th>
      <th><?php echo $MSG_PROBLEM_ID ?></th>
      <th><?php echo $MSG_RESULT ?></th>
      <th><?php echo $MSG_MEMORY ?></th>
      <th><?php echo $MSG_TIME ?></th>
      <th><?php echo $MSG_LANG ?></th>
      <th class='desktop-only item'><?php echo $MSG_CODE_LENGTH ?></th>
      <th><?php echo $MSG_SUBMIT_TIME ?></th>
      <th><?php echo $MSG_FEEDBACK ?></th>
      <?php if (isset($_SESSION[$OJ_NAME . '_administrator'])): ?>
        <th class='desktop-only item'><?php echo $MSG_JUDGER ?></th>
      <?php endif; ?>
    </tr>
  </thead>
  <tbody style='font-weight:700'>
    <?php foreach ($view_status as $row): ?>
      <tr>
        <?php
        $i = 0;
        foreach ($row as $key => $cell):
          $class = ($i == 4) ? 'td_result' : (($i == 0 || ($i > 7 && $i != 9)) ? 'desktop-only item' : '');
          echo "<td class='$class'>$cell</td>";
          $i++;
        endforeach;

        // Feedback column
        $sid = htmlspecialchars($row['solution_id'], ENT_QUOTES);
        if ($row['result'] != 4) {
          echo "<td><a href='showfeedback.php?solution_id={$sid}' class='ui orange mini button'>피드백 보기</a></td>";
        } else {
          echo "<td>-</td>";
        }

        // Judger column (admin only)
        if (isset($_SESSION[$OJ_NAME . '_administrator'])) {
          echo "<td>{$row['judger']}</td>";
        }
        ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div style="text-align: center;">
  <div class="ui pagination menu">
    <a class="icon item" href="status.php?<?php echo $str2 ?>">Top</a>
    <?php
    if (isset($_GET['prevtop']))
      echo "<a class='item' href='status.php?{$str2}&top=" . intval($_GET['prevtop']) . "'>Prev</a>";
    else
      echo "<a class='item' href='status.php?{$str2}&top=" . ($top + 20) . "'>Prev</a>";
    ?>
    <a class="icon item" href="status.php?<?php echo $str2 ?>&top=<?php echo $bottom ?>&prevtop=<?php echo $top ?>">Next</a>
  </div>
</div>
</div>

<script>
  var judge_result = [<?php echo implode(',', array_map(fn($v) => "'" . $v . "'", $judge_result)) ?>, ''];
  var judge_color = [<?php echo implode(',', array_map(fn($v) => "'" . $v . "'", $judge_color)) ?>, ''];
  var oj_mark = '<?php echo $OJ_MARK ?>';
  var user_id = '<?php echo isset($_SESSION[$OJ_NAME.'_user_id']) ? $_SESSION[$OJ_NAME.'_user_id'] : '' ?>';
  var fancy_mp3 = '<?php echo $OJ_FANCY_RESULT && isset($_SESSION[$OJ_NAME.'_user_id']) ? $OJ_FANCY_MP3 : '' ?>';
</script>
<script src="template/<?php echo $OJ_TEMPLATE ?>/auto_refresh.js?v=0.522"></script>
<?php include("template/$OJ_TEMPLATE/footer.php"); ?>