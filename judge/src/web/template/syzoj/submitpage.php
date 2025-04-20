<?php $show_title="$MSG_SUBMIT - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<style>
#source {
    width: 80%;
    height: 600px;
    background-color: rgba(255, 225, 225, 0.5);
}

.ace-chrome .ace_marker-layer .ace_active-line {
    background-color: rgba(0, 0, 199, 0.3);
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

<script src="<?php echo \$OJ_CDN_URL ?>include/checksource.js"></script>

<form id="frmSolution" action="submit.php<?php if (isset($_GET['spa'])) echo "?spa"; ?>" method="post" onsubmit="do_submit()" enctype="multipart/form-data">

<?php if (!isset($_GET['spa']) || \$solution_name) { ?>
    <input type="file" name="answer" placeholder="Upload answer file">
<?php } else { ?>

    <span id="language_span">Language:
        <select id="language" name="language" onChange="reloadtemplate($(this).val());">
            <?php
            \$lang_count = count(\$language_ext);
            \$langmask = isset($_GET['langmask']) ? $_GET['langmask'] : 0;
            \$langmask |= \$OJ_LANGMASK;

            \$lang = (~((int)\$langmask)) & ((1 << \$lang_count) - 1);

            for (\$i = 0; \$i < \$lang_count; \$i++) {
                if (\$lang & (1 << \$i)) {
                    \$selected = (\$lastlang == \$i) ? 'selected' : '';
                    echo "<option value=\"\$i\" \$selected>{\$language_name[\$i]}</option>";
                }
            }
            ?>
        </select>
    </span>

    <?php if (\$OJ_VCODE) { ?>
        <?php echo \$MSG_VCODE ?>:
        <input name="vcode" size="4" type="text" autocomplete="off">
        <img id="vcode" alt="click to change" src="vcode.php" onclick="this.src='vcode.php?' + Math.random()">
    <?php } ?>

<?php } ?>

<button id="Submit" type="button" class="ui primary icon button" onclick="do_submit();"><?php echo \$MSG_SUBMIT?></button>
<label id="countDown"></label>
<?php if (isset(\$OJ_ENCODE_SUBMIT) && \$OJ_ENCODE_SUBMIT) { ?>
<input class="btn btn-success" title="WAF gives you reset ? try this." type=button value="Encoded <?php echo \$MSG_SUBMIT ?>" onclick="encoded_submit();">
<input type=hidden id="encoded_submit_mark" name="reverse2" value="reverse"/>
<?php } ?>

<?php if (\$spj > 1 || !\$OJ_TEST_RUN) { ?>
<span class="btn" id="result"><?php echo \$MSG_STATUS ?></span>
<?php } ?>
</span>

<?php 
if (!\$solution_name) {
    if (\$OJ_ACE_EDITOR) {
        \$height = isset(\$OJ_TEST_RUN) && \$OJ_TEST_RUN ? "400px" : "500px";
        echo "<pre style=\"width:90%;height:\$height\" id=\"source\">" . htmlentities(\$view_src, ENT_QUOTES, "UTF-8") . "</pre>";
        echo "<input type=hidden id=\"hide_source\" name=\"source\" value=\"\"/>";
    } else {
        echo "<textarea style=\"width:80%;height:600px\" id=\"source\" name=\"source\">" . htmlentities(\$view_src, ENT_QUOTES, "UTF-8") . "</textarea>";
    }
} else {
    echo "<br><h2>指定上传文件：\$solution_name</h2>";
}
?>

<div class="row">
<div class="column" style="display: flex;">
<?php if (isset(\$OJ_TEST_RUN) && \$OJ_TEST_RUN && \$spj <= 1 && !\$solution_name) { ?>
    <div style="margin-left: 60px; width: 40%; padding: 14px; flex-direction: column;">
        <div style="display: flex; border-radius: 8px; background-color: rgba(255,255,255,0.4);" id="language_span"><?php echo \$MSG_Input ?></div>
        <textarea style="width:100%" id="input_text" name="input_text"><?php echo \$view_sample_input ?></textarea>
    </div>
    <div style="width: 40%; flex-direction: column;">
        <div style="display: flex; border-radius: 8px; background-color: rgba(255,255,255,0.4); justify-content: space-between;" id="language_span"><?php echo \$MSG_Output ?>
        <span class="btn" id="result"><?php echo \$MSG_STATUS ?></span>
        </div>
        <textarea style="width:100%;background-color: white;" id="out" name="out" disabled placeholder='<?php echo htmlentities(\$view_sample_output, ENT_QUOTES, 'UTF-8') ?>'></textarea>
    </div>
<?php } ?>

<?php if (isset(\$OJ_TEST_RUN) && \$OJ_TEST_RUN && \$spj <= 1 && !\$solution_name) { ?>
    <input style="margin-top: 30px; margin-left: auto; width: 7%; background-color: #22ba46a3; border-color: #00fff470; height: 130px;" id="TestRun" class="btn btn-info" type=button value="<?php echo \$MSG_TR ?>" onclick="do_test_run();">
<?php } ?>
</div>
</div>
<input type="hidden" value="<?php echo isset(\$id) ? \$id : 0 ?>" id="problem_id" name="problem_id"/>
</form>

<?php if (isset(\$OJ_BLOCKLY) && \$OJ_BLOCKLY) { ?>
    <input id="blockly_loader" type=button class="btn" onclick="openBlockly()" value="<?php echo \$MSG_BLOCKLY_OPEN ?>" style="color:white;background-color:rgb(169,91,128)">
    <input id="transrun" type=button class="btn" onclick="loadFromBlockly()" value="<?php echo \$MSG_BLOCKLY_TEST ?>" style="display:none;color:white;background-color:rgb(90,164,139)">
    <div id="blockly" class="center">
        <iframe name='frmBlockly' width=90% height=580 src='blockly/demos/code/index.html'></iframe>
    </div>
<?php } ?>

<?php if (!empty(\$OJ_REMOTE_JUDGE)) { ?>
    <iframe src="remote.php" height="0" width="0"></iframe>
<?php } ?>

</center>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>