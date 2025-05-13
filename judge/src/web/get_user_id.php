<?php
// user_id 값을 가져오는 함수 정의
function getUserIdsFromDatabase() {
    // 데이터베이스 연결
    $db = mysqli_connect("localhost", "username", "password", "database_name"); // 본인의 DB 정보로 수정하세요

    if (!$db) {
        error_log("Connection failed: " . mysqli_connect_error());
        return []; // DB 연결 실패 시 빈 배열 반환
    }

    // user_id만 가져오는 쿼리 (LIMIT 추가)
    $query = "SELECT user_id FROM users LIMIT 1000"; // 데이터를 1000개만 가져옴
    $result = mysqli_query($db, $query);

    if (!$result) {
        error_log("Query failed: " . mysqli_error($db)); // 오류 로그 기록
        mysqli_close($db);
        return []; // 쿼리 실패 시 빈 배열 반환
    }

    // user_id를 저장할 배열 초기화
    $allowed_user_ids = [];

    // 쿼리 결과에서 user_id 값을 배열에 저장
    while ($row = mysqli_fetch_assoc($result)) {
        $allowed_user_ids[] = $row['user_id']; // user_id만 배열에 저장
    }

    // DB 연결 종료
    mysqli_close($db);

    // user_id 배열 반환
    return $allowed_user_ids;
}
