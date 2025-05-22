<?php
// 에러 출력 설정 (개발 시에만 켜두세요)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 외부 파일 로드
require_once("../include/db_info.inc.php"); // DB 설정 포함
require_once("template/syzoj/header.php");  // 헤더 포함

// GET 파라미터 확인
$problem_id = $_GET['problem_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>결함 코드 훈련 - 문제 <?php echo htmlspecialchars($problem_id); ?></title>
</head>
<body>

<script>
window.onload = function () {
    const pid = "<?php echo $problem_id; ?>";
    console.log("문제 번호:", pid);

    if (!pid) {
        alert("❌ 문제 번호가 없습니다.");
        return;
    }

    fetch(`get_random_defect_code.php?problem_id=${pid}`)
        .then(res => {
            if (!res.ok) {
                throw new Error("서버 응답 오류: " + res.status);
            }
            return res.json();
        })
        .then(data => {
            if (data.status === "ok") {
                document.getElementById("source").value = data.code;
            } else {
                alert("⚠️ " + data.message);
            }
        })
        .catch(error => {
            console.error("❌ fetch 오류:", error);
            alert("❌ 서버 오류로 결함 코드를 불러올 수 없습니다.");
        });
};
</script>

<div class="ui container">
    <h2>🛠 결함 코드 훈련 - 문제 <?php echo htmlspecialchars($problem_id); ?></h2>
    <form method="post" action="submit.php">
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

</body>
</html>