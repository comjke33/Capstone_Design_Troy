<?php
$md = file_get_contents('ref.md');
$parsed = htmlspecialchars($md); // 단순한 경우, 이건 그냥 플레이스홀더입니다
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>개념 레퍼런스</title>
</head>
<body>
    <pre><?= $parsed ?></pre>
</body>
</html>