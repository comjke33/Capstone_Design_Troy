<?php
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// ✅ step 파라미터 받기
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$step = max(1, min(3, $step));

// ✅ step별 파일 지정
switch ($step) {
    case 1:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline1.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code1.txt";
        break;
    case 2:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline2.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code2.txt";
        break;
    case 3:
        $guideline_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/guideline3.txt";
        $tagged_file = "/home/Capstone_Design_Troy/test/step1_test_tagged_guideline/tagged_code3.txt";
        break;
    default:
        die("Invalid step.");
}

// ✅ 글로벌 변수로 넘긴다
$GLOBALS['guideline_file'] = $guideline_file;
$GLOBALS['tagged_file'] = $tagged_file;

// ✅ 출력 버퍼 시작
ob_start();

// ✅ guideline2.php 실행 (출력은 버퍼로 저장)
include("guideline2.php");

// ✅ 버퍼 내용을 가져오기
$guideline_content = ob_get_clean();

// ✅ 이제 $guideline_content 변수에 guideline2.php의 출력이 저장되어 있음!

?>

<!-- 여기서 원하는 곳에 렌더링 -->
<div class="ui container" style="margin-top:3em;">
    <h2>📖 Guideline Viewer</h2>
    <?= $guideline_content ?>
</div>

<?php
include("template/syzoj/footer.php");
?>
