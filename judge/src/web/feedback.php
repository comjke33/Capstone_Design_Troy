<?php
require_once('include/db_info.inc.php');

// solution_id를 GET 대신 직접 사용할 수 있는 방법으로 수정
$solution_id = $_POST['solution_id']; // 예를 들어 POST 방식으로 전달한다고 가정

// 기본값 설정
$feedback_error = null;
$link_result = null;
$code = null;  // 코드 값을 저장할 변수

// 유효한 solution_id인지 확인
if ($solution_id <= 0) {
    $feedback_error = "❌ 유효하지 않은 요청입니다.";
} else {
    // solution_id로 코드 가져오기 (solution 테이블에서 코드 찾기)
    $sql = "SELECT code FROM solution WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);

    if ($stmt) {
        // solution_id 바인딩 및 실행
        $stmt->bind_param("i", $solution_id);
        $stmt->execute();
        $stmt->bind_result($code);

        if ($stmt->fetch()) {
            // ✅ 정상적으로 code를 가져옴
            // 이제 $code에는 solution 테이블에서 가져온 코드가 저장됨
        } else {
            // ❌ 해당 solution_id에 대한 코드가 없음
            $feedback_error = "⚠️ 해당 solution_id에 대한 코드가 존재하지 않습니다.";
        }

        $stmt->close();
    } else {
        $feedback_error = "❌ 데이터베이스 오류: 쿼리 준비 실패.";
    }

    // solution_id에 맞는 link 가져오기
    if (!$feedback_error) {
        $sql = "SELECT link FROM hyperlink WHERE solution_id = ?";
        $stmt = $mysqli->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $solution_id);
            $stmt->execute();
            $stmt->bind_result($link);

            if ($stmt->fetch()) {
                // ✅ 정상적으로 link를 가져옴
                $link_result = $link;
            } else {
                // ❌ 해당 solution_id에 대한 링크 없음
                $feedback_error = "⚠️ 해당 풀이에 연결된 피드백 링크가 없습니다.";
            }

            $stmt->close();
        } else {
            $feedback_error = "❌ 데이터베이스 오류: 쿼리 준비 실패.";
        }
    }
}

// code를 바탕으로 파이썬 스크립트 실행
if ($code && !$feedback_error) {
    // 예시: code를 Python 스크립트로 전달
    $command = escapeshellcmd("python3 ../../../py/compile_process.py $code");
    $output = shell_exec($command);
} else {
    $feedback_error = "❌ 코드 실행 오류: 코드가 없습니다.";
}

// 피드백 페이지로 결과 전달
include("template/syzoj/feedback.php");
?>
