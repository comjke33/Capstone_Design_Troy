<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

////////////////////////////Common head
$cache_time = 2;
$OJ_CACHE_SHARE = false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
require_once("./include/my_func.inc.php");

$view_title = "$MSG_STATUS";

if (isset($OJ_LANG)) {
  require_once("./lang/$OJ_LANG.php");
}

require_once("./include/const.inc.php");


$str2 = "";
$lock = false;
$lock_time = date("Y-m-d H:i:s",time());

$sql = "WHERE problem_id>0 ";

if (isset($_GET['cid'])) {
  $cid = intval($_GET['cid']);
  $sql = $sql." AND `contest_id`='$cid' and num>=0 ";
  $str2 = $str2."&cid=$cid";
  $sql_lock = "SELECT *  FROM `contest` WHERE `contest_id`=?";

  $view_cid = $cid;
  $result = pdo_query($sql_lock,$cid);
  $rows_cnt = count($result);
  $start_time = 0;
  $end_time = 0;

  if ($rows_cnt>0) {
    $row = $result[0];
    $title = $row['title'];
    $contest_type = $row['contest_type'];
		$start_time = strtotime($row['start_time']);
		$end_time = strtotime($row['end_time']);
		$view_description = $row['description'];
		$view_title = $row['title'];
		$view_start_time = $row['start_time'];
		$view_end_time = $row['end_time'];
	$noip = ((time()<$end_time) && (stripos($title,$OJ_NOIP_KEYWORD)!==false) ) || ($contest_type & 16)>0  ;
	if(isset($_SESSION[$OJ_NAME.'_'."administrator"])||
		isset($_SESSION[$OJ_NAME.'_'."m$cid"])||
		isset($_SESSION[$OJ_NAME.'_'."source_browser"])||
		isset($_SESSION[$OJ_NAME.'_'."contest_creator"])
	   ) $noip=false;
    if($noip){
      $view_errors =  "<h2> $MSG_NOIP_WARNING <a href=\"contest.php?cid=$cid\">$MSG_RETURN_CONTEST</a></h2>";
      $refererUrl = parse_url($_SERVER['HTTP_REFERER']);
      $top=intval($_GET['top']);
      if($refererUrl['path']=="/submitpage.php") {
	$view_errors="<h2>$MSG_SUBMIT $MSG_SUCCESS! $top </h2><a href=\"contest.php?cid=$cid\">$MSG_RETURN_CONTEST</a></h2>";
      }
      require("template/".$OJ_TEMPLATE."/error.php");
      exit(0);
    }
  }

  $lock_time = $end_time-($end_time-$start_time)*$OJ_RANK_LOCK_PERCENT;
  //$lock_time=date("Y-m-d H:i:s",$lock_time);
  $time_sql = "";
  //echo $lock.'-'.date("Y-m-d H:i:s",$lock);
  if (time()>$lock_time && time()<$end_time) {
    //$lock_time=date("Y-m-d H:i:s",$lock_time);
    //echo $time_sql;
    $lock = true;
  }
  else {
    $lock = false;
  }
        
  //require_once("contest-header.php");
}
else {
  //require_once("oj-header.php");
   if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])      // 管理员
	||isset($_SESSION[$OJ_NAME.'_'.'source_browser'])   //代码审查员
	||(isset($_SESSION[$OJ_NAME.'_'.'user_id'])&&(isset($_GET['user_id'])&&$_GET['user_id']==$_SESSION[$OJ_NAME.'_'.'user_id']))  // 普通用户查询自己的
  ){
	    if(isset($_SESSION[$OJ_NAME.'_'.'source_browser'])){
			  $sql="WHERE problem_id>0  ";                               // 默认只有管理员可以在练习状态看所有人的比赛提交，其他人只能在特意查询时查到自己的比赛提交
	    }else if ($_SESSION[$OJ_NAME.'_'.'user_id']!="guest"){
			  $sql="WHERE (contest_id=0 or contest_id is null)  ";      // 如果希望所有人能在练习状态直接查看自己的比赛提交，这里改成 where problem_id>0 
	    }
    }else{
        $sql="WHERE solution.user_id not in ($OJ_RANK_HIDDEN) and  problem_id>0 and (contest_id=0 or contest_id is null) "; // 如果希望所有人能在练习状态直接查看别人的比赛提交，这里改成 where problem_id>0 
    }
}

$start_first = true;
$order_str = " ORDER BY `solution_id` DESC ";

// check the top arg
if (isset($_GET['top'])) {
  $top = strval(intval($_GET['top']));
  if ($top!=-1)
    $sql = $sql."AND `solution_id`<='".$top."' ";
}

// check the problem arg
$problem_id = "";
if (isset($_GET['problem_id']) && $_GET['problem_id']!="") {
  if (isset($_GET['cid'])) {
    $problem_id = htmlentities($_GET['problem_id'],ENT_QUOTES,'UTF-8');
    $num = array_search($problem_id,$PID);
    $problem_id = $PID[$num];
    $sql = $sql."AND `num`='".$num."' ";
    $str2 = $str2."&problem_id=".trim($problem_id);
  }
  else {
    $problem_id = strval(intval($_GET['problem_id']));
    if ($problem_id!='0') {
      $sql = $sql."AND `problem_id`='".$problem_id."' ";
      $str2 = $str2."&problem_id=".trim($problem_id);
    }
    else
      $problem_id = "";
  }
}

$param=array();

// check the user_id arg
$user_id = "";
//    echo "[".(($contest_type&8)>0)."]";
if ((isset($OJ_ON_SITE_CONTEST_ID)&&$OJ_ON_SITE_CONTEST_ID>0&&!isset($_SESSION[$OJ_NAME.'_'.'administrator']))
       || ( isset($OJ_PUBLIC_STATUS) && !$OJ_PUBLIC_STATUS )
       || ( isset($contest_type) && ($contest_type & 8) > 0 )
        ) {
  if(!isset($_SESSION[$OJ_NAME.'_'.'user_id']))
          $_GET['user_id']='Guest';
  else if (!isset($_SESSION[$OJ_NAME.'_'.'source_browser']))
          $_GET['user_id'] = $_SESSION[$OJ_NAME.'_'.'user_id'];
}


if (isset($_GET['user_id'])) {
  $user_id = trim($_GET['user_id']);
  if ( $user_id!="" ) {
      $sql = $sql."AND solution.user_id=? ";
      if ($str2!="")
      		$str2 = $str2."&";
      $str2 = $str2."&user_id=".htmlentities(urlencode($user_id),ENT_QUOTES);
      array_push($param,$user_id);
  }
  else{
	  $user_id = "";
  }
}

if (isset($_GET['language'])){
	  $language = intval($_GET['language']);
	  if ($language>count($language_ext) || $language<0)
		  $language = -1;
	if ($language!=-1) {
	  $sql = $sql."AND solution.`language`=?  ";
	  $str2 = $str2."&language=".$language;
      	  array_push($param,$language);
	}
}else{
	$language = -1;
}



if (isset($_GET['jresult'])){
  $result = intval($_GET['jresult']);
	if ($result!=-1 && !$lock) {
	  $sql = $sql."AND `result`=? ";
	  $str2 = $str2."&jresult=".$result;
      	  array_push($param,$result);
	}
}else{
  $result = -1;
}
$showsim = isset($_GET['showsim']) ? intval($_GET['showsim']) : 0;
if ($OJ_SIM&$showsim>0) {
        $fields="solution.*,users.nick,users.group_name,users.starred,sim.*";
}else{
        $fields="solution.*,users.nick,users.group_name,users.starred";
}
if(isset($_GET['school'])&&trim($_GET['school'])!="" || isset($_GET['group_name'])&&trim($_GET['group_name'])!=""    ){

         $sql0="select $fields from solution solution inner join users users on solution.user_id=users.user_id  and users.defunct='N' ";
         if(isset($_GET['school'])&&trim($_GET['school'])!=""){
            $school=trim($_GET['school']);
            $sql.=" and users.school=? ";
            array_push($param,trim($_GET['school']));
            $str2 = $str2."&school=".htmlentities(trim($_GET['school']),ENT_QUOTES);
         }
         if(isset($_GET['group_name'])&&trim($_GET['group_name'])!=""){
            $group_name=trim($_GET['group_name']);
            $sql.=" and users.group_name=? ";
            array_push($param,trim($_GET['group_name']));
            $str2 = $str2."&group_name=".htmlentities(trim($_GET['group_name']),ENT_QUOTES);
         }
}else{
        $sql0="select $fields from solution inner join users on solution.user_id=users.user_id  and users.defunct='N' ";
}

if ($OJ_SIM&$showsim>0) {
  //$old=$sql;
  $sql = $sql0." left join `sim` sim on solution.solution_id=sim.s_id ".$sql;
  if ($showsim>0) {
    $sql .= " and sim.sim>=$showsim";
    $str2 .= "&showsim=$showsim";
  }
  
  //$sql=$sql.$order_str." LIMIT 20";
}
else {
  $sql = $sql0." ".$sql;
}

//echo $sql;
//exit();
$sql = $sql.$order_str." LIMIT 10";
//echo $sql;


if (!empty($param)) {
  $result = pdo_query($sql,$param);
}else{
  $result = pdo_query($sql);
}
  
if (!empty($result))
  $rows_cnt = count($result);
else
  $rows_cnt = 0;

$top = $bottom=-1;
$cnt = 0;
if ($start_first) {
  $row_start = 0;
  $row_add = 1;
}
else {
  $row_start = $rows_cnt-1;
  $row_add = -1;
}

$view_status = Array();

$last = 0;
$avg_delay=0;
$total_count=0;
$need_refresh_remote=false;

// -----------------------------------------------------
// correct_solution 테이블에 데이터를 삽입하는 함수
function saveCorrectSolution($solution_id, $problem_id, $correct_code) {
  // correct_solution 테이블에 데이터를 삽입하는 SQL 쿼리
  $sql = "INSERT INTO correct_solution (solution_id, problem_id, correct_code) 
          VALUES (?, ?, ?)";
  
  // pdo_query를 사용하여 쿼리 실행
  pdo_query($sql, $solution_id, $problem_id, $correct_code);
}

//허가된 유저들만 사용가능하게
include('allowed_users.php');

for ($i=0; $i<$rows_cnt; $i++) {
  $row = $result[$i];

  $row = $result[$i];

  // 결과가 정답 코드(correct_solution) 가져오기
  if ($row['result'] == 4) {  // Accepted
      // solution_id와 problem_id를 가져오기
      $sid = urlencode($row['solution_id']);  // solution_id
      $problem_id = $row['problem_id'];        // problem_id

      // 정답 코드 (정답 코드 내용은 시스템에 맞게 처리해야 함)
      $correct_code = "정답 코드 내용"; // 실제 정답 코드를 가져오는 방식에 맞게 처리

      // correct_solution 테이블에 삽입
      saveCorrectSolution($sid, $problem_id, $correct_code);
  }

  //$view_status[$i]=$row;
  if ($i==0 && $row['result']<4)
    $last = $row['solution_id'];

  if ($top==-1)
    $top = $row['solution_id'];
  
  $bottom = $row['solution_id'];
  $flag = (!is_running(intval($row['contest_id']))) 
  || isset($_SESSION[$OJ_NAME.'_'.'source_browser']) 
  || isset($_SESSION[$OJ_NAME.'_'.'administrator']) 
  || (isset($_SESSION[$OJ_NAME.'_'.'user_id'])&&!strcmp($row['user_id'],$_SESSION[$OJ_NAME.'_'.'user_id']));

  $cnt = 1-$cnt;
  
  $view_status[$i][0] = $row['solution_id'];
       
  if ($row['contest_id']>0) {
    if (isset($_SESSION[$OJ_NAME.'_'.'administrator']))
      $view_status[$i][1] = "<a href='contestrank.php?cid=".$row['contest_id']."&user_id=".$row['user_id']."#".$row['user_id']."' title='".$row['ip']."'>".$row['user_id']."</a>";
    else
      $view_status[$i][1] = "<a href='contestrank.php?cid=".$row['contest_id']."&user_id=".$row['user_id']."#".$row['user_id']."'>".$row['user_id']."</a>";
  }
  else {
    if (isset($_SESSION[$OJ_NAME.'_'.'administrator']))
      $view_status[$i][1] = "<a href='userinfo.php?user=".$row['user_id']."' title='".$row['nick']."[".$row['ip']."]'>".$row['user_id']."</a>";
    else
      $view_status[$i][1] = "<a href='userinfo.php?user=".$row['user_id']."' title='".$row['nick']."'>".$row['user_id']."</a>";
  }
  if(isset($row['starred']) && $row['starred'] >0 ) {
	  $view_status[$i][1]="⭐".$view_status[$i][1]."<span title='用同名账户给hustoj项目加星，可以点亮此星' >⭐</span>";	//people who starred us ,we star them
  }
  $view_status[$i]['nick']=$row['nick'];

  if ($row['contest_id']>0) {
    if (isset($end_time) && time() < $end_time) {
      $view_status[$i][2] = "<div><a href='problem.php?cid=".$row['contest_id']."&pid=".$row['num']."'>";
      if (isset($cid)) {
        $view_status[$i][2] .= $PID[$row['num']];
      }
      else {
        $view_status[$i][2] .= $row['problem_id'];
      }
      $view_status[$i][2] .= "</div></a>";
    }
    else {
      $view_status[$i][2] = "<div class=center>";
      if (isset($cid)) {

        //check the problem will be use remained contest/exam
        $tpid = intval($row['problem_id']);
        $sql = "SELECT `problem_id` FROM `problem` WHERE `problem_id`=? AND `problem_id` IN (
          SELECT `problem_id` FROM `contest_problem` WHERE `contest_id` IN (
            SELECT `contest_id` FROM `contest` WHERE (`defunct`='N' AND now()<`end_time`)
          )
        )";

        $tresult = pdo_query($sql, $tpid);

        if (intval($tresult) != 0)   //if the problem will be use remaind contes/exam
          $view_status[$i][2] .= $PID[$row['num']]; //hide link
        else
          $view_status[$i][2] .= "<a href='problem.php?id=".$row['problem_id']."'>".$PID[$row['num']]."</a>";
      }
      else {
        $view_status[$i][2] .= "<a href='problem.php?id=".$row['problem_id']."'>".$row['problem_id']."</a>";
      }
      $view_status[$i][2] .= "</div>";
    }
  }
  else {
    $view_status[$i][2] = "<div class=center><a href='problem.php?id=".$row['problem_id']."'>".$row['problem_id']."</a></div>";
  }

  switch($row['result']) {
    case 4:
      $MSG_Tips = $MSG_HELP_AC; break;
    case 5:
      $MSG_Tips = $MSG_HELP_PE; break;
    case 6:
      $MSG_Tips = $MSG_HELP_WA; break;
    case 7:
      $MSG_Tips = $MSG_HELP_TLE; break;
    case 8:
      $MSG_Tips = $MSG_HELP_MLE; break;
    case 9:
      $MSG_Tips = $MSG_HELP_OLE; break;
    case 10:
      $MSG_Tips = $MSG_HELP_RE; break;
    case 11:
      $MSG_Tips = $MSG_HELP_CE; break;
    default:
      $MSG_Tips = "";
  }
  if($row['result'] > 15){
        $need_refresh_remote=true;
  }
  $AC_RATE = floatval($row['pass_rate']*100);
  if (isset($OJ_MARK) && $OJ_MARK!="mark") {
          if($OJ_MARK=="percent"&&$row['result']>4){
                $mark = (100-$AC_RATE)."%";
          }else{
                $mark="";
          }
  }
  else {
    if($AC_RATE > 99||$row['result']==4)      
        $mark = "";
    else 
        $mark = " ".$MSG_MARK.$AC_RATE;
  }

  if ((!isset($_SESSION[$OJ_NAME.'_'.'user_id']) || $row['user_id']!=$_SESSION[$OJ_NAME.'_'.'user_id']) && !isset($_SESSION[$OJ_NAME.'_'.'source_browser']))
    $mark = "";

  $view_status[$i][3] = "<span class='hidden' style='display:none' result=".$row['result']."></span>";
  if (intval($row['result'])==11 && ((isset($_SESSION[$OJ_NAME.'_'.'user_id']) && $row['user_id']==$_SESSION[$OJ_NAME.'_'.'user_id']) || isset($_SESSION[$OJ_NAME.'_'.'source_browser']))) {
    $view_status[$i][3] .= "<a href=ceinfo.php?sid=".$row['solution_id']." class='".$judge_color[$row['result']]."' title='$MSG_Tips'>".$MSG_Compile_Error."</a>";
  }
  else if ((((intval($row['result'])==8 || intval($row['result'])==7 || intval($row['result'])==5 || intval($row['result'])==6) && ($OJ_SHOW_DIFF || isset($_SESSION[$OJ_NAME.'_'.'source_browser']))) || $row['result']==10 || $row['result']==13) && ((isset($_SESSION[$OJ_NAME.'_'.'user_id']) && $row['user_id']==$_SESSION[$OJ_NAME.'_'.'user_id']) || isset($_SESSION[$OJ_NAME.'_'.'source_browser']))) {
    $view_status[$i][3] .= "<a href=reinfo.php?sid=".$row['solution_id']." class='".$judge_color[$row['result']]."' title='$MSG_Tips'>".$judge_result[$row['result']].$mark."</a>";
  }
  else {
    if (!$lock || $lock_time>$row['in_date'] || $row['user_id']==$_SESSION[$OJ_NAME.'_'.'user_id']) {
      if ($OJ_SIM && isset($row['sim']) && $row['sim']>80 && $row['sim_s_id']!=$row['s_id']) {
        $view_status[$i][3] .= "<a href=reinfo.php?sid=".$row['solution_id']." class='".$judge_color[$row['result']]."' title='$MSG_Tips'>*".$judge_result[$row['result']];

        if ($row['result']!=4 && isset($row['pass_rate']) && $row['pass_rate']!=1)
          $view_status[$i][3] .= $mark."</a>";
        else
          $view_status[$i][3] .= "</a>";

        if( isset($_SESSION[$OJ_NAME.'_'.'source_browser'])) {
          $view_status[$i][3] .= "<a href=comparesource.php?left=".$row['sim_s_id']."&right=".$row['solution_id']." class='label label-info' target=original>".$row['sim_s_id']."(".$row['sim']."%)</a>";
        }
        else {
          $view_status[$i][3] .= "<span class='label label-info'>".$row['sim_s_id']."</span>";
        }
        
        if (isset($_GET['showsim']) && isset($row['sim_s_id'])) {
          $view_status[$i][3] .= "<span sid='".$row['sim_s_id']."' class='original'></span>";
        }
      }
      else {
        $view_status[$i][3] .= "<a href=reinfo.php?sid=".$row['solution_id']." class='".$judge_color[$row['result']]."' title='$MSG_Tips'>".$judge_result[$row['result']].$mark."</a>";
      }
    }
    else {
      $view_status[$i][3] = "----";
    }
  }

  if (isset($_SESSION[$OJ_NAME.'_'.'http_judge'])) {
    $view_status[$i][3] .= "<form class='http_judge_form form-inline'> <input type=hidden name=sid value='".$row['solution_id']."'>";
    $view_status[$i][3] .= "</form>";
  }
  
  if ($flag) {
    if ($row['result']>=4) {
      $view_status[$i][4] = "<div id=center>".$row['memory']." KiB</div>";
      $view_status[$i][5] = "<div id=center>".$row['time']." ms</div>";
      //echo "=========".$row['memory']."========";
    }
    else {
      $view_status[$i][4] = "---";
      $view_status[$i][5] = "---";
    }
    
    //echo $row['result'];
    if (!(isset($_SESSION[$OJ_NAME.'_'.'user_id']) && strtolower($row['user_id'])==strtolower($_SESSION[$OJ_NAME.'_'.'user_id']) 
    || isset($_SESSION[$OJ_NAME.'_'.'source_browser']))) {
      $view_status[$i][6] = $language_name[$row['language']];
    }
    else {
      if( (isset($end_time) && time() < $end_time)
		||(isset($_SESSION[$OJ_NAME.'_'.'user_id']) && strtolower($row['user_id'])==strtolower($_SESSION[$OJ_NAME.'_'.'user_id'])) 
		||isset($_SESSION[$OJ_NAME.'_'.'source_browser'])
	)
        $view_status[$i][6] = "<a target=_self href=showsource.php?id=".$row['solution_id'].">".$language_name[$row['language']]."</a>";
      else
        $view_status[$i][6] = $language_name[$row['language']];
        
      if( (!(isset($OJ_OLD_FASHINED) && $OJ_OLD_FASHINED )) && ($OJ_TEMPLATE=="syzoj" || $OJ_TEMPLATE=="bs3" ) && $OJ_AUTO_SHOW_OFF ) {
            $edit_link="problem.php";
      }else {
            $edit_link="submitpage.php";
      }
      if ($row["problem_id"]>0) {
        if ($row['contest_id']>0) {
         if (isset($end_time)&&time()<$end_time || isset($_SESSION[$OJ_NAME.'_'.'source_browser']))
            $view_status[$i][6] .= "/<a target=_self href=\"$edit_link?cid=".$row['contest_id']."&pid=".$row['num']."&sid=".$row['solution_id']."\">Edit</a>";
          else
            $view_status[$i][6] .= "";
        }
        else {
          $view_status[$i][6] .= "/<a target=_self href=\"$edit_link?id=".$row['problem_id']."&sid=".$row['solution_id']."\">Edit</a>";
        }
      }
    }
    
    $view_status[$i][7] = $row['code_length']." bytes";
    

        
  }
  else {
    $view_status[$i][4] = "----";
    $view_status[$i][5] = "----";
    $view_status[$i][6] = "----";
    $view_status[$i][7] = "----";
  }
  
    $used=(strtotime($row['judgetime'])-strtotime($row['in_date']));
  if($used>0){
    $avg_delay+=floatval($used);
    $total_count++;
  }
  if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    $view_status[$i][8] = substr($row['in_date'],5)."[".$used."]";
    $view_status[$i][9] = $row['judger'];
  }
  else
    $view_status[$i][8]= $row['in_date'];

    // 예시로, 특정 user_id만 버튼을 보이게 하려면 
    if (!isset($cid) && in_array($_SESSION[$OJ_NAME.'_'.'user_id'], $allowed_user_id)) { 
    $sid = urlencode($row['solution_id']);
    $pid = urlencode($row['problem_id']);
    
    $result = $row['result'];
    
    if ($result == 4) {  // Accepted
        $view_status[$i][10] = "
        <button class='toggle-similar ui blue mini button' data-sid='{$sid}'>유사문제 추천</button>
        <div id='similar-{$sid}' class='similar-box' style='display:none; margin-top:5px;'></div>
        ";
    } elseif ($result < 4) {  // 아직 채점 중이거나 Pending, Running, etc.
        $view_status[$i][10] = "<span class='ui grey text'>채점 중...</span>";
    } else {  // 채점 완료지만 Accepted는 아님
        $view_status[$i][10] = "<a target=\"_self\" href=\"feedback.php?solution_id={$sid}&problem_id={$pid}\" class=\"ui orange mini button\">문법 오류 확인</a>";
    }
    } else {
        $view_status[$i][10] = "-";
    }

  
}
if($total_count>0) $avg_delay/= $total_count;

/////////////////////////Template
if (isset($_GET['cid']))
  require("template/".$OJ_TEMPLATE."/conteststatus.php");
else
  require("template/".$OJ_TEMPLATE."/status.php");


//触发Remote judge模块
$remote_delay=5;   //最小轮询周期，单位秒
if( $need_refresh_remote && isset($OJ_REMOTE_JUDGE)&&$OJ_REMOTE_JUDGE&& (time()-fileatime("remote.php")>$remote_delay)){ 
       touch("remote.php");
       ?>
        <iframe src='remote.php' width=0 height=0 ></iframe>
       <?php
}

/////////////////////////Common foot
if(file_exists('./include/cache_end.php'))
  require_once('./include/cache_end.php');

?>



<script>
document.addEventListener("DOMContentLoaded", function() {
  document.querySelectorAll(".toggle-similar").forEach(function(button) {
    button.addEventListener("click", function() {
      const sid = this.dataset.sid;
      const box = document.getElementById("similar-" + sid);

      if (box.style.display === "none") {
        box.innerHTML = "<i>🔄 유사 문제 불러오는 중...</i>";
        fetch("/ajax/similar_problem.php?solution_id=" + sid)
          .then(res => res.text())
          .then(html => {
            box.innerHTML = html;
            box.style.display = "block";
          })
          .catch(() => {
            box.innerHTML = "<span style='color:red'>❌ 유사 문제 불러오기 실패</span>";
          });
      } else {
        box.style.display = "none";
      }
    });
  });
});
</script>
