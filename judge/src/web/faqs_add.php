<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');

$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'] ?? null;

// ✨ 문제 번호를 URL 파라미터 또는 hidden 필드에서 받음
$problem_id = $_GET['problem_id'] ?? $_POST['problem_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $helper_function = $_POST['helper_function'];
    $solution_code = $_POST['solution_code'];

    if (!$user_id || !$problem_id || empty($title)) {
        echo "필수 항목이 누락되었습니다.";
        exit;
    }

    $sql = "INSERT INTO strategy (problem_id, title, description, helper_function, solution_code, user_id) 
            VALUES (?, ?, ?, ?, ?, ?)";
    pdo_query($sql, $problem_id, $title, $description, $helper_function, $solution_code, $user_id);

    header("Location: faqs.php");
    exit;
}
?>


<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <h2 class="ui dividing header">문제풀이 전략 추가</h2>
    <form class="ui form" method="post" action="faqs_add.php">

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
