<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// Step 값 받기
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// Step 버튼 UI
echo "<div class='ui large buttons' style='margin-bottom:2em;'>";
for ($i = 1; $i <= 3; $i++) {
    $active = ($i === $step) ? "style='background-color:#1678c2; color:white;'" : "";
    echo "<a href='guideline.php?step=$i' class='ui blue button' $active>Step $i</a>";
}
echo "</div>";

// Step에 맞게 파일 불러오기
switch ($step) {
    case 1:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code1.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code1.txt";
        break;
    case 2:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code2.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code2.txt";
        break;
    case 3:
        $guideline_file = "/home/Capstone_Design_Troy/test/guideline_code3.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/tagged_code3.txt";
        break;
    default:
        die("Invalid step.");
}

// 파일 경로를 전역변수로 설정해서 guideline2.php로 넘김
$GLOBALS['guideline_file'] = $guideline_file;
$GLOBALS['tagged_file'] = $tagged_file;
$GLOBALS['current_step'] = $step;

// ✅ 여기서 guideline2.php를 include
include("guideline2.php");

include("template/syzoj/footer.php");
?>
