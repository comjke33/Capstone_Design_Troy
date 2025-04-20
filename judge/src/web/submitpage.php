<?php
require_once('./include/db_info.inc.php');
require_once('./include/const.inc.php');
require_once('./include/my_func.inc.php');
require_once('./include/memcache.php');
require_once('./include/setlang.php');
require_once('./include/curl.php');

$view_title = $MSG_SUBMIT;

if (!isset($_SESSION[$OJ_NAME . '_user_id'])) {
	if (isset($OJ_GUEST) && $OJ_GUEST) {
		$_SESSION[$OJ_NAME . '_user_id'] = "Guest";
	} else {
		$view_errors = "<a href=loginpage.php>$MSG_Login</a>";
		require("template/" . $OJ_TEMPLATE . "/error.php");
		exit(0);
	}
}

$langmask = $OJ_LANGMASK;
$problem_id = 1000;

if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
} else if (isset($_GET['cid']) && isset($_GET['pid'])) {
	$cid = intval($_GET['cid']);
	$pid = intval($_GET['pid']);
	require_once("contest-check.php");
	$psql = "SELECT problem_id FROM contest_problem WHERE contest_id=? AND num=?";
	$data = pdo_query($psql, $cid, $pid);
	$row = $data[0];
	$problem_id = $row[0];
} else {
	$view_errors = "<h2>No Such Problem!</h2>";
	require("template/" . $OJ_TEMPLATE . "/error.php");
	exit(0);
}

$view_src = "";
$lastlang = 1;
$spj = 0;
$remote_oj = "";
$solution_name = false;
$view_sample_input = "1 2";
$view_sample_output = "3";

if (isset($_GET['sid'])) {
	$sid = intval($_GET['sid']);
	$sql = "SELECT * FROM `solution` WHERE `solution_id`=?";
	$result = pdo_query($sql, $sid);
	$row = $result[0];
	$cid = intval($row['contest_id']);
	$sproblem_id = intval($row['problem_id']);
	$contest_id = $cid;

	$ok = false;
	if ($row && $row['user_id'] == $_SESSION[$OJ_NAME . '_user_id']) $ok = true;

	$need_check_using = true;
	$now = time();

	if ($contest_id > 0) {
		$sql = "SELECT start_time, end_time FROM contest WHERE contest_id=?";
		$result = pdo_query($sql, $contest_id);
		if ($result) {
			$row = $result[0];
			$start_time = strtotime($row['start_time']);
			$end_time = strtotime($row['end_time']);
			$need_check_using = $end_time < $now;
		}
	} else {
		$need_check_using = !isset($_SESSION[$OJ_NAME . '_source_browser']);
	}

	if ($need_check_using) {
		$now_str = date('Y-m-d H:i', $now);
		$sql = "SELECT contest_id FROM contest WHERE contest_id IN (SELECT contest_id FROM contest_problem WHERE problem_id=?) AND start_time < ? AND end_time > ?";
		$result = pdo_query($sql, $sproblem_id, $now_str, $now_str);
		if (count($result) > 0 && !isset($_SESSION[$OJ_NAME . '_source_browser'])) {
			$view_errors = "<center><h3>$MSG_CONTEST_ID : " . $result[0][0] . "</h3><p> $MSG_SOURCE_NOT_ALLOWED_FOR_EXAM </p><br></center><br><br>";
			require("template/" . $OJ_TEMPLATE . "/error.php");
			exit(0);
		}
	}

	if (isset($_SESSION[$OJ_NAME . '_source_browser'])) $ok = true;

	if (isset($OJ_EXAM_CONTEST_ID) && $cid < $OJ_EXAM_CONTEST_ID && !isset($_SESSION[$OJ_NAME . '_source_browser'])) {
		$view_errors = "<center><h3>$MSG_CONTEST_ID : $OJ_EXAM_CONTEST_ID+ </h3><p> $MSG_SOURCE_NOT_ALLOWED_FOR_EXAM </p><br></center><br><br>";
		require("template/" . $OJ_TEMPLATE . "/error.php");
		exit(0);
	}

	if ($ok) {
		$sql = "SELECT `source` FROM `source_code_user` WHERE `solution_id`=?";
		$result = pdo_query($sql, $sid);
		$row = $result[0];
		if ($row) $view_src = $row['source'];

		if (isset($cid) && $cid > 0) {
			$sql = "SELECT langmask FROM contest WHERE contest_id=?";
			$result = pdo_query($sql, $cid);
			$row = $result[0];
			if (count($row) > 0) {
				$_GET['langmask'] = $row['langmask'];
				$langmask = $row['langmask'];
			}
		}

		$sql = "SELECT language FROM solution WHERE solution_id=?";
		$result = pdo_query($sql, $sid);
		$row = $result[0];
		if ($row && str_contains($_SERVER['HTTP_REFERER'], "status.php")) {
			$lastlang = intval($row['language']);
		} else {
			$lastlang = intval($_COOKIE['lastlang']);
		}
	}
}

if (isset($id)) $problem_id = $id;

$sample_sql = "SELECT sample_input, sample_output, problem_id, spj, remote_oj FROM problem WHERE problem_id = ?";
if (isset($_GET['id'])) {
	$result = pdo_query($sample_sql, $id);
} else {
	$result = pdo_query($sample_sql, $problem_id);
}

if ($result == false) {
	$view_errors = "<h2>No Such Problem!</h2>";
	require("template/" . $OJ_TEMPLATE . "/error.php");
	exit(0);
}

$row = $result[0];
$view_sample_input = $row['sample_input'];
$view_sample_output = $row['sample_output'];
$problem_id = $row['problem_id'];
$spj = $row['spj'];
$remote_oj = $row['remote_oj'];
if ($spj > 1) $OJ_ACE_EDITOR = false;

$solution_file = "$OJ_DATA/$problem_id/solution.name";
if (file_exists($solution_file)) {
	$solution_name = file_get_contents($solution_file);
} else {
	$solution_name = false;
}

if (!$view_src) {
	if (isset($_COOKIE['lastlang']) && $_COOKIE['lastlang'] != "undefined") {
		$lastlang = intval($_COOKIE['lastlang']);
	} else {
		$sql = "SELECT language FROM solution WHERE user_id=? ORDER BY solution_id DESC LIMIT 1";
		$result = pdo_query($sql, $_SESSION[$OJ_NAME . '_user_id']);
		if (count($result) > 0) {
			$lastlang = $result[0][0];
		} else {
			$lastlang = 1;
		}
	}
	$template_file = "$OJ_DATA/$problem_id/template." . $language_ext[$lastlang];
	if (file_exists($template_file)) {
		$view_src = file_get_contents($template_file);
	} else if ($spj > 1 && file_exists("$OJ_DATA/$problem_id/template.c")) {
		$view_src = file_get_contents("$OJ_DATA/$problem_id/template.c");
	} else if ($spj == 2 && file_exists("$OJ_DATA/$problem_id/test.in")) {
		$total = intval(file_get_contents("$OJ_DATA/$problem_id/test.in"));
		$view_src = "";
		for ($i = 1; $i <= $total; $i++) {
			$view_src .= $i . "\n";
		}
	}
}

$sql = "SELECT count(1) FROM `solution` WHERE result<4";
$result = mysql_query_cache($sql);
$row = $result[0];
if ($row[0] > 10) {
	$OJ_VCODE = true;
}

require("template/" . $OJ_TEMPLATE . "/submit_origin.php");