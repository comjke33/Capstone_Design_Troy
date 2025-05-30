<?php $show_title="$MSG_LOGIN - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<!-- 에러 메세지 -->
<div class="ui error message" id="error" hidden></div>
<div class="ui middle aligned center aligned grid"  style="height: 500px;" >
  <div class="row">
    <div class="column" style="max-width: 450px">
      <h2 class="ui image header">
        <div class="content" style="margin-bottom: 10px; ">
          <?php echo $MSG_LOGIN ?>
        </div>
      </h2>

      <!-- 로그인 폼 -->
      <form class="ui large form" id="login" action="login.php" method="post" role="form" class="form-horizontal" onSubmit="return jsMd5();" >
        <div class="ui existing segment">
          <div class="field">
            <div class="ui left icon input">
              <i class="user icon"></i>

              <!-- ID -->
              <input name="user_id" placeholder="<?php echo $MSG_USER_ID ?>" type="text" id="username">
            </div>
          </div>
          <div class="field">
            <div class="ui left icon input">
              <i class="lock icon"></i>

              <!-- PASSWARD -->
              <input name="password" placeholder="<?php echo $MSG_PASSWORD ?>" type="password" id="password">
            </div>
          </div>

          <!-- 이미지 CAPTCHA 보안코드  -->
          <?php if($OJ_VCODE){?>
            <div class="field">
              <div class="ui left icon input">
                <i class="lock icon"></i>
                <input name="vcode" placeholder="<?php echo $MSG_VCODE ?>" type="text" autocomplete=off >
                <img id="vcode-img" onclick="this.src='vcode.php?'+Math.random()" height="30px">
              </div>
            </div>
          <?php }?>
          <button name="submit" type="submit" class="ui fluid large submit button" ><?php echo $MSG_LOGIN ?></button>
        </div>

        <div class="ui error message"></div>

      </form>

      <div class="ui message">
        
        <!-- 회원가입 및 비밀번호 찾기 링크 -->
        <?php if ($OJ_REGISTER){ ?>
                <a href="registerpage.php"><?php echo $MSG_REGISTER ?></a>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <?php } ?>

        <a href="lostpassword.php"><?php echo $MSG_LOST_PASSWORD ?></a>
      </div>
    </div>
  </div>
</div>
<script src="<?php echo $OJ_CDN_URL?>include/md5-min.js"></script>
<script>
  function jsMd5(){
    if($("input[name=password]").val()=="") return false;
    $("input[name=password]").val(hex_md5($("input[name=password]").val()));
    return true;
  }
</script>
<?php if ($OJ_VCODE) { ?>
    <script>
        $(document).ready(function () {
            $("#vcode-img").attr("src", "vcode.php?" + Math.random());
        })
    </script>
<?php } ?>


<?php include("template/$OJ_TEMPLATE/footer.php");?>
