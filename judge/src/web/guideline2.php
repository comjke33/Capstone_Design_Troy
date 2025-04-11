<?php
$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// 함수 정의 추출
preg_match_all("/func_def_start\((.*?)\)(.*?)func_def_end\((.*?)\)/s", $file_contents, $functions);

// 함수별로 HTML로 출력
foreach ($functions[0] as $index => $function) {
    $func_name = extract_function_name($function);
    $function_content = $functions[2][$index];
    echo "<div class='function-box'>";
    echo "<h3>Function: $func_name</h3>";
    echo "<p>" . nl2br($function_content) . "</p>";
    echo "</div>";
}

// 반복문 추출
preg_match_all("/rep_start\((.*?)\)(.*?)rep_end\((.*?)\)/s", $file_contents, $loops);

// 반복문별로 HTML로 출력
foreach ($loops[0] as $index => $loop) {
    $loop_info = extract_loop_info($loop);
    $loop_content = $loops[2][$index];
    echo "<div class='loop-box'>";
    echo "<h3>Loop: $loop_info</h3>";
    echo "<p>" . nl2br($loop_content) . "</p>";
    echo "</div>";
}

// 함수 이름 추출
function extract_function_name($function) {
    preg_match("/func_def_start\((.*?)\)/", $function, $matches);
    return $matches[1] ?? 'Unknown Function';
}

// 반복문 정보 추출
function extract_loop_info($loop) {
    preg_match("/rep_start\((.*?)\)/", $loop, $matches);
    return $matches[1] ?? 'Unknown Loop';
}
?>
