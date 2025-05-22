<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("include/db_info.inc.php");
require_once("template/syzoj/header.php");

$problem_id = $_GET['problem_id'] ?? '';

// 기본값
$title = $description = $input = $output = '';

// ✅ pdo_query 사용
if ($problem_id) {
    $sql = "SELECT title, description, input, output FROM problem WHERE problem_id = ?";
    $rows = pdo_query($sql, [$problem_id]);
    if ($rows && count($rows)) {
        $row = $rows[0];
        $title = $row['title'];
        $description = $row['description'];
        $input = $row['input'];
        $output = $row['output'];
    } else {
        $title = "문제를 찾을 수 없습니다.";
    }
}
?>
<script>
window.onload = function () {
    const pid = "<?php echo $problem_id; ?>";
    console.log("문제 번호:", pid);

    if (!pid) {
        alert("❌ 문제 번호가 없습니다.");
        return;
    }

    fetch(`get_random_defect_code.php?problem_id=${pid}`)
        .then(res => res.json())
        .then(data => {
            if (data.status === "ok") {
                document.getElementById("source").value = data.code;
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error("❌ fetch 오류:", err);
        });
};
</script>

<div class="ui container">
    <h2>🛠 결함 코드 훈련 - 문제 <?php echo htmlspecialchars($problem_id); ?>: <?php echo htmlspecialchars($title); ?></h2>

    <div class="ui segment">
        <h3 class="ui header">문제 설명</h3>
        <p><?php echo nl2br(htmlspecialchars($description)); ?></p>

        <h3 class="ui header">입력</h3>
        <p><?php echo nl2br(htmlspecialchars($input)); ?></p>

        <h3 class="ui header">출력</h3>
        <p><?php echo nl2br(htmlspecialchars($output)); ?></p>
    </div>

    <form method="post" action="submit.php" class="ui form">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($problem_id); ?>">
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