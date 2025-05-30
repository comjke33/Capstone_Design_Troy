
<link rel="stylesheet" as="style" crossorigin href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard.css" />
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE"?>/css/semantic.min.js"></script>
<script src="<?php echo $path_fix."template/$OJ_TEMPLATE"?>/css/Chart.min.js"></script>
    <style>
    
    /* footer 스타일 정의 */
    .footer {
        line-height: 1.4285em;
        font-family: "Lato", "Noto Sans CJK SC", "Source Han Sans SC", "PingFang SC", "Hiragino Sans GB", "Microsoft Yahei", "WenQuanYi Micro Hei", "Droid Sans Fallback", "sans-serif";
        box-sizing: inherit;
        padding: 0 !important;
        border: none !important;
        color: #888;
        font-size: 1rem;
        margin: 35px 0 14px !important;
        position: relative;
        width: 100%;
        bottom: 0;
        background: none;
        border-radius: 0;
        box-shadow: none;
    }
    </style>
    <?php include(dirname(__FILE__)."/js.php");?>
    <div class="footer">
        <div class="ui center aligned container" title="이 정보를 삭제하려면 편집하십시오template/syzoj/footer.php" >
            <div><?php echo $domain==$DOMAIN?$OJ_NAME:ucwords($OJ_NAME)."'s OJ"?> is powered by <a style="color: inherit !important;" class=" " title="GitHub"
                    target="_blank" rel="noreferrer noopener" href="https://github.com/zhblue/hustoj">hustOj</a>, Theme
                by <a style="color: inherit !important;" href="https://github.com/syzoj">Troy</a></div>
                
        </div>
    </div>
    </div>
<?php if (isset($_SESSION[$OJ_NAME.'_user_id'])){ ?>
        <iframe id="sk" src="session.php" height=0px width=0px ></iframe>
<?php } ?>
<?php if (file_exists(dirname(__FILE__)."/css/$OJ_CSS")){ ?>
<link href="<?php echo $path_fix."template/$OJ_TEMPLATE"?>/css/<?php echo $OJ_CSS?>" rel="stylesheet">
<?php } ?>

</body>

</html>
