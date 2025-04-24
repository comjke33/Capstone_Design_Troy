<?php
        require_once(dirname(__FILE__)."/../../include/memcache.php");
        function checkmail(){  // check if has mail
          global $OJ_NAME;
          $sql="SELECT count(1) FROM `mail` WHERE new_mail=1 AND `to_user`=?";
          $result=pdo_query($sql,$_SESSION[$OJ_NAME.'_'.'user_id']);
          if(!$result) return false;
          $row=$result[0];
          $retmsg="<span id=red>(".$row[0].")</span>";
          return $retmsg;
        }

        function get_menu_news() {
            $result = "";
            $sql_news_menu = "select `news_id`,`title` FROM `news` WHERE `menu`=1 AND `title`!='faqs.cn' ORDER BY `importance` ASC,`time` DESC LIMIT 10";
            $sql_news_menu_result = mysql_query_cache( $sql_news_menu );
            if ( $sql_news_menu_result ) {
                foreach ( $sql_news_menu_result as $row ) {
                    $result .= '<a class="item" href="/viewnews.php?id=' . $row['news_id'] . '">' ."<i class='star icon'></i>" . $row['title'] . '</a>';
                }
            }
            return $result;
        }
        $url=basename($_SERVER['REQUEST_URI']);
        $dir=basename(getcwd());
        if($dir=="discuss3") $path_fix="../";
        else $path_fix="";
        if(isset($OJ_NEED_LOGIN)&&$OJ_NEED_LOGIN&&(
                  $url!='loginpage.php'&&
                  $url!='lostpassword.php'&&
                  $url!='lostpassword2.php'&&
                  $url!='registerpage.php'
                  ) && !isset($_SESSION[$OJ_NAME.'_'.'user_id'])){

           header("location:".$path_fix."loginpage.php");
           exit();
        }

        if($OJ_ONLINE){
                require_once($path_fix.'include/online.php');
                $on = new online();
        }

        $sql_news_menu_result_html = "";

        if ($OJ_MENU_NEWS) {
            if ($OJ_REDIS) {
                $redis = new Redis();
                $redis->connect($OJ_REDISSERVER, $OJ_REDISPORT);

                if (isset($OJ_REDISAUTH)) {
                  $redis->auth($OJ_REDISAUTH);
                }
                $redisDataKey = $OJ_REDISQNAME . '_MENU_NEWS_CACHE';
                if ($redis->exists($redisDataKey)) {
                    $sql_news_menu_result_html = $redis->get($redisDataKey);
                } else {
                    $sql_news_menu_result_html = get_menu_news();
                    $redis->set($redisDataKey, $sql_news_menu_result_html);
                    $redis->expire($redisDataKey, 300);
                }

                $redis->close();
            } else {
                $sessionDataKey = $OJ_NAME.'_'."_MENU_NEWS_CACHE";
                if (isset($_SESSION[$sessionDataKey])) {
                    $sql_news_menu_result_html = $_SESSION[$sessionDataKey];
                } else {
                    $sql_news_menu_result_html = get_menu_news();
                    $_SESSION[$sessionDataKey] = $sql_news_menu_result_html;
                }
            }
        }
?>

<!DOCTYPE html>
<html lang="ko" style="position: fixed; width: 100%; overflow: hidden;">

<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title><?php echo $show_title ?></title>
    <?php include(dirname(__FILE__)."/css.php"); ?>
    <style>
        @media (max-width: 991px) {
            .mobile-only {
                display: block !important;
            }

            .desktop-only {
                display: none !important;
            }
        }

        /* 슬라이더 바 배경 색 변경 */
        .ui.sidebar {
            background-color: #88B3C2 !important; /* 슬라이더 바 배경 색 */
        }

        /* body와 html에 대해 전체 화면을 채우도록 설정 */
        html, body {
            height: 100%; /* 화면 전체를 차지하도록 설정 */
            margin: 0; /* 기본 여백 제거 */
        }

        /* 배경 이미지 설정 */
        .padding {
            background: url('../../image/bg.jpg') no-repeat center center fixed; /* 배경 이미지 설정 */
            background-size: cover; /* 배경 이미지가 화면을 가득 채우도록 설정 */
            height: 100%; /* 배경을 화면 전체에 적용 */
            width: 100%; /* 배경이 화면의 너비를 채우도록 설정 */
            backdrop-filter: none !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* 각 버튼 item 색상 설정 */
        .ui.sidebar .item.source {
            background-color: #6A98C3; /* 소스 버튼 색상 */
        }

        .ui.sidebar .item.category {
            background-color: #9A74B7; /* 카테고리 버튼 색상 */
        }

        /* 버튼에 마우스를 올렸을 때 색상 변경 */
        .ui.sidebar .item:hover {
            background-color: #51779A !important; /* 버튼에 마우스를 올렸을 때 색상 */
        }

    </style>
    <script src="<?php echo "$OJ_CDN_URL/include/" ?>jquery-latest.js"></script>

    <!-- Scripts -->
    <script>
        console.log('\n %c HUSTOJ %c https://github.com/zhblue/hustoj %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', '');
        console.log('\n %c Theme By %c Baoshuo ( @renbaoshuo ) %c https://baoshuo.ren %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', 'background: #ffbf33; padding:5px 0;', '');
        console.log('\n GitHub Homepage: https://github.com/zhblue/hustoj \n Document: https://zhblue.github.io/hustoj \n Bug report URL: https://github.com/zhblue/hustoj/issues \n \n%c ★ Please give us a star on GitHub! ★ %c \n', 'color: red;', '')
    </script>
</head>

<?php if (!isset($_GET['spa'])) 

{ ?>
   
   <body id="MainBg-C" style="position: relative; background-size: 100%">
    <div id="page-header" class="ui fixed borderless tiny thin inverted vertical menu" style="position: fixed; height: 100%; z-index: 99999">
        <div id="menu" class="ui stackable mobile ui container computer" style="margin-left:auto;margin-right:auto;">
            <a class="header item" href="/"><span style="font-family: 'Exo 2'; font-size: 1.5em; font-weight: 600; "><?php echo $domain == $DOMAIN ? $OJ_NAME : ucwords($OJ_NAME) . "'s OJ" ?></span></a>

            <!-- 메뉴 항목들 -->
            <a class="item" href="<?php echo $path_fix ?>problemset.php"><i class="list icon"></i><?php echo $MSG_PROBLEMS ?> </a>
            <a class="item" href="<?php echo $path_fix ?>category.php"><i class="globe icon"></i><?php echo $MSG_SOURCE ?></a>
            <a class="item" href="<?php echo $path_fix ?>contest.php"><i class="trophy icon"></i> <?php echo $MSG_CONTEST ?></a>

            <?php echo $sql_news_menu_result_html; ?>

            <div class="right menu">
                <?php if (isset($_SESSION[$OJ_NAME . '_' . 'user_id'])) { ?>
                <a href="<?php echo $path_fix ?>/userinfo.php?user=<?php echo $_SESSION[$OJ_NAME . '_' . 'user_id'] ?>" style="color: inherit;">
                    <div class="ui simple dropdown item">
                        <?php echo $_SESSION[$OJ_NAME . '_' . 'user_id']; ?>
                        <i class="dropdown icon"></i>
                        <div class="menu">
                            <a class="item" href="modifypage.php"><i class="edit icon"></i><?php echo $MSG_REG_INFO; ?></a>
                            <a class="item" href="portal.php"><i class="tasks icon"></i><?php echo $MSG_TODO; ?></a>
                        </div>
                    </div>
                </a>
                <?php } else { ?>
                <div class="item">
                    <a class="ui button" href="loginpage.php"><?php echo $MSG_LOGIN ?></a>
                    <?php if (isset($OJ_REGISTER) && $OJ_REGISTER) { ?>
                    <a class="ui primary button" href="registerpage.php"><?php echo $MSG_REGISTER ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div style="margin-left: 168px;">
        <div id="main" class="ui main container">
    <?php } ?>
            </div>
     <div class="footer" style="position:absolute;bottom:0;left:-10px">
        <div class="ui center aligned container" title="如果你想移除这个信息，请编辑template/siderbar/footer.php" >
            <div><?php echo $domain==$DOMAIN?$OJ_NAME:ucwords($OJ_NAME)."'s OJ"?> is powered by <a style="color: inherit !important;" class=" " title="GitHub"
                    target="_blank" rel="noreferrer noopener" href="https://github.com/zhblue/hustoj">HUSTOJ</a>, Theme
                by <a style="color: inherit !important;" href="https://github.com/syzoj">SYZOJ</a></div>
         <!--   <div> Running on <a href='https://debian.org' target='_blank'>Debian11</a> / <a href='https://www.loongson.cn' target='_blank'>Loongson 3A3000</a> </div> -->
            <?php if ($OJ_BEIAN) { ?>
            <div>
            <img src="image/icp.png">
                <a href="https://beian.miit.gov.cn/" style="text-decoration: none; color: #444444;"
                    target="_blank"><?php echo $OJ_BEIAN; ?></a>
            </div>
            <?php } ?>
        </div>
    </div>
        </div>
    </div>
    <div style="margin-left: 168px; ">
        <div id="main" class="ui main container">
<?php } ?>
