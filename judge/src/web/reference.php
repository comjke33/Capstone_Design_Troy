
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/ParsedownExtension.php');

$md = file_get_contents(__DIR__ . '/ref.md');
$Parsedown = new ParsedownExtension();
$Parsedown->setSafeMode(true);
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