<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("include/db_info.inc.php");
require_once("template/syzoj/header.php");
require_once("include/bbcode.php");

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

    <div class="ui grid">
  <!-- 📘 문제 설명 -->
  <div class="eight wide column">
    <div class="ui segment">
      <h3 class="ui dividing header"><?php echo $MSG_Description ?></h3>
      <div class="font-content"><?php echo bbcode_to_html($row['description']); ?></div>

      <h4 class="ui header"><?php echo $MSG_Input ?></h4>
      <div class="font-content"><?php echo bbcode_to_html($row['input']); ?></div>

      <h4 class="ui header"><?php echo $MSG_Output ?></h4>
      <div class="font-content"><?php echo bbcode_to_html($row['output']); ?></div>

      <?php if (strlen(trim($row['sample_input'])) > 0) { ?>
      <h4 class="ui header"><?php echo $MSG_Sample_Input ?></h4>
      <pre><?php echo htmlentities($row['sample_input']); ?></pre>
      <?php } ?>

      <?php if (strlen(trim($row['sample_output'])) > 0) { ?>
      <h4 class="ui header"><?php echo $MSG_Sample_Output ?></h4>
      <pre><?php echo htmlentities($row['sample_output']); ?></pre>
      <?php } ?>
    </div>
  </div>

  <!-- 🛠 제출 영역 -->
  <div class="eight wide column">
    <div class="ui segment">
      <form method="post" action="submit.php" class="ui form">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <div class="field">
          <label>언어 선택</label>
          <select name="language" class="ui dropdown">
            <option value="0">C</option>
          </select>
        </div>
        <div class="field">
          <label>코드 입력</label>
          <textarea name="source" rows="20" id="source" style="font-family:monospace;"></textarea>
        </div>
        <button class="ui primary button" type="submit">제출하기</button>
      </form>
    </div>
  </div>
</div>

<?php require_once("template/syzoj/footer.php"); ?>