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

// 3. 각 블록을 색상별로 출력하기 전에 정규 표현식을 사용해 부분을 처리합니다.

// 함수 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['func_def'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    $output = "";
    foreach ($sentences as $s) {
        $output .= htmlspecialchars($s) . "<br /><textarea rows='2' style='width: 100%;'></textarea><br />";
    }
    return "<div class='code-block function' style='background-color: #e0f7fa; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>함수: {$matches[1]}</h3><p>$output</p></div>";
}, $file_contents);

// 반복문 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['rep'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    $output = "";
    foreach ($sentences as $s) {
        $output .= htmlspecialchars($s) . "<br /><textarea rows='4' style='width: 100%;'></textarea><br />";
    }
    return "<div class='code-block loop' style='background-color: #fce4ec; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>반복문: {$matches[1]}</h3><p>$output</p></div>";
}, $file_contents);

// 조건문 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['cond'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    $output = "";
    foreach ($sentences as $s) {
        $output .= htmlspecialchars($s) . "<br /><textarea rows='4' style='width: 100%;'></textarea><br />";
    }
    return "<div class='code-block conditional' style='background-color: #e8f5e9; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>조건문: {$matches[1]}</h3><p>$output</p></div>";
}, $file_contents);

// self-contained 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['self'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    $output = "";
    foreach ($sentences as $s) {
        $output .= htmlspecialchars($s) . "<br /><textarea rows='4' style='width: 100%;'></textarea><br />";
    }
    return "<div class='code-block self-block' style='background-color: #fff9c4; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>기본 문장: {$matches[1]}</h3><p>$output</p></div>";
}, $file_contents);

// 구조체 블록을 찾아서 출력
$file_contents = preg_replace_callback($patterns['struct'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    $output = "";
    foreach ($sentences as $s) {
        $output .= htmlspecialchars($s) . "<br /><textarea rows='4' style='width: 100%;'></textarea><br />";
    }
    return "<div class='code-block struct' style='background-color: #ffecb3; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>구조체: {$matches[1]}</h3><p>$output</p></div>";
}, $file_contents);


// 4. 코드 내에서 [ ]로 감싸진 부분 제거
$file_contents = preg_replace("/\[(rep_start|rep_end|self_start|self_end|func_def_start|func_def_end|cond_start|cond_end|struct_start|struct_end)\([^\)]*\)\]/", "", $file_contents);

// 전체 코드 출력 (구분된 색상으로)
echo "<div class='code-container' style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto;'>";

// 전체 코드 출력 (정리된 형태로)
echo $file_contents;

echo "</div>"; // End of code-container

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
