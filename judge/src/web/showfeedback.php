<?php
require_once('./include/db_info.inc.php');
if (isset($mysqli)) {
    echo "✅ DB 연결 성공!";
} else {
    echo "❌ \$mysqli 객체 없음";
}
?>
