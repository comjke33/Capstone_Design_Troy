<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');
session_start();
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'] ?? null;

if (!$user_id) {
    header("Location: loginpage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 문제 데이터 받기
    $problem_title = $_POST['problem_title'] ?? '';
    $problem_description = $_POST['problem_description'] ?? '';
    $problem_input = $_POST['problem_input'] ?? '';
    $problem_output = $_POST['problem_output'] ?? '';
    $problem_hint = $_POST['problem_hint'] ?? '';

    // 전략 데이터 받기
    $title = $_POST['title'];
    $description = $_POST['description'];
    $helper_function = $_POST['helper_function'];
    $solution_code = $_POST['solution_code'];

    // 문제 저장
    $sql_insert_problem = "INSERT INTO problem (title, description, input, output, hint) VALUES (?, ?, ?, ?, ?)";
    pdo_query($sql_insert_problem, $problem_title, $problem_description, $problem_input, $problem_output, $problem_hint);

    // 마지막으로 삽입된 문제 id 가져오기
    $problem_id = pdo_lastInsertId();

    // 전략 저장
    $sql_insert_strategy = "INSERT INTO strategy (problem_id, title, description, helper_function, solution_code, user_id) 
                            VALUES (?, ?, ?, ?, ?, ?)";
    pdo_query($sql_insert_strategy, $problem_id, $title, $description, $helper_function, $solution_code, $user_id);

    header("Location: faqs.php");
    exit;
}
?>

<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <h2 class="ui dividing header">문제 및 전략 추가</h2>
    <form class="ui form" method="post" action="faqs_add.php">
        <h4 class="ui dividing header">문제 정보 입력</h4>
        <div class="field">
            <label>문제 제목</label>
            <input type="text" name="problem_title" required>
        </div>
        <div class="field">
            <label>문제 설명</label>
            <textarea name="problem_description" rows="4" required></textarea>
        </div>
        <div class="field">
            <label>문제 입력 예시</label>
            <textarea name="problem_input" rows="4" required></textarea>
        </div>
        <div class="field">
            <label>문제 출력 예시</label>
            <textarea name="problem_output" rows="4" required></textarea>
        </div>
        <div class="field">
            <label>힌트</label>
            <textarea name="problem_hint" rows="3" required></textarea>
        </div>

        <h4 class="ui dividing header">전략 정보 입력</h4>
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
