<?php
@session_start();
require_once "include/db_info.inc.php"; // DB 연결

$user_id = $_SESSION[$OJ_NAME . '_user_id'];

// SQL 쿼리 실행: is_checked가 0인지를 확인
$sql = "SELECT is_checked FROM comment_check WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['is_checked'] == 0) { // is_checked가 0인 경우 (false)
        // Python 스크립트 실행
        $command = escapeshellcmd("cd /home/Capstone_Design_Troy/test && python3 baba.py");
        $output = shell_exec($command);
        echo "Python script executed.";
    } else {
        echo "is_checked is not false.";
    }
} else {
    echo "No record found for user_id.";
}

$stmt->close();
$conn->close();
?>