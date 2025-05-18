<?php
        require_once(dirname(__FILE__)."/../../include/memcache.php");
        function checkmail(){  // check if has mail
          global $OJ_NAME;
          $sql="select count(1) cnt FROM `mail` WHERE new_mail=1 AND `to_user`=?";
          $result=pdo_query($sql,$_SESSION[$OJ_NAME.'_'.'user_id']);
          if(empty($result)) return false;
          $row=$result[0];
          $retmsg="<span id=red>(".$row['cnt'].")</span>";
          return $retmsg;
        }

        function checknoti(){
            global $OJ_NAME;
            $sql = "SELECT EXISTS (
                SELECT 1 
                FROM user_weakness 
                WHERE user_id = ? 
                  AND mistake_count >= 15
            ) AS has_high_mistake;";
            $result=pdo_query($sql, $_SESSION[$OJ_NAME.'_'.'user_id']);
            $new_notification_count = $result[0]['has_high_mistake'];
            if(empty($result)) return false;
            return $new_notification_count;
        }

        $new_notification_count = checknoti();

        // 사이트 표시될 최신 뉴스 표시
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

        // 로그인 여부 확인, 안되있으면 다른페이지 접근할 때 로그인 페이지 리다이렉션
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

            // 저장된 뉴스 메뉴있으면 가져옴
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
<html lang="ko" style="position:fixed; width: 100%; overflow: hidden; ">

<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title><?php echo $show_title ?></title>

    <!-- css.php를 추가시키는 부분 -->
    <?php include(dirname(__FILE__)."/css.php");?>
        <style>
@media (max-width: 991px) {
        .mobile-only {
                display:block !important;
        }

        .desktop-only {
            display:none !important;
        }
}

/* 배경화면 수정부분 */

html, body {
  height: 100%; /* 전체 화면을 차지하도록 설정 */
  margin: 0; /* 기본 여백 제거 */
}

.padding {
  background: url('../../image/bg.jpg') no-repeat center center fixed !important; /* 배경 이미지 설정 */
  background-size: cover !important; /* 배경이 화면을 가득 채우도록 설정 */
  height: 100% !important; /* 배경을 화면 전체에 적용 */
  width: 100% !important; /* 배경이 화면의 너비를 채우도록 설정 */
  backdrop-filter: none !important;
  box-shadow: none !important;
  border: none !important;
}

.bell-wrapper {
    position: relative;
    display: inline-block;
}

.notification-dot {
    position: absolute;
    top: -5px;     /* 위로 5px 이동 */
    right: -5px;   /* 오른쪽으로 5px 이동 */
    width: 6px;    /* 크기 작게 */
    height: 6px;
    background-color: red;
    border-radius: 50%;
}

</style>
    <script src="<?php echo "$OJ_CDN_URL/include/"?>jquery-latest.js"></script>

<!-- Scripts -->
<script>
    console.log('\n %c HUSTOJ %c https://github.com/zhblue/hustoj %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', '');
    console.log('\n %c Theme By %c Baoshuo ( @renbaoshuo ) %c https://baoshuo.ren %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', 'background: #ffbf33; padding:5px 0;', '');
    console.log('\n GitHub Homepage: https://github.com/zhblue/hustoj \n Document: https://zhblue.github.io/hustoj \n Bug report URL: https://github.com/zhblue/hustoj/issues \n \n%c ★ Please give us a star on GitHub! ★ %c \n', 'color: red;', '')
</script>
</head>

<!-- ================================================= -->
<?php
// allowed_users.php 파일을 포함하여 배열을 불러옴
include('allowed_users.php');
?>
<!-- ================================================== -->

<?php
        if(!isset($_GET['spa'])){
?>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <!-- 워터마크 제거 후 배경화면 추가가  -->
   <body id="MainBg-C" style="position: relative; margin-top: 49px; height: calc(100% - 49px); overflow-y: overlay; background: url('../../image/bg.jpg') no-repeat center center fixed; background-size: cover !important;">
    

    <!-- 사이트 이름 표시, 메뉴 항목 링크제공 -->
    <div id="page-header" class="ui" style="position: fixed; height: 49px; z-index:99999">
        <div id="menu" class="menu-container">
            <a class="header item"  href="/"><span style="font-family: 'Exo 2'; font-size: 1.5em; font-weight: 600; "><?php echo $domain==$DOMAIN?$OJ_NAME:ucwords($OJ_NAME)."'s OJ"?></span></a>
            
          <?php
            if(isset($OJ_AI_HTML)&&$OJ_AI_HTML && !isset($OJ_ON_SITE_CONTEST_ID) ) echo $OJ_AI_HTML;
            else echo '<a class="desktop-only item" href="/"><i class="home icon"></i><span class="desktop-only">'.$MSG_HOME.'</span></a>';
            if(file_exists("moodle"))  // Moodle 디렉토리가 있으면 자동으로 링크 추가
            {
              echo '<a class="item" href="moodle"><i class="group icon"></i><span class="desktop-only">Moodle</span></a>';
            }
             if( !isset($OJ_ON_SITE_CONTEST_ID) && (!isset($_GET['cid'])||$cid==0) ){
          ?>

            <!-- 문제 -->
            <a class="item <?php if ($url=="problemset.php") echo "active";?>"
                href="<?php echo $path_fix?>problemset.php"><i class="list icon"></i><span class="desktop-only"><?php echo $MSG_PROBLEMS?></span></a>
            <!--//////////////////////////-->
            <!-- 소스/카테고리 제거-->
            <!--//////////////////////////-->
            <!-- 경진대회-->
            <a class="item <?php if ($url=="contest.php") echo "active";?>" href="<?php echo $path_fix?>contest.php<?php if(isset($_SESSION[$OJ_NAME."_user_id"])) echo "?my" ?>" ><i
                    class="trophy icon"></i><span class="desktop-only"> <?php echo $MSG_CONTEST?></span></a>
                    
            <!-- 채점기록 -->
            <a class="item <?php if ($url=="status.php") echo "active";?>" href="<?php echo $path_fix?>status.php"><i
                    class="tasks icon"></i><span class="desktop-only"><?php echo $MSG_STATUS?></span></a>

            <!-- 순위 -->
            <a class="item <?php if ($url=="ranklist.php") echo "active";?> "
                href="<?php echo $path_fix?>ranklist.php"><i class="signal icon"></i><span class="desktop-only"><?php echo $MSG_RANKLIST?></span></a>
                
            <!-- 최근 경진대회 -->
            <?php if(isset($OJ_RECENT_CONTEST)&&$OJ_RECENT_CONTEST){    ?>
                        <a class="item <?php if ($url=="recent-contest.php") echo "active";?> "
                            href="<?php echo $path_fix?>recent-contest.php"><i class="bullhorn icon"></i> <span class="desktop-only"><?php echo $MSG_RECENT_CONTEST?></span></a>
            <?php } ?>
            
            <!-- 문제해결 전략게시판 -->

            <!-- <a class="item <?php if ($url=="faqs.php") echo "active";?>" href="<?php echo $path_fix?>faqs.php"><i
                    class="help circle icon"></i><span class="desktop-only"> <?php echo $MSG_FAQ?></span></a>

            <a class="item <?php if ($url=="troy_debugging.php") echo "active";?>" href="<?php echo $path_fix?>troy_debugging.php"><i
            class="bug icon"></i><span class="desktop-only"> <?php echo "디버그용"?></span></a> -->
            

            <!-- 토론 게시판 -->
            <?php if (isset($OJ_BBS)&& $OJ_BBS){ ?>
                <a class='item' href="discuss.php"><i class="clipboard icon"></i> <span class="desktop-only"><php echo $MSG_BBS?></span></a>
            <?php }

            }
                ?>
            <?php if( isset($_GET['cid']) && intval($_GET['cid'])>0 ){
                     $cid=intval($_GET['cid']);
                     if(!isset($OJ_ON_SITE_CONTEST_ID)){   ?>
                            <a id="" class="item" href="<?php echo $path_fix?>contest.php" ><i class="arrow left icon"></i><span class="desktop-only"><?php echo $MSG_CONTEST.$MSG_LIST?></span></a>
            <?php    }      ?>
            <a id="" class="item active" href="<?php echo $path_fix?>contest.php?cid=<?php echo $cid?>" ><i class="list icon"></i><span class="desktop-only"><?php echo $MSG_PROBLEMS.$MSG_LIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>status.php?cid=<?php echo $cid?>" ><i class="tasks icon"></i><span class="desktop-only"><?php echo $MSG_STATUS.$MSG_LIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>contestrank.php?cid=<?php echo $cid?>" ><i class="numbered list icon"></i><span class="desktop-only"><?php echo $MSG_RANKLIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>contestrank-oi.php?cid=<?php echo $cid?>" ><i class="child icon"></i><span class="desktop-only">OI-<?php echo $MSG_RANKLIST?></span></a>
            <?php if (isset($OJ_BBS)&& $OJ_BBS){ ?>
                  <a class='item active' href="discuss.php?cid=<?php echo $cid?>"><i class="clipboard icon"></i> <span class="desktop-only"><?php echo $MSG_BBS?></span></a>
             <?php } ?>

                    <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])){ ?>
                            <a id="" class="item active" href="<?php echo $path_fix?>conteststatistics.php?cid=<?php echo $cid?>" ><i class="eye icon"></i><span class="desktop-only"><?php echo $MSG_STATISTICS?></span></a>
                    <?php }  ?>
            <?php }  ?>
            <?php echo $sql_news_menu_result_html; ?>


            <div class="right menu">
                <?php
                // allowed_user_id에 포함된 사용자만 알림 버튼을 보이도록 조건 추가
                if (in_array($_SESSION[$OJ_NAME.'_'.'user_id'], $allowed_user_id)) {
                    ?>
                    <a id="notification-link" class="item active" href="#">
                        <span class="bell-wrapper">
                            <i class="fa fa-bell"></i>
                            <?php if (isset($new_notification_count) && $new_notification_count > 0) { ?>
                                <span class="notification-dot"></span>
                            <?php } ?>
                        </span>
                        <span class="desktop-only"></span>
                    </a>
                    <?php
                }
                    ?>

                <!-- <a id="notification-link" class="item active" href="#">
                    <span class="bell-wrapper">
                        <i class="fa fa-bell"></i>
                        <?php if(isset($new_notification_count) && $new_notification_count > 0){ ?>
                            <span class="notification-dot"></span>
                        <?php } ?>
                    </span>
                    <span class="desktop-only"></span>
                </a> -->

                <script>
                document.getElementById("notification-link").addEventListener("click", function(e) {
                    e.preventDefault();  // 기본 이동 방지
                    console.log("클릭됨");

                    fetch("/check_notification.php")  // Python 실행 요청
                    .then(response => response.text())
                    .then(data => {
                        console.log("데이터:", data)
                        window.location.href = "notifications.php";
                        // Python 실행이 끝나면 이동
                        // setTimeout(function() {
                        //      window.location.href = "notifications.php";
                        //  }, 20000);  // 0.5초 정도의 딜레이를 추가 (필요에 따라 조정)
                    })
                    .catch(error => {
                        alert("오류 발생: " + error);
                    });
                });
                </script>


                <!-- 로그인한 사용자에 대한 정보를 표시합니다. 사용자 정보를 클릭하면 프로필 수정, 할 일 목록 등을 확인할 수 있는 메뉴를 제공합니다. -->
                <?php if(isset($_SESSION[$OJ_NAME.'_'.'user_id'])) { ?>
                <a href="<?php echo $path_fix?>/userinfo.php?user=<?php echo $_SESSION[$OJ_NAME.'_'.'user_id']?>"
                    style="color: inherit; ">
                    <div class="ui simple dropdown item">
                        <?php echo $_SESSION[$OJ_NAME.'_'.'user_id']; 
                              if(!empty($_SESSION[$OJ_NAME.'_nick'])) echo "(".$_SESSION[$OJ_NAME.'_nick'].")";
                              if(!empty($_SESSION[$OJ_NAME.'_group_name'])) echo "[".$_SESSION[$OJ_NAME.'_group_name']."]";
                        ?>
                        <i class="dropdown icon"></i>
                        <div class="menu">
                                <a class="item" href="modifypage.php"><i class="edit icon"></i><?php echo $MSG_REG_INFO;?></a>
                                <a class="item" href="portal.php"><i class="tasks icon"></i><?php echo $MSG_TODO;?></a>
                                <?php if ($OJ_SaaS_ENABLE){ ?>
                                <?php if($_SERVER['HTTP_HOST']==$DOMAIN)
                                        echo  "<a class='item' href='http://".  $_SESSION[$OJ_NAME.'_'.'user_id'].".$DOMAIN'><i class='globe icon' ></i>MyOJ</a>";?>
                                <?php } ?>
                            <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])||isset($_SESSION[$OJ_NAME.'_'.'user_adder'])||isset($_SESSION[$OJ_NAME.'_'.'password_setter'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])){ ?>
                            <a class="item" href="admin/"><i class="settings icon"></i><?php echo $MSG_ADMIN;?></a>
                            <?php }
if(isset($_SESSION[$OJ_NAME.'_'.'balloon'])){
  echo "<a class=item href='balloon.php'><i class='golf ball icon'></i>$MSG_BALLOON</a>";
}
                              if((isset($OJ_EXAM_CONTEST_ID)&&$OJ_EXAM_CONTEST_ID>0)||
                                     (isset($OJ_ON_SITE_CONTEST_ID)&&$OJ_ON_SITE_CONTEST_ID>0)||
                                     (isset($OJ_MAIL)&&!$OJ_MAIL)){
                                      // mail can not use in contest or mail is turned off
                              }else{
                                    $mail=checkmail();
                                    if($mail) echo "<a class='item mail' href=".$path_fix."mail.php><i class='mail icon'></i>$MSG_MAIL$mail</a>";
                              }
                            ?>
        <?php
        if(isset($OJ_PRINTER) && $OJ_PRINTER)
        {
        ?>
          <a  class="item"  href="printer.php">
            <i class="print icon"></i> <?php echo $MSG_PRINTER?>
          </a>
        <?php
        }
        ?>
                            <a class="item" href="logout.php"><i class="power icon"></i><?php echo $MSG_LOGOUT;?></a>
                        </div>
                    </div>
                </a>
                <?php } else { ?>


                <div class="item">
                    <a class="ui button" style="margin-right: 0.5em; " href="loginpage.php">
                       <?php echo $MSG_LOGIN?>
                    </a>
                    <?php if(isset($OJ_REGISTER)&&$OJ_REGISTER ){ ?>
                    <a class="ui primary button" href="registerpage.php">
                       <?php echo $MSG_REGISTER?>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div style="margin-top: 49px; ">
        <div id="main" class="ui main container">
<?php } ?>

<style>
.menu-container {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 1rem;
  background-color: transparent;
  font-size: 16px;
}

.menu-container a {
  color: #003366;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.4em; /* 아이콘과 텍스트 간격 */
}

.menu-container a:hover {
  color: #0078d7;
}

.right.menu {
  margin-left: auto !important;
  padding-right: 2rem !important;   /* 우측 내부 여백 추가 */
  display: flex !important;
  align-items: center !important;
  gap: 2rem !important;             /* 요소 간 간격 확장 */
  min-width: 250px !important;      /* 우측 영역 최소 너비 확보 */
}


</style>