<?php
// db_connect.php 파일을 불러옵니다
include 'db_connect.php';

// SQL 쿼리 실행
$sql = "SELECT user_id FROM users";
$result = $conn->query($sql);

// 결과 확인
if ($result->num_rows > 0) {
    // 데이터 가져오기
    while($row = $result->fetch_assoc()) {
        echo "user_id: " . $row["user_id"] . "<br>";
    }
} else {
    echo "결과가 없습니다.";
}

// 연결 종료
$conn->close();
?>
