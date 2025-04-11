<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. problem_id 가져오기 및 검증
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
if ($problem_id <= 0) {
    echo "❌ 잘못된 요청입니다. problem_id 필요합니다.";
    exit;
}

<?php
$file_path = "/home/troy0012/aaa.txt";
$file_contents = file_get_contents($file_path);

if ($file_contents === false) {
    echo "파일을 읽을 수 없습니다.";
} else {
    var_dump($file_contents); // 파일 내용을 출력
    echo nl2br($file_contents); // 줄바꿈을 HTML에서 보이도록 변환
}
?>


?>
