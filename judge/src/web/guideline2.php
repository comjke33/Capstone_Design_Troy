<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test.txt";

// 1. 파일 내용 읽기
$file_contents = file_get_contents($file_path);

// 2. 정규 표현식 정의
$patterns = [
    'func_def' => "/\[func_def_start\((.*?)\)\](.*?)\[func_def_end\((.*?)\)\]/s",
    'rep' => "/\[rep_start\((.*?)\)\](.*?)\[rep_end\((.*?)\)\]/s",
    'cond' => "/\[cond_start\((.*?)\)\](.*?)\[cond_end\((.*?)\)\]/s",
    'self' => "/\[self_start\((.*?)\)\](.*?)\[self_end\((.*?)\)\]/s",
    'struct' => "/\[struct_start\((.*?)\)\](.*?)\[struct_end\((.*?)\)\]/s"
];

// 🔷 공통 블록 처리 함수
function render_block($title, $color, $sentences, $textarea_rows = 2) {
    $output = "";
    foreach ($sentences as $s) {
        if (trim($s) === "") continue;

        // HTML 태그가 포함된 문자열 제거
        if (preg_match('/<(\/)?(textarea|div)[^>]*>/i', $s) || preg_match('/&lt;.*textarea.*&gt;/i', $s)) continue;

        $output .= "<div style='margin-bottom: 10px;'>" . htmlspecialchars($s) . "</div><textarea rows='$textarea_rows' style='width: 100%;'>";
    }

    // 마지막에 불필요한 빈 줄 제거
    $output = preg_replace("/<div[^>]*>\s*<\/div>/", "", $output);

    return "<div class='code-block' style='background-color: $color; padding: 15px; margin-bottom: 20px; border-radius: 8px;'><h3>$title</h3>" . rtrim($output) . "</div>";
}

// 🔷 각 블록별 적용
$file_contents = preg_replace_callback($patterns['func_def'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/u', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    return render_block("함수: {$matches[1]}", "#e0f7fa", $sentences, 2);
}, $file_contents);

$file_contents = preg_replace_callback($patterns['rep'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/u', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    return render_block("반복문: {$matches[1]}", "#fce4ec", $sentences, 4);
}, $file_contents);

$file_contents = preg_replace_callback($patterns['cond'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/u', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    return render_block("조건문: {$matches[1]}", "#e8f5e9", $sentences, 4);
}, $file_contents);

$file_contents = preg_replace_callback($patterns['self'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/u', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    return render_block("기본 문장: {$matches[1]}", "#fff9c4", $sentences, 4);
}, $file_contents);

$file_contents = preg_replace_callback($patterns['struct'], function($matches) {
    $sentences = preg_split('/(?<=\.)\s*/u', trim($matches[2]), -1, PREG_SPLIT_NO_EMPTY);
    return render_block("구조체: {$matches[1]}", "#ffecb3", $sentences, 4);
}, $file_contents);

// 🔷 태그 포함된 줄 전체 제거
$file_contents = preg_replace(
    "/^.*\[(rep_start|rep_end|self_start|self_end|func_def_start|func_def_end|cond_start|cond_end|struct_start|struct_end)\([^\)]*\)\].*$(\r?\n)?/m",
    "",
    $file_contents
);

// 🔷 전체 출력
echo "<div class='code-container' style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 1000px; margin: 0 auto;'>";
echo $file_contents;
echo "</div>";

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
