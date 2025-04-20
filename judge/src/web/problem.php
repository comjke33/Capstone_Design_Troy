<?php
// 캐시 설정
$cache_time = 10;
$OJ_CACHE_SHARE = false;

// 공통 include
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/bbcode.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/setlang.php');
if (isset($OJ_LANG)) require_once("./lang/$OJ_LANG.php");

// 현재 시간 구하기
$now = date("Y-m-d H:i");

// URL 인자 처리
$ucid = isset($_GET['cid']) ? "&cid=" . intval($_GET['cid']) : "";

// 문제 출처 플래그 초기화
$pr_flag = $co_flag = false;
$id = null;

/////////////////////////////
// 연습 문제로 접근한 경우 //
/////////////////////////////
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // 아직 끝나지 않았거나 private인 contest에서 사용 중인 문제 조회
    $sql = "SELECT c.contest_id, c.title 
            FROM contest c 
            INNER JOIN contest_problem cp ON c.contest_id=cp.contest_id 
            WHERE cp.problem_id=? AND ((c.end_time>'$now' AND c.defunct='N') OR c.private='1')";
    $used_in_contests = pdo_query($sql, $id);

    // 관리자/출제자/검토자/편집자는 모든 문제 열람 가능
    $sql = (isset($_SESSION["{$OJ_NAME}_administrator"]) || isset($_SESSION["{$OJ_NAME}_problem_verifiter"])
         || isset($_SESSION["{$OJ_NAME}_contest_creator"]) || isset($_SESSION["{$OJ_NAME}_problem_editor"]))
         ? "SELECT * FROM problem WHERE problem_id=?"
         : ($OJ_FREE_PRACTICE
            ? "SELECT * FROM problem WHERE defunct='N' AND problem_id=?"
            : // 연습 모드가 아니면 아직 끝나지 않았거나 private인 contest에 포함된 문제는 차단
              "SELECT * FROM problem WHERE problem_id=? AND defunct='N' AND problem_id NOT IN (
                SELECT problem_id FROM contest_problem 
                WHERE contest_id IN (
                    SELECT contest_id FROM contest 
                    WHERE (end_time>'$now' AND defunct='N') OR private='1'
                )
              )");

    $pr_flag = true;
    $result = pdo_query($sql, $id);
}

//////////////////////////////////
// contest 문제로 접근한 경우 //
//////////////////////////////////
elseif (isset($_GET['cid']) && isset($_GET['pid'])) {
    $cid = intval($_GET['cid']);
    $pid = intval($_GET['pid']);

    // contest 접근 검사
    require_once("contest-check.php");

    // contest 유효성 검사 쿼리
    $sql = (isset($_SESSION["{$OJ_NAME}_administrator"]) || isset($_SESSION["{$OJ_NAME}_contest_creator"]) || isset($_SESSION["{$OJ_NAME}_problem_editor"]))
         ? "SELECT langmask, private, defunct FROM contest WHERE contest_id=?"
         : "SELECT langmask, private, defunct FROM contest 
            WHERE defunct='N' AND contest_id=? 
            AND (start_time <= '$now' AND ('$now' < end_time OR private='N'))";

    $result = pdo_query($sql, $cid);

    // contest가 없거나 접근 권한 없을 때 차단
    if (empty($result) && !$OJ_FREE_PRACTICE && !isset($_SESSION["{$OJ_NAME}_administrator"]) && !isset($_SESSION["{$OJ_NAME}_c$cid"])) {
        $view_errors = "<title>$MSG_CONTEST</title><h2>No such Contest!</h2>";
        require("template/$OJ_TEMPLATE/error.php"); exit;
    }

    $row = $result[0];
    $contest_ok = !(($row[1] && !isset($_SESSION["{$OJ_NAME}_c$cid"])) || $row[2] == 'Y');

    // 관리자/출제자 등은 강제 통과
    if (isset($_SESSION["{$OJ_NAME}_administrator"]) || isset($_SESSION["{$OJ_NAME}_contest_creator"]) || isset($_SESSION["{$OJ_NAME}_problem_editor"])) {
        $contest_ok = true;
    }

    // 권한 없을 경우 차단
    if (!$contest_ok) {
        $view_errors = "No such Contest!";
        require("template/$OJ_TEMPLATE/error.php"); exit;
    }

    // contest 내 문제 정보 로드
    $sql = "SELECT * FROM problem 
            WHERE problem_id=(SELECT problem_id FROM contest_problem WHERE contest_id=? AND num=?)";
    $result = pdo_query($sql, $cid, $pid);
    $id = $result[0]['problem_id'];
    $co_flag = true;
}

/////////////////////////////
// 문제 정보가 아예 없음 //
/////////////////////////////
else {
    $view_errors = "<title>$MSG_NO_SUCH_PROBLEM</title><h2>$MSG_NO_SUCH_PROBLEM</h2>";
    require("template/$OJ_TEMPLATE/error.php");
    exit;
}

//////////////////////////////////
// 문제가 없는 경우 에러 처리 //
//////////////////////////////////
if (count($result) != 1) {
    $view_errors = "";
    if (isset($_GET['id']) && count($used_in_contests) > 0 && !isset($OJ_EXAM_CONTEST_ID) && !isset($OJ_ON_SITE_CONTEST_ID)) {
        $view_errors .= "<hr><br>$MSG_PROBLEM_USED_IN:";
        foreach ($used_in_contests as $contests)
            $view_errors .= "<a class='label label-warning' href='contest.php?cid={$contests[0]}'>{$contests[1]}</a><br>";
    } else {
        $view_title = "<title>$MSG_NO_SUCH_PROBLEM!</title>";
        $view_errors .= "<h2>$MSG_NO_SUCH_PROBLEM!</h2>";
    }

    if (!isset($_SESSION["{$OJ_NAME}_administrator"]) && !isset($_SESSION["{$OJ_NAME}_problem_editor"])) {
        require("template/$OJ_TEMPLATE/error.php");
        exit;
    }
} 
//////////////////////////////////
// 정상적으로 문제 로드한 경우 //
//////////////////////////////////
else {
    $row = $result[0];
    $view_title = $row['title'];
}

////////////////////////////////////////////////
// NOIP 모드 문제이거나 문제 잠금 처리된 경우 //
////////////////////////////////////////////////
$flag = false;
if (!empty($OJ_NOIP_KEYWORD)) {
    $sql = "SELECT 1 FROM contest_problem 
            WHERE problem_id=? 
            AND contest_id IN (
                SELECT contest_id FROM contest 
                WHERE start_time < ? AND end_time > ? AND title LIKE ?
            )";
    $flag = !empty(pdo_query($sql, $id, $now, $now, "%$OJ_NOIP_KEYWORD%"));
}

if ($flag || problem_locked($id, 28)) {
    $row['accepted'] = $row['submit'] = "<font color='red'> ? </font>";
    if (isset($OJ_NOIP_HINT) && $OJ_NOIP_HINT) {
        $row['hint'] = $MSG_NOIP_NOHINT;
    }
}

///////////////////////////////////////////
// 문제 출력 예시 파일 있는 경우 표시 //
///////////////////////////////////////////
$solution_file = "$OJ_DATA/$id/output.name";
if (file_exists($solution_file)) {
    $content = file_get_contents($solution_file);
    $filename = pathinfo($content, PATHINFO_FILENAME);
}

//////////////////////////
// 문제 화면 템플릿 로드 //
//////////////////////////
require("template/$OJ_TEMPLATE/problem.php");

/////////////////////
// 캐시 종료 처리 //
/////////////////////
if (file_exists('./include/cache_end.php')) {
    require_once('./include/cache_end.php');
}
?>
