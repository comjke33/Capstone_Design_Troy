<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");


$file_path = "/home/Capstone_Design_Troy/test/test.txt";

$file_contents = file_get_contents($file_path);
  echo nl2br($file_contents); // 파일 내용 출력


  
?>
