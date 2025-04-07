<?php
$show_title = "$MSG_NEWS - $OJ_NAME";
include("template/$OJ_TEMPLATE/header.php");

// 데이터베이스 연결 설정 (이미 연결이 되어 있다고 가정)
require_once('./include/db_info.inc.php');

// 뉴스 ID 받아오기 (URL에서 전달된 'id' 값을 사용)
$news_id = intval($_GET['id']);  // GET 요청으로 뉴스 아이디 받기

// 뉴스 내용 조회
$sql = "SELECT * FROM news WHERE news_id = ? AND defunct = 'N'";  // 'defunct'가 'N'인 활성화된 뉴스만 조회
$stmt = $pdo->prepare($sql);
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

// 뉴스가 존재하는지 확인
if ($news) {
    $news_title = $news['title'];
    $news_content = $news['content'];
    $news_writer = $news['user_id'];
    $news_date = $news['time'];
} else {
    $news_title = "뉴스를 찾을 수 없습니다.";
    $news_content = "요청한 뉴스가 존재하지 않거나 삭제된 상태입니다.";
    $news_writer = "";
    $news_date = "";
}

?>

<div class="padding">
    <h1><?php echo $news_title; ?></h1>
    <p style="margin-bottom: 5px;">
        <b style="margin-right: 30px;"><i class="edit icon"></i><a class="black-link"
                href="userinfo.php?user=<?php echo $news_writer; ?>"><?php echo $news_writer; ?></a></b>
        <b style="margin-right: 30px;"><i class="calendar icon"></i> <?php echo $news_date; ?></b>
    </p>
    <div class="ui existing segment" style="overflow-y:overlay;">
        <div id="content" class="font-content"><?php echo bbcode_to_html($news_content); ?></div>
    </div>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
