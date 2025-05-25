<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $problem_id = $_POST['problem_id'] ?? null;
    $problem_id = is_numeric($problem_id) ? (int)$problem_id : null;

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $helper_function = trim($_POST['helper_function'] ?? '');
    $solution_code = trim($_POST['solution_code'] ?? '');

    if (!$user_id || !$problem_id || $title === '' || $description === '') {
        echo "<script>alert('입력값이 부족하거나 로그인되지 않았습니다.'); history.back();</script>";
        exit;
    }

    // 문제 존재 여부 검사
    $check_sql = "SELECT 1 FROM problem WHERE problem_id = ?";
    $check_res = pdo_query($check_sql, $problem_id);
    if (empty($check_res)) {
        echo "<script>alert('❌ 존재하지 않는 문제 ID입니다.'); history.back();</script>";
        exit;
    }

    // INSERT
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

    <div class="field">
        <label>문제 번호</label>
        <input type="text" name="problem_id" required>
    </div>

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
