<?php 
$show_title = "$MSG_SUBMIT - $OJ_NAME"; 
include("template/$OJ_TEMPLATE/header.php");
?>

<style>
#source {
    width: 80%;
    height: 600px;
    background-color: rgba(255,225,225,0.5);
}

.ace-chrome .ace_marker-layer .ace_active-line {
    background-color: rgba(0,0,199,0.3);
}

.button, input, optgroup, select, textarea {
    font-family: sans-serif;
    font-size: 150%;
    line-height: 1.2;
}

.ace_error_marker {
    position: absolute;
    background-color: rgba(255, 0, 0, 0.3);
}
</style>

<center>
<script src="<?php echo $OJ_CDN_URL ?>include/checksource.js"></script>

<form id="frmSolution" action="<?php echo isset($_GET['spa']) ? 'submit.php?spa' : 'submit.php'; ?>" method="post" enctype="multipart/form-data" onsubmit="do_submit();">
<?php if (!isset($_GET['spa']) || $solution_name) { ?>
    <input type='file' name='answer' id='answer_file'>
<?php } ?>

<?php if (isset($id)) { ?>
    <span style="color:#0000ff">Problem <b><?php echo $id ?></b></span>
    <input id="problem_id" type='hidden' value='<?php echo $id ?>' name="id" >
<?php } else { ?>
    Problem <span class=blue><b><?php echo chr($pid + ord('A')) ?></b></span> of Contest <span class=blue><b><?php echo $cid ?></b></span>
    <input id="cid" type='hidden' value='<?php echo $cid ?>' name="cid">
    <input id="pid" type='hidden' value='<?php echo $pid ?>' name="pid">
<?php } ?>

<span id="language_span">Language:
<select id="language" name="language" onChange="reloadtemplate($(this).val());">
<?php
$lang_count = count($language_ext);
if (isset($_GET['langmask']))
    $langmask = $_GET['langmask'];
else
    $langmask = $OJ_LANGMASK;
$lang = (~((int)$langmask)) & ((1 << ($lang_count)) - 1);
$lastlang = $_COOKIE['lastlang'] ?? 0;
for ($i = 0; $i < $lang_count; $i++) {
    if ($lang & (1 << $i))
        echo "<option value=$i " . ($lastlang == $i ? "selected" : "") . ">" . $language_name[$i] . "</option>";
}
?>
</select>
<?php if ($OJ_VCODE) { ?>
<?php echo $MSG_VCODE ?>:
<input name="vcode" size=4 type=text autocomplete=off ><img id="vcode" alt="click to change" src="vcode.php" onclick="this.src='vcode.php?'+Math.random()">
<?php } ?>

<button id="Submit" type="submit" class="ui primary icon button"><?php echo $MSG_SUBMIT?></button>
<label id="countDown"></label>
<?php if (isset($OJ_ENCODE_SUBMIT)&&$OJ_ENCODE_SUBMIT){?>
<input class="btn btn-success" title="WAF gives you reset ? try this." type=button value="Encoded <?php echo $MSG_SUBMIT?>" onclick="encoded_submit();">
<input type=hidden id="encoded_submit_mark" name="reverse2" value="reverse"/>
<?php }?>

<?php if ($spj>1 || !$OJ_TEST_RUN ){?>
<span class="btn" id=result><?php echo $MSG_STATUS?></span> 
<?php }?>
</span>

<?php 
if(!$solution_name){
    if($OJ_ACE_EDITOR){
        $height = isset($OJ_TEST_RUN) && $OJ_TEST_RUN ? "400px" : "500px";
        echo "<pre style=\"width:90%;height:$height\" id=\"source\">" . htmlentities($view_src, ENT_QUOTES, "UTF-8") . "</pre>";
        echo "<input type=hidden id=\"hide_source\" name=\"source\" value=\"\"/>";
    } else {
        echo "<textarea style=\"width:80%;height:600px\" id=\"source\" name=\"source\">" . htmlentities($view_src, ENT_QUOTES, "UTF-8") . "</textarea>";
    }
} else {
    echo "<br><h2>제출할 파일로 지정된 파일명: $solution_name</h2>";
}
?>

<?php if (isset($OJ_BLOCKLY)&&$OJ_BLOCKLY){?>
<input id="blockly_loader" type=button class="btn" onclick="openBlockly()" value="<?php echo $MSG_BLOCKLY_OPEN?>" style="color:white;background-color:rgb(169,91,128)">
<input id="transrun" type=button class="btn" onclick="loadFromBlockly() " value="<?php echo $MSG_BLOCKLY_TEST?>" style="display:none;color:white;background-color:rgb(90,164,139)">
<div id="blockly" class="center">
    <iframe name='frmBlockly' width=90% height=580 src='blockly/demos/code/index.html'></iframe>
</div>
<?php } ?>

<?php if (!empty($OJ_REMOTE_JUDGE)) { ?>
<iframe src="remote.php" height="0px" width="0px"></iframe>
<?php } ?>
</form>
</center>

<script src="<?php echo $OJ_CDN_URL?>include/base64.js"></script>
<?php if($OJ_ACE_EDITOR){ ?>
<script src="<?php echo $OJ_CDN_URL?>ace/ace.js"></script>
<script src="<?php echo $OJ_CDN_URL?>ace/ext-language_tools.js"></script>
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
    var markers = editor.getSession().getMarkers(false);
    for (var id in markers) {
        if (markers[id].clazz === "ace_error_marker") {
            editor.getSession().removeMarker(id);
        }
    }
});
</script>
<?php } ?>
<script>
document.getElementById("frmSolution").addEventListener("submit", function(e) {
    var fileInput = document.getElementById("answer_file");
    if (fileInput && fileInput.value === "") {
        e.preventDefault();
        alert("파일을 선택해주세요.");
    }
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>