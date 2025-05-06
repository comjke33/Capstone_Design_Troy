<?php
// src/web/guideline.php
// 기능만 처리: JS 내에서 호출됨 (동적 로딩 대상)

if (!isset($_GET['step'])) {
    echo "<div class='ui red message'>Step 정보가 없습니다.</div>";
    exit;
}

$step = intval($_GET['step']);
$file = "guideline{$step}.php"; 

if (file_exists($file)) {
    include($file); // 실질적으로 문제 출력을 담당하는 개별 PHP 포함
} else {
    echo "<div class='ui red message'>해당 step 파일이 존재하지 않습니다.</div>";
}
