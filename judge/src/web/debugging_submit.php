<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("include/db_info.inc.php");
require_once("include/bbcode.php");
require_once("template/syzoj/header.php");

$problem_id = $_GET['problem_id'] ?? '';

// 기본값
$title = $description = $input = $output = $sample_input = $sample_output = '';

if ($problem_id) {
    $sql = "SELECT title, description, input, output, sample_input, sample_output FROM problem WHERE problem_id = ?";
    $rows = pdo_query($sql, [$problem_id]);
    if ($rows && count($rows)) {
        $row = $rows[0];
        $title = $row['title'];
        $description = $row['description'];
        $input = $row['input'];
        $output = $row['output'];
        $sample_input = $row['sample_input'] ?? '';
        $sample_output = $row['sample_output'] ?? '';
    } else {
        $title = "문제를 찾을 수 없습니다.";
    }
}
?>

<script>
window.onload = function () {
    const pid = "<?php echo $problem_id; ?>";
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
    <h2 class="ui dividing header">🛠 결함 코드 훈련 - 문제 <?php echo htmlspecialchars($problem_id); ?>: <?php echo htmlspecialchars($title); ?></h2>

    <div class="ui stackable grid">
        <!-- 왼쪽: 문제 설명 -->
        <div class="eight wide column">
            <div class="ui segment">
                <h4 class="ui dividing header">문제 설명</h4>
                <div class="font-content"><?php echo bbcode_to_html($description); ?></div>

                <h4 class="ui dividing header">입력</h4>
                <div class="font-content"><?php echo bbcode_to_html($input); ?></div>

                <h4 class="ui dividing header">출력</h4>
                <div class="font-content"><?php echo bbcode_to_html($output); ?></div>

                <?php if (trim($sample_input)) { ?>
                    <h4 class="ui dividing header">입력 예시</h4>
                    <pre><?php echo htmlentities($sample_input); ?></pre>
                <?php } ?>

                <?php if (trim($sample_output)) { ?>
                    <h4 class="ui dividing header">출력 예시</h4>
                    <pre><?php echo htmlentities($sample_output); ?></pre>
                <?php } ?>
            </div>
        </div>

        <!-- 오른쪽: 코드 작성 및 제출 -->
        <div class="eight wide column">
            <div class="ui segment">
                <form method="post" action="submit.php" class="ui form">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($problem_id); ?>">
                    <div class="field">
                        <label>언어 선택</label>
                        <select name="language" class="ui dropdown">
                            <option value="0">C</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>코드 수정 후 제출</label>
                        <textarea name="source" id="source" rows="20" style="width:100%; font-family:monospace;"></textarea>
                    </div>
                    <button class="ui primary button" type="submit">제출하기</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once("template/syzoj/footer.php"); ?>