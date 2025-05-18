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

// //파이썬 실행
// shell_exec("cd /home/Capstone_Design_Troy/button_test/ && python3 button_test.py");
// echo "button_test 성공공";
// shell_exec("cd /home/Capstone_Design_Troy/test/ && python3 make_question_and_code.py" . escapeshellarg($description) . ' ' . escapeshellarg($exemplary_code));
// echo "문제, 코드 JSON 생성 성공";
// echo $description;
// echo $exemplary_code;
// //파이썬 실행

// =================순서도 생성 트리거=======================

// // $problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
// $problem_id = "1000"; // 디버깅용
// $problem_id = $pid;

// // 인자를 공백으로 구분해 Python 스크립트에 전달
// $tagged_guideline = "/home/Capstone_Design_Troy/judge/src/web/tagged_guideline/" . $problem_id . ".txt";

// // $tagged_guideline = "../src/web/tagged_guideline/";
// // problem을 가져오는 SQL 쿼리
// // $sql = "SELECT description FROM problem WHERE problem_id = ?";
// // $problem = pdo_query($sql, $problem_id);
// // $desc = $problem[0][0];
// $desc = $description;

// $output_dir = "/home/Capstone_Design_Troy/judge/src/web/flowcharts/";

// echo "<pre>Python 스크립트 실행 중...</pre>";

// $command = "cd /home/Capstone_Design_Troy/py/ && python3 make_flowchart.py "
//     . escapeshellarg($tagged_guideline) . " "
//     . escapeshellarg($desc) . " "
//     . escapeshellarg($output_dir) . " "
//     . escapeshellarg($problem_id);

// $result = shell_exec($command);
// $json_data = json_decode($result, true);

// foreach ($json_data as $index => $row) {
//   $idx = intval($index);
//   $start_line = intval($row['start_line']);
//   $end_line = intval($row['end_line']);

//   // SQL 준비
//   $sql = "INSERT INTO flowchart (problem_id, png_address, png_number, start_num,  end_num) VALUES (?, ?, ?, ?, ?)";
//   $result = pdo_query($sql, $problem_id, $output_dir, $idx, $start_line, $end_line);
// }
// ========================================================
$output_dir = "/home/Capstone_Design_Troy/judge/src/web/flowcharts/";

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

<script>
$.ajax({
    type: "POST",
    url: "../ajax/save_problem_run_python.php",
    dataType: "json",  // JSON으로 응답 받기
    data: {
        description: <?php echo json_encode(htmlspecialchars_decode($description, ENT_QUOTES)); ?>,
        exemplary_code: <?php echo json_encode($exemplary_code); ?>,
        problem_id: <?php echo json_encode($pid); ?>,
        output_dir: <?php echo json_encode($output_dir); ?>,
        post_key: "<?php echo $_SESSION[$OJ_NAME . '_post_key']; ?>"
    }, beforeSend: function(request, settings) {
        // 🔍 전송할 데이터 확인
        console.log("🚀 전송할 데이터:", settings.data);
    },
    success: function(response) {
        console.log("📜 Python Script Response:");
        console.log(response);

        if (Array.isArray(response)) {
            response.forEach((result, idx) => {
                console.log(`▶️ Script ${idx + 1}`);
                console.log("Command:", result.command);
                console.log("Return Code:", result.return_code);
                console.log("Output:", result.output.join("\n"));
            });
        } else if (typeof response === "object" && response !== null) {
            // 객체인 경우 status 등 출력
            if (response.status) {
                console.log(`🟡 상태: ${response.status}`);
            } else {
                console.warn("⚠️ 응답 객체에 예상된 키가 없음:", response);
            }
        } else {
            console.error("⚠️ 알 수 없는 형식의 응답:", response);
        }
    },
    error: function(xhr, status, error) {
        console.error("❌ Python 실행 중 오류 발생:", error);
    }
});
</script>