<?php
// ✅ src/web/guideline.php
// 기능 담당: 각 step에 맞는 PHP 파일을 불러와 HTML을 반환

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$problem_id = isset($_GET['problem_id']) ? intval($_GET['problem_id']) : 0;
$index = isset($_GET['index']) ? intval($_GET['index']) : -1;

switch ($step) {
    case 1:
        include(__DIR__ . "/guideline1.php");
        break;
    case 2:
        include(__DIR__ . "/guideline2.php");
        break;
    case 3:
        include(__DIR__ . "/guideline3.php");
        break;
    default:
        echo "<div class='ui red message'>존재하지 않는 스텝입니다.</div>";
}
