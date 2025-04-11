<?php
$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// 반복문 패턴을 정규 표현식으로 추출
preg_match_all("/rep_start\((.*?)\)(.*?)rep_end\((.*?)\)/s", $file_contents, $matches);

// 각 반복문을 처리
foreach ($matches[0] as $index => $match) {
    $rep_start = $matches[1][$index]; // rep_start 안의 숫자
    $loop_content = trim($matches[2][$index]); // 반복문 안의 내용
    $rep_end = $matches[3][$index]; // rep_end 안의 숫자

    echo "<div class='loop-box'>";
    echo "<h3>반복문 $rep_start</h3>";
    echo "<p>" . nl2br($loop_content) . "</p>";
    echo "<p>반복문 끝: $rep_end</p>";
    echo "</div>";
}
?>
