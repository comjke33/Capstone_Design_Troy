<?php
$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// 패턴을 수정하여 반복문과 self_end 구문을 정확히 추출
preg_match_all("/rep_start\((.*?)\)(.*?)rep_end\((.*?)\)/s", $file_contents, $loops);
preg_match_all("/self_end\((.*?)\)/s", $file_contents, $self_ends);

// 반복문을 HTML로 출력
foreach ($loops[0] as $index => $loop) {
    $loop_info = extract_loop_info($loop);
    $loop_content = $loops[2][$index];
    echo "<div class='loop-box'>";
    echo "<h3>Loop: $loop_info</h3>";
    echo "<p>" . nl2br($loop_content) . "</p>";
    echo "</div>";
}

// self_end 처리
foreach ($self_ends[0] as $self_end) {
    echo "<div class='self-end-box'>";
    echo "<h3>Self End</h3>";
    echo "<p>" . nl2br($self_end) . "</p>";
    echo "</div>";
}

// 루프 정보 추출
function extract_loop_info($loop) {
    preg_match("/rep_start\((.*?)\)/", $loop, $matches);
    return $matches[1] ?? 'Unknown Loop';
}
?>
