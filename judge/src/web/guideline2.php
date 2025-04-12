<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test.txt";

// 1. 파일 내용 읽기
$file_contents = file_get_contents($file_path);

// 2. 정규 표현식을 사용하여 각 블록을 색상별로 구분합니다.
$patterns = [
    'func_def' => "/\[func_def_start\((.*?)\)\](.*?)\[func_def_end\((.*?)\)\]/s",
    'rep' => "/\[rep_start\((.*?)\)\](.*?)\[rep_end\((.*?)\)\]/s",
    'cond' => "/\[cond_start\((.*?)\)\](.*?)\[cond_end\((.*?)\)\]/s",
    'self' => "/\[self_start\((.*?)\)\](.*?)\[self_end\((.*?)\)\]/s",
    'struct' => "/\[struct_start\((.*?)\)\](.*?)\[struct_end\((.*?)\)\]/s"
];

// 3. 각 블록에 색상을 적용하여 출력하기
echo "<div class='code-container'>";

// 함수 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['func_def'], function($matches) {
    return "<div class='code-block function'><h3>Function: {$matches[1]}</h3><p>" . nl2br(htmlspecialchars($matches[2])) . "</p></div>";
}, $file_contents);

// 반복문 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['rep'], function($matches) {
    return "<div class='code-block loop'><h3>Loop: {$matches[1]}</h3><p>" . nl2br(htmlspecialchars($matches[2])) . "</p></div>";
}, $file_contents);

// 조건문 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['cond'], function($matches) {
    return "<div class='code-block conditional'><h3>Conditional: {$matches[1]}</h3><p>" . nl2br(htmlspecialchars($matches[2])) . "</p></div>";
}, $file_contents);

// self-contained 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['self'], function($matches) {
    return "<div class='code-block self-block'><h3>Self Block: {$matches[1]}</h3><p>" . nl2br(htmlspecialchars($matches[2])) . "</p></div>";
}, $file_contents);

// 구조체 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['struct'], function($matches) {
    return "<div class='code-block struct'><h3>Struct: {$matches[1]}</h3><p>" . nl2br(htmlspecialchars($matches[2])) . "</p></div>";
}, $file_contents);

// 전체 코드 출력 (구분된 색상으로)
echo $file_contents;

echo "</div>"; // End of code-container

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
