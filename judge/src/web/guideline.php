<?php
// JS fetch()에서 이 파일을 통해 guideline1/2/3.php 중 하나를 include 하도록 구성

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$file = __DIR__ . "/guideline{$step}.php";

if (file_exists($file)) {
    include($file);
} else {
    echo "<div class='ui red message'>해당 step 파일이 존재하지 않습니다.</div>";
}
