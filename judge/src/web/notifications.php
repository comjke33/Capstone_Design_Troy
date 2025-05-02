<?php
@session_start();
require_once "include/db_info.inc.php"; // DB 연결

$user_id = $_SESSION[$OJ_NAME . '_user_id'];

// 알림 개수
$sql = "SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0";
$result = pdo_query($sql, $user_id);
$new_notification_count = $result[0]['cnt'];

// 필터
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$where_clause = "user_id = ?";
$params = [$user_id];

if ($filter == 'important') {
    $where_clause .= " AND type = 'important'";
} elseif ($filter == 'unread') {
    $where_clause .= " AND is_read = 0";
}

// 알림 목록 가져오기
$sql = "SELECT content, created_at, is_read FROM notifications WHERE $where_clause ORDER BY created_at DESC LIMIT 50";
$notifications = pdo_query($sql, ...$params);

// 일괄 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['mark_all_read'])) {
        pdo_query("UPDATE notifications SET is_read = 1 WHERE user_id = ?", $user_id);
        header("Location: notifications.php");
        exit();
    }
    if (isset($_POST['delete_old'])) {
        pdo_query("DELETE FROM notifications WHERE user_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)", $user_id);
        header("Location: notifications.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>알림 센터</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <style>
        .notification-dot {
            position: absolute;
            top: -7px;
            right: -7px;
            width: 6px;
            height: 6px;
            background-color: red;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="ui container" style="margin-top: 30px;">
        <h2 class="ui header">🔔 알림 센터</h2>

        <p>현재 새로운 알림: <strong><?php echo $new_notification_count; ?>개</strong></p>

        <?php if($new_notification_count > 20){ ?>
            <div class="ui yellow message">
                <i class="exclamation triangle icon"></i> 알림이 많습니다. 중요 알림만 보는 것을 추천합니다.
            </div>
        <?php } ?>
        <?php if($new_notification_count > 50){ ?>
            <div class="ui red message">
                <i class="warning sign icon"></i> 알림이 50개 이상입니다. 오래된 알림을 삭제하는 것을 추천합니다.
            </div>
        <?php } ?>

        <!-- 필터 버튼 -->
        <div class="ui buttons">
            <a href="notifications.php?filter=all" class="ui button <?php if($filter=='all') echo 'blue'; ?>">전체 보기</a>
            <a href="notifications.php?filter=important" class="ui button <?php if($filter=='important') echo 'blue'; ?>">중요 알림</a>
            <a href="notifications.php?filter=unread" class="ui button <?php if($filter=='unread') echo 'blue'; ?>">읽지 않은 알림</a>
        </div>

        <!-- 알림 목록 -->
        <table class="ui celled table" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th>시간</th>
                    <th>내용</th>
                    <th>상태</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($notifications) > 0){ ?>
                    <?php foreach($notifications as $note){ ?>
                        <tr <?php if(!$note['is_read']) echo 'style="background-color: #fff8dc;"'; ?>>
                            <td><?php echo $note['created_at']; ?></td>
                            <td><?php echo htmlspecialchars($note['content']); ?></td>
                            <td><?php echo $note['is_read'] ? '읽음' : '새 알림'; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="3">알림이 없습니다.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 일괄 처리 버튼 -->
        <form method="post" action="notifications.php">
            <button class="ui green button" name="mark_all_read" value="1">전체 읽음 처리</button>
            <button class="ui red button" name="delete_old" value="1">30일 이상 알림 삭제</button>
        </form>
    </div>
</body>
</html>
