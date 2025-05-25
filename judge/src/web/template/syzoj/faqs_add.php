
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em;">
    <h2 class="ui dividing header">문제풀이 전략 추가</h2>

    <form class="ui form" method="post" action="faqs_add.php">
        <h4 class="ui dividing header">풀이전략 정보 입력</h4>

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
