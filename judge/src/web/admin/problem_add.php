<?php
require_once ("admin-header.php");
require_once("../include/check_post_key.php");
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

require_once ("../include/db_info.inc.php");
require_once ("../include/my_func.inc.php");
require_once ("../include/problem.php");

// contest_id
$title = $_POST['title'];
$title = str_replace(",", "&#44;", $title);
$time_limit = $_POST['time_limit'];
$memory_limit = $_POST['memory_limit'];

$description = $_POST['description'];
//$description = str_replace("<p>", "", $description); 
//$description = str_replace("</p>", "<br />", $description);
$description = str_replace(",", "&#44;", $description); 

$input = $_POST['input'];
//$input = str_replace("<p>", "", $input); 
//$input = str_replace("</p>", "<br />", $input); 
$input = str_replace(",", "&#44;", $input);

$output = $_POST['output'];
//$output = str_replace("<p>", "", $output); 
//$output = str_replace("</p>", "<br />", $output);
$output = str_replace(",", "&#44;", $output); 

$sample_input = $_POST['sample_input'];
$sample_output = $_POST['sample_output'];
$test_input = $_POST['test_input'];
$test_output = $_POST['test_output'];
/* don't do this , we will left them empty for not generating invalid test data files 
if ($sample_input=="") $sample_input="\n";
if ($sample_output=="") $sample_output="\n";
if ($test_input=="") $test_input="\n";
if ($test_output=="") $test_output="\n";
*/
$hint = $_POST['hint'];
//$hint = str_replace("<p>", "", $hint); 
//$hint = str_replace("</p>", "<br />", $hint); 
$hint = str_replace(",", "&#44;", $hint);

$source = $_POST['source'];

$spj = $_POST['spj'];


if (false) {
  $title = stripslashes($title);
  $time_limit = stripslashes($time_limit);
  $memory_limit = stripslashes($memory_limit);
  $description = stripslashes($description);
  $input = stripslashes($input);
  $output = stripslashes($output);
  $sample_input = stripslashes($sample_input);
  $sample_output = stripslashes($sample_output);
  $test_input = stripslashes($test_input);
  $test_output = stripslashes($test_output);
  $hint = stripslashes($hint);
  $source = stripslashes($source);
  $spj = stripslashes($spj);
  $source = stripslashes($source);
}

$title = ($title);
$description = ($description);
$input = ($input);
$output = ($output);
$hint = ($hint);
//echo "->".$OJ_DATA."<-"; 
$pid = addproblem($title, $time_limit, $memory_limit, $description, $input, $output, $sample_input, $sample_output, $hint, $source, $spj, $OJ_DATA);
$basedir = "$OJ_DATA/$pid";
mkdir($basedir);

//모범코드 DB저장
$exemplary_code = $_POST['exemplary_code'];
if (!empty($exemplary_code)) {
  $sql = "INSERT INTO exemplary (problem_id, exemplary_code) VALUES (?, ?)";
  pdo_query($sql, $pid, $exemplary_code);
}
//모범코드 DB저장


//문제태그 db저장
$tag_ids = isset($_POST['tag_ids']) ? $_POST['tag_ids'] : [];
foreach ($tag_ids as $tag_id) {
    $sql = "INSERT INTO problem_tag (problem_id, tag_id) VALUES (?, ?)";
    pdo_query($sql, $pid, intval($tag_id));
}



if(strlen($sample_output) && !strlen($sample_input)) $sample_input = "0";
if(strlen($sample_input)) mkdata($pid, "sample.in", $sample_input, $OJ_DATA);
if(strlen($sample_output)) mkdata($pid, "sample.out", $sample_output, $OJ_DATA);
if(strlen($test_output) && !strlen($test_input)) $test_input = "0";
if(strlen($test_input)) mkdata($pid,"test.in", $test_input, $OJ_DATA);
if(strlen($test_output)) mkdata($pid,"test.out", $test_output, $OJ_DATA);
if(isset($_POST['remote_oj'])){
	$remote_oj=$_POST['remote_oj'];
	$remote_id=intval($_POST['remote_id']);
	$sql="update problem set remote_oj=?,remote_id=? where problem_id=?";
	pdo_query($sql,$remote_oj,$remote_id,$pid);
	?>
<form method=POST action=problem_add_page_<?php echo $remote_oj?>.php>
<?php 
	if($remote_oj=="luogu"){
		$pre=mb_strpos($source,"P");
		$pre=mb_substr($source,0,$pre+1);
		$remote_id=intval(mb_substr($_POST['remote_id'],1));
		echo "remote id :$remote_id";
	
	}else{
		$pre=mb_strpos($source,"=");
		$pre=mb_substr($source,0,$pre+1);
	}
?>
<input name=url type=text size=100  class="input input-xxlarge" value="<?php echo $pre.(++$remote_id) ?>">
  <input type=submit>
</form>
<script>// $("form").submit();</script>

	<?php
}

//파이썬 실행
shell_exec("cd /home/Capstone_Design_Troy/button_test/ && python3 button_test.py");
shell_exec("cd /home/Capstone_Design_Troy/test/ && python3 make_question_and_code.py" . escapeshellarg($description, $exemplary_code));
shell_exec("cd /home/Capstonen_Design_Troy/test/ && python3 AIFlowchar.py" . escapeshellarg($problem_id));
//파이썬 실행

$sql = "INSERT INTO `privilege` (`user_id`,`rightstr`) VALUES(?,?)";
pdo_query($sql, $_SESSION[$OJ_NAME.'_'.'user_id'], "p$pid");
$_SESSION[$OJ_NAME.'_'."p$pid"] = true;
  
echo "&nbsp;&nbsp;- <a href='javascript:phpfm($pid);'>Add more TestData now!</a>";
/*  */
?>

<script src='../template/bs3/jquery.min.js' ></script>
<script>
function phpfm(pid){
  //alert(pid);
  $.post("phpfm.php",{'frame':3,'pid':pid,'pass':''},function(data,status){
    if(status=="success"){
      document.location.href="phpfm.php?frame=3&pid="+pid;
    }
  });
}
</script>

