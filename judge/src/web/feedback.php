<?php
require_once('include/db_info.inc.php');
$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0; // solution_id를 GET 파라미터로 받음
$feedback_error = ""; // 피드백 오류 메시지 초기화
$code = ""; // 코드 초기화

// problem_id가 1051인 solution_id를 가져오기
// $sql = "SELECT solution_id FROM feedback WHERE problem_id = ?"; // problem_id 조건 추가
// $stmt = $mysqli->prepare($sql);

// if ($stmt) {
//     $problem_id = 1051; // problem_id가 1051인 경우를 조회
//     $stmt->bind_param("i", $problem_id); // problem_id를 바인딩 (정수형)
//     $stmt->execute();
//     $stmt->bind_result($solution_id);

//     if ($stmt->fetch()) {
//         // 정상적으로 solution_id를 가져옴
//     } else {
//         $feedback_error = "❌ 해당 problem_id에 대한 solution_id가 없습니다."; // solution_id가 없을 경우 오류 처리
//     }

//     $stmt->close();
// } else {
//     $feedback_error = "❌ 데이터베이스 오류: 쿼리 준비 실패.";
// }

// solution_id로 solution 테이블에서 code 가져오기
if (!$feedback_error && $solution_id > 0) {
    $sql_2 = "SELECT source FROM source_code_user WHERE solution_id = ?";
    $stmt_2 = $mysqli->prepare($sql_2);

    if ($stmt_2) {
        $stmt_2->bind_param("i", $solution_id);
        $stmt_2->execute();
        $stmt_2->bind_result($code);

        if ($stmt_2->fetch()) {
            // 정상적으로 code를 가져옴
        } else {
            $feedback_error = "⚠️ 해당 solution_id에 대한 코드가 없습니다.";
        }

        $stmt_2->close();
    } else {
        $feedback_error = "❌ 데이터베이스 오류: 코드 조회 쿼리 준비 실패.";
    }
}

// solution_id로 source_code 테이블에서 source 가져오기
// if (!$feedback_error && $solution_id > 0) {
//     $sql_3 = "SELECT source FROM source_code WHERE solution_id = ?";
//     $stmt_3 = $mysqli->prepare($sql_3);

//     if ($stmt_3) {
//         $stmt_3->bind_param("i", $solution_id);
//         $stmt_3->execute();
//         $stmt_3->bind_result($source);

//         if ($stmt_3->fetch()) {
//             // 정상적으로 source를 가져옴
//         } else {
//             $feedback_error = "⚠️ 해당 solution_id에 대한 source가 없습니다.";
//         }

//         $stmt_3->close();
//     } else {
//         $feedback_error = "❌ 데이터베이스 오류: source 조회 쿼리 준비 실패.";
//     }
// }

// 인자를 공백으로 구분해 Python 스크립트에 전달
if (!$feedback_error && isset($code)) {
    $command = escapeshellcmd("python3 ../../../py/compile_process.py $code");
    $compile_result = shell_exec($command);
}

// 링크 생성 python 스크립트에 전달
$command = escapeshellcmd("python3 ../../../py/matching_hyperlink.py $compile_result");
$link_result = shell_exec($command);

// solution_id에 해당하는 링크 가져오기
if (!$feedback_error && $solution_id > 0) {
    $sql_4 = "SELECT link FROM hyperlink WHERE solution_id = ?";
    $stmt_4 = $mysqli->prepare($sql_4);

    if ($stmt_4) {
        $stmt_4->bind_param("i", $solution_id);
        $stmt_4->execute();
        $stmt_4->bind_result($link);

        if ($stmt_4->fetch()) {
            // ✅ 정상적으로 link를 가져옴
            $link_result = $link;
        } else {
            // ❌ 해당 solution_id에 대한 링크 없음
            $feedback_error = "⚠️ 해당 풀이에 연결된 피드백 링크가 없습니다.";
        }

        $stmt_4->close();
    } else {
        $feedback_error = "❌ 데이터베이스 오류: 링크 조회 쿼리 준비 실패.";
    }
}

include("template/syzoj/feedback.php");
?>


<!-- // if ($solution_id <= 0) {
//     $feedback_error = "❌ 유효하지 않은 요청입니다.";
// } else {
//     $sql = "SELECT link FROM hyperlink WHERE solution_id = ?";
//     $stmt = $mysqli->prepare($sql);

//     if ($stmt) {
//         $stmt->bind_param("i", $solution_id);
//         $stmt->execute();
//         $stmt->bind_result($link);

//         if ($stmt->fetch()) {
//             // ✅ 정상적으로 link를 가져옴
//             $link_result = $link;
//         } else {
//             // ❌ 해당 solution_id에 대한 링크 없음
//             $feedback_error = "⚠️ 해당 풀이에 연결된 피드백 링크가 없습니다.";
//         }

//         $stmt->close();
//     } else {
//         $feedback_error = "❌ 데이터베이스 오류: 쿼리 준비 실패.";
//     }
// }
// include("template/syzoj/feedback.php");
// ?> -->