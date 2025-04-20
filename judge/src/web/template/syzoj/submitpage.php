<?php $show_title = "$MSG_SUBMIT - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<style>
  #source {
    width: 80%; height: 600px;
    background-color: rgba(255, 225, 225, 0.5);
  }
  .ace-chrome .ace_marker-layer .ace_active-line {
    background-color: rgba(0, 0, 199, 0.3);
  }
  .ace_error_marker {
    position: absolute;
    background-color: rgba(255, 0, 0, 0.3);
  }
  .button, input, select, textarea {
    font-family: sans-serif;
    font-size: 150%;
    line-height: 1.2;
  }
</style>

<center>
<script src="<?php echo $OJ_CDN_URL ?>include/checksource.js"></script>
<form id="frmSolution" action="submit.php<?php if (isset($_GET['spa'])) echo '?spa'; ?>" method="post" onsubmit="do_submit()" enctype="multipart/form-data">

<?php if (!isset($_GET['spa']) || $solution_name) { ?>
  <input type="file" name="answer" placeholder="Upload answer file">
<?php } else { ?>
  <label>Language:
    <select id="language" name="language" onChange="reloadtemplate(this.value);">
      <?php
        $langmask = ($_GET['langmask'] ?? 0) | $OJ_LANGMASK;
        $lang = (~((int)$langmask)) & ((1 << count($language_ext)) - 1);
        foreach ($language_ext as $i => $ext) {
          if ($lang & (1 << $i)) {
            $selected = ($lastlang == $i) ? 'selected' : '';
            echo "<option value=\"$i\" $selected>{$language_name[$i]}</option>";
          }
        }
      ?>
    </select>
  </label>
  <?php if ($OJ_VCODE) { ?>
    <?php echo $MSG_VCODE ?>:
    <input name="vcode" size="4" type="text" autocomplete="off">
    <img id="vcode" alt="click to change" src="vcode.php" onclick="this.src='vcode.php?' + Math.random()">
  <?php } ?>
<?php } ?>

<button id="Submit" type="button" onclick="do_submit();"><?php echo $MSG_SUBMIT ?></button>
<label id="countDown"></label>
<?php if (!empty($OJ_ENCODE_SUBMIT)) { ?>
  <input type="button" value="Encoded <?php echo $MSG_SUBMIT ?>" onclick="encoded_submit();">
  <input type="hidden" id="encoded_submit_mark" name="reverse2" value="reverse" />
<?php } ?>
<?php if ($spj > 1 || !$OJ_TEST_RUN) { ?><span id="result"><?php echo $MSG_STATUS ?></span><?php } ?>

<?php if (!$solution_name) {
  if ($OJ_ACE_EDITOR) {
    $height = isset($OJ_TEST_RUN) && $OJ_TEST_RUN ? "400px" : "500px";
    echo "<pre id='source' style='width:90%;height:$height'>" . htmlentities($view_src, ENT_QUOTES, "UTF-8") . "</pre>";
    echo "<input type='hidden' id='hide_source' name='source' value='' />";
  } else {
    echo "<textarea id='source' name='source' style='width:80%;height:600px'>" . htmlentities($view_src, ENT_QUOTES, "UTF-8") . "</textarea>";
  }
} else {
  echo "<br><h2>指定上传文件：$solution_name</h2>";
} ?>

<?php if ($OJ_TEST_RUN && $spj <= 1 && !$solution_name) { ?>
  <div style="display: flex; justify-content: space-around; margin: 20px;">
    <div style="width: 40%;">
      <div><?php echo $MSG_Input ?></div>
      <textarea id="input_text" name="input_text" style="width:100%;"><?php echo $view_sample_input ?></textarea>
    </div>
    <div style="width: 40%;">
      <div><?php echo $MSG_Output ?><span id="result"></span></div>
      <textarea id="out" name="out" style="width:100%;background:white;" disabled placeholder='<?php echo htmlentities($view_sample_output, ENT_QUOTES, 'UTF-8') ?>'></textarea>
    </div>
    <input id="TestRun" type="button" value="<?php echo $MSG_TR ?>" onclick="do_test_run();" style="height: 130px; background-color: #22ba46a3;">
  </div>
<?php } ?>
<input type="hidden" id="problem_id" name="problem_id" value="0" />
</form>
<?php if (!empty($OJ_BLOCKLY)) { ?>
  <input id="blockly_loader" type="button" onclick="openBlockly()" value="<?php echo $MSG_BLOCKLY_OPEN ?>">
  <input id="transrun" type="button" onclick="loadFromBlockly()" value="<?php echo $MSG_BLOCKLY_TEST ?>" style="display:none;">
  <div id="blockly">Blockly</div>
<?php } ?>
</center>

<script src="<?php echo $OJ_CDN_URL ?>include/base64.js"></script>
<?php if (!empty($remote_oj)) echo "<iframe src='remote.php' height='0' width='0'></iframe>"; ?>
<?php if ($OJ_ACE_EDITOR) { ?>
<script src="<?php echo $OJ_CDN_URL ?>ace/ace.js"></script>
<script src="<?php echo $OJ_CDN_URL ?>ace/ext-language_tools.js"></script>
<script>
  ace.require("ace/ext/language_tools");
  var editor = ace.edit("source");
  editor.setTheme("ace/theme/xcode");
  switchLang(<?php echo $lastlang ?>);
  editor.setOptions({
    enableBasicAutocompletion: true,
    enableSnippets: true,
    enableLiveAutocompletion: true,
    fontSize: "18px"
  });
  editor.getSession().on("change", function() {
    Object.keys(editor.getSession().getMarkers(false)).forEach(id => {
      if (editor.getSession().getMarkers(false)[id].clazz === "ace_error_marker")
        editor.getSession().removeMarker(id);
    });
  });
  $(document).ready(function() {
    editor.resize();
  });
</script>
<?php } ?>
