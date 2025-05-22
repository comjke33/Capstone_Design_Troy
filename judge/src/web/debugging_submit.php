<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("include/db_info.inc.php");
require_once("include/bbcode.php");
require_once("template/syzoj/header.php");

$problem_id = $_GET['problem_id'] ?? '';

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
                editor.setValue(data.code, -1); // Ace Editor로 직접 설정
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
        <!-- 문제 설명 (왼쪽) -->
        <div class="six wide column">
            <div class="ui segments">
                <div class="ui top attached block header"><?php echo $MSG_Description ?></div>
                <div class="ui bottom attached segment font-content"><?php echo bbcode_to_html($description); ?></div>

                <div class="ui top attached block header"><?php echo $MSG_Input ?></div>
                <div class="ui bottom attached segment font-content"><?php echo bbcode_to_html($input); ?></div>

                <div class="ui top attached block header"><?php echo $MSG_Output ?></div>
                <div class="ui bottom attached segment font-content"><?php echo bbcode_to_html($output); ?></div>

                <?php if (trim($sample_input)) { ?>
                    <div class="ui top attached block header"><?php echo $MSG_Sample_Input ?></div>
                    <div class="ui bottom attached segment font-content"><pre><?php echo htmlentities($sample_input); ?></pre></div>
                <?php } ?>

                <?php if (trim($sample_output)) { ?>
                    <div class="ui top attached block header"><?php echo $MSG_Sample_Output ?></div>
                    <div class="ui bottom attached segment font-content"><pre><?php echo htmlentities($sample_output); ?></pre></div>
                <?php } ?>
            </div>
        </div>

        <!-- 코드 입력 및 제출 (오른쪽) -->
        <div class="ten wide column">
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
                        <label>코드 입력</label>
                        <!-- 실제 코드는 Ace Editor -->
                        <div id="editor" style="height: 400px; width: 100%; font-family: monospace;"></div>
                        <!-- 제출용 숨겨진 textarea -->
                        <textarea name="source" id="source" style="display: none;"></textarea>
                    </div>
                    <button class="ui primary button" type="submit">제출하기</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Ace Editor 스크립트 및 연동 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js" integrity="sha512-G5TtS78o5gB/ZI6O3hO++0cF/6a3zi6O3cbU1tz4Qs6EJ2Z9lHREac1vKpTCwVhV7i3PXgA+j38AkbKMGKaZDg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
let editor = ace.edit("editor");
editor.setTheme("ace/theme/chrome");
editor.session.setMode("ace/mode/c_cpp");

// 제출 시 Ace editor 내용을 숨겨진 textarea에 복사
document.querySelector("form").addEventListener("submit", function () {
    document.getElementById("source").value = editor.getValue();
});
</script>

<?php require_once("template/syzoj/footer.php"); ?>