<?php
require_once("include/db_info.inc.php");
require_once("template/syzoj/header.php");

$problem_id = $_GET['problem_id'] ?? '';
?>

<script>
console.log("문제 번호:", "<?php echo $problem_id; ?>");
window.onload = function() {
    fetch(`get_random_defect_code.php?problem_id=<?php echo $problem_id; ?>`)
    .then(res => res.json())
    .then(data => {
        if (data.status === "ok") {
            document.getElementById("source").value = data.code;
        } else {
            alert(data.message);
        }
    });
};
</script>

<div class="ui container">
    <h2>🛠 결함 코드 훈련 - 문제 <?php echo $problem_id; ?></h2>
    <form method="post" action="submit.php">
        <input type="hidden" name="id" value="<?php echo $problem_id; ?>">
        <div class="field">
            <label>언어 선택:</label>
            <select name="language">
                <option value="0">C</option>
            </select>
        </div>
        <div class="field">
            <label>코드 수정 후 제출:</label>
            <textarea name="source" id="source" rows="20" style="width:100%; font-family:monospace;"></textarea>
        </div>
        <button type="submit" class="ui primary button">제출하기</button>
    </form>
</div>

<?php require_once("template/syzoj/footer.php"); ?>