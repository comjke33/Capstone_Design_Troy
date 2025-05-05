<?php
require_once(__DIR__ . '/Parsedown.php');

$md = file_get_contents(__DIR__ . '/ref.md');
$Parsedown = new Parsedown();
$html = $Parsedown->text($md);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>문법 개념 레퍼런스</title>
</head>
<body style="max-width: 900px; margin: 2rem auto; font-family: sans-serif;">
    <?= $html ?>
</body>
</html>