<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

$file_path = "/home/Capstone_Design_Troy/test/test.txt";

// 1. 파일 내용 읽기
$file_contents = file_get_contents($file_path);

// 2. 정규 표현식을 사용하여 각 블록을 구분합니다.
// 'func_def_start'로 시작하고 'func_def_end'로 끝나는 함수 블록
preg_match_all("/\[func_def_start\((.*?)\)\](.*?)\[func_def_end\((.*?)\)\]/s", $file_contents, $functions);

// 'rep_start'로 시작하고 'rep_end'로 끝나는 반복문 블록
preg_match_all("/\[rep_start\((.*?)\)\](.*?)\[rep_end\((.*?)\)\]/s", $file_contents, $loops);

// 'cond_start'로 시작하고 'cond_end'로 끝나는 조건문 블록
preg_match_all("/\[cond_start\((.*?)\)\](.*?)\[cond_end\((.*?)\)\]/s", $file_contents, $conditionals);

// 'self_start'로 시작하고 'self_end'로 끝나는 self-contained 블록
preg_match_all("/\[self_start\((.*?)\)\](.*?)\[self_end\((.*?)\)\]/s", $file_contents, $self_blocks);

// 'struct_start'로 시작하고 'struct_end'로 끝나는 구조체 블록
preg_match_all("/\[struct_start\((.*?)\)\](.*?)\[struct_end\((.*?)\)\]/s", $file_contents, $structs);

// 3. 코드 내에서 [ ]로 감싸진 부분 및 <br /> 태그를 제거합니다.
$file_contents = preg_replace("/\[.*?\]/", "", $file_contents); // [ ] 안의 내용 제거
$file_contents = preg_replace("/<br \/>/", "", $file_contents); // <br /> 태그 제거

// 4. 각 블록을 색상별로 출력하기 전에 불필요한 부분을 제거한 후 출력합니다.
echo "<div class='code-container'>";

// 4.1 함수 블록 출력
foreach ($functions[0] as $index => $function) {
    $func_name = htmlspecialchars($functions[1][$index]);
    $function_content = nl2br(htmlspecialchars($functions[2][$index]));
    echo "<div class='code-block function'>";
    echo "<h3>Function: $func_name</h3>";
    echo "<p>$function_content</p>";
    echo "</div>";
}

// 4.2 반복문 블록 출력
foreach ($loops[0] as $index => $loop) {
    $loop_info = htmlspecialchars($loops[1][$index]);
    $loop_content = nl2br(htmlspecialchars($loops[2][$index]));
    echo "<div class='code-block loop'>";
    echo "<h3>Loop: $loop_info</h3>";
    echo "<p>$loop_content</p>";
    echo "</div>";
}

// 4.3 조건문 블록 출력
foreach ($conditionals[0] as $index => $conditional) {
    $conditional_info = htmlspecialchars($conditionals[1][$index]);
    $conditional_content = nl2br(htmlspecialchars($conditionals[2][$index]));
    echo "<div class='code-block conditional'>";
    echo "<h3>Conditional: $conditional_info</h3>";
    echo "<p>$conditional_content</p>";
    echo "</div>";
}

// 4.4 self-contained 블록 출력
foreach ($self_blocks[0] as $index => $self_block) {
    $self_block_info = htmlspecialchars($self_blocks[1][$index]);
    $self_block_content = nl2br(htmlspecialchars($self_blocks[2][$index]));
    echo "<div class='code-block self-block'>";
    echo "<h3>Self Block: $self_block_info</h3>";
    echo "<p>$self_block_content</p>";
    echo "</div>";
}

// 4.5 구조체 블록 출력
foreach ($structs[0] as $index => $struct) {
    $struct_name = htmlspecialchars($struct[1]);
    $struct_content = nl2br(htmlspecialchars($struct[2]));
    echo "<div class='code-block struct'>";
    echo "<h3>Struct: $struct_name</h3>";
    echo "<p>$struct_content</p>";
    echo "</div>";
}

echo "</div>"; // End of code-container

include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
