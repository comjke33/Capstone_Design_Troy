<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');

// 로그인 사용자 ID 가져오기
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'] ?? null;

// 문제 번호 가져오기 (GET 또는 POST)
$problem_id = $_GET['problem_id'] ?? $_POST['problem_id'] ?? null;
$problem_id = is_numeric($problem_id) ? (int)$problem_id : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST 데이터 수신
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $helper_function = trim($_POST['helper_function'] ?? '');
    $solution_code = trim($_POST['solution_code'] ?? '');

    // 필수값 확인
    if ($title === '' || $description === '') {
        echo "<script>alert('입력값이 부족하거나 로그인되지 않았습니다.'); history.back();</script>";
        exit;
    }

    // INSERT 쿼리 실행
    $sql = "INSERT INTO strategy (problem_id, title, description, helper_function, solution_code, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    pdo_query($sql, $problem_id, $title, $description, $helper_function, $solution_code, $user_id);

    // 등록 완료 후 페이지 이동
    header("Location: faqs.php");
    exit;
}
?>

<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <h2 class="ui dividing header">문제풀이 전략 추가</h2>
    <form class="ui form" method="post" action="faqs_add.php">

        <!-- 문제 번호 hidden으로 전달 -->
        <input type="hidden" name="problem_id" value="<?= htmlspecialchars($problem_id) ?>">

        <h4 class="ui dividing header">풀이전략 정보 입력</h4>

        <div class="field">
            <label>전략 제목</label>
            <input type="text" name="title" required>
        </div>

        <div class="field">
            <label>전략 설명</label>
            <textarea name="description" rows="4" required></textarea>
        </div>

        <div class="field">
            <label>보조 함수</label>
            <textarea name="helper_function" rows="3"></textarea>
        </div>

        <div class="field">
            <label>예제 코드</label>
            <textarea name="solution_code" rows="6"></textarea>
        </div>

        <button class="ui primary button" type="submit">등록</button>
    </form>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
