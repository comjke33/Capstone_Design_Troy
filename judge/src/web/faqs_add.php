<?php
// strategy_add.php

require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');
session_start();
$user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'] ?? null;

if (!$user_id) {
    header("Location: loginpage.php"); // 로그인 페이지로 리다이렉트
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 폼 데이터 받아서 DB 저장 처리
    $problem_id = intval($_POST['problem_id']);
    $title = $_POST['title'];
    $description = $_POST['description'];
    $helper_function = $_POST['helper_function'];
    $solution_code = $_POST['solution_code'];

    $sql = "INSERT INTO strategy (problem_id, title, description, helper_function, solution_code, user_id, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    pdo_query($sql, $problem_id, $title, $description, $helper_function, $solution_code, $user_id);
    header("Location: strategy_board.php");
    exit;
}
?>

<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <h2 class="ui dividing header">전략 추가</h2>
    <form class="ui form" method="post" action="strategy_add.php">
        <div class="field">
            <label>문제 ID</label>
            <input type="number" name="problem_id" required>
        </div>
        <div class="field">
            <label>전략 제목</label>
            <input type="text" name="title" required>
        </div>
        <div class="field">
            <label>문제 설명</label>
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
