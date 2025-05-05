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


// comment_check DB에서 is_checked가 false이면 코멘트 생성, 이전 코멘트 DB이동
if ($row = $result->fetch_assoc()) {
    if ($row['is_checked'] == 0) { // is_checked가 0인 경우 (false)

        // SQL 쿼리 실행: 이전 문법 오류 기록 이동동
        $sql_move_db = "INSERT INTO user_weakness_prev (user_id, mistake_type, mystake_count)
        SELECT user_id, mistake_type, mystake_count
        FROM user_weakness
        WHERE user_id = ?";

        $stmt_move_db = $conn->prepare($sql);
        $stmt_move_db->bind_param("s", $user_id);

        // Python 스크립트 실행
        $command = escapeshellcmd("cd /home/Capstone_Design_Troy/test && python3 make_comment.py");
        $output = shell_exec($command);

        // SQL 쿼리 실행: 생성된 코멘트 삽입입

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