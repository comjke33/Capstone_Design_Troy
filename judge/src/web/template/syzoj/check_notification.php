<?php
session_start();
$user_id = $_SESSION['user_id'];

// DB 연결
$conn = new mysqli("localhost", "username", "password", "database");
if ($conn->connect_error) {
    http_response_code(500);
    exit("DB connection failed");
}

$sql = "SELECT alert FROM user_alert WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($alert);
$stmt->fetch();
$stmt->close();

if ($alert == 0) {
    exec("python3 /절대경로/a.py > /dev/null 2>&1 &"); // 백그라운드 실행
}

$conn->close();
http_response_code(200);
?>