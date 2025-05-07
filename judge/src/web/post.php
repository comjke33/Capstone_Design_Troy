<?php
require_once("discuss_func.inc.php");
require_once("include/db_info.inc.php");
require_once("include/my_func.inc.php");

if(!isset($_SESSION[$OJ_NAME.'_'.'user_id'])){
  $view_errors="<a href=loginpage.php>Please Login First</a>";
  require("template/".$OJ_TEMPLATE."/error.php");
  exit(0);
}
if(strlen($_POST['content'])>5000){
  $view_errors="Your contents is too long!";
  require("template/".$OJ_TEMPLATE."/error.php");
  exit(0);
}
if(strlen($_POST['title'])>60){
  require_once("oj-header.php");
  echo "Your title is too long!";
 // require_once("../oj-footer.php");
  exit(0);
}
if(has_bad_words($_POST['content'])){
    $view_errors="请文明上网！";
    require("template/".$OJ_TEMPLATE."/error.php");
    exit(0);
}
if(has_bad_words($_POST['title'])){
    $view_errors="请文明上网！";
    require("template/".$OJ_TEMPLATE."/error.php");
    exit(0);
}

$tid = null;
if($_REQUEST['action']=='new'){
    if(isset($_POST['title']) && isset($_POST['content']) && $_POST['title']!='' && $_POST['content']!=''){
        if(isset($_REQUEST['pid']) && $_REQUEST['pid']!='')
          $pid = intval($_REQUEST['pid']);
        else
          $pid = 0;
        
        if(isset($_REQUEST['cid'])&&$_REQUEST['cid']!='')
          $cid = intval($_REQUEST['cid']);
        else
          $cid = 0;
    
        if($pid==0){
            if($cid>0){
                $problem_id = htmlentities($_POST['pid'],ENT_QUOTES,'UTF-8');
                //echo "problem_id:".$problem_id;
                $num = strpos($PID,$problem_id);
                //echo "num:$num";
                $pid = pdo_query("select problem_id from contest_problem where contest_id=? and num=?",$cid,$num)[0][0];
                //echo "pid:$pid";
            }
        }
    
        $sql = "INSERT INTO `topic` (`title`, `author_id`, `cid`, `pid`) VALUES(?,?,?,?)";
        $rows = pdo_query($sql,$_POST['title'],$_SESSION[$OJ_NAME.'_'.'user_id'],$cid,$pid);
        
        if($rows<=0){
          echo('Unable to post new.');
        }else{
          $tid = $rows;
        }
    }else{
        echo('Error!');
    }
}

if($_REQUEST['action']=='reply' || !is_null($tid)){
    if(is_null($tid))
      $tid = intval($_POST['tid']);
  
    if(!is_null($tid) && isset($_POST['content']) && $_POST['content']!=''){
      $rows = pdo_query("SELECT tid FROM topic WHERE tid=?",$tid);
      if(isset($rows[0]) && $rows[0][0]>0 ){
 
        $sql = "INSERT INTO `reply` (`author_id`, `time`, `content`, `topic_id`,`ip`) VALUES(?,NOW(),?,?,?)";
        if(pdo_query($sql, $_SESSION[$OJ_NAME.'_'.'user_id'],$_POST['content'],$tid,$ip)){
            if(isset($_REQUEST['cid'])){
                $cid = intval($_REQUEST['cid']);
                header('Location: thread.php?cid='.$cid.'&tid='.$tid);
            }else{
                header('Location: thread.php?tid='.$tid);
            }
            exit(0);
        }else{
            $view_errors="发帖失败！";
            require("template/".$OJ_TEMPLATE."/error.php");
            exit(0);
        }
      }else{
           $view_errors="回复不存在的帖子！";
           require("template/".$OJ_TEMPLATE."/error.php");
           exit(0);
      }
    }else{
        $view_errors="请文明上网！";
        require("template/".$OJ_TEMPLATE."/error.php");
        exit(0);
    }
}
