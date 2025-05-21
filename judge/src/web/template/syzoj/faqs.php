<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; margin-bottom: 4em;">
    <h2 class="ui dividing header">내 문제 해결 전략 보기</h2>

    <?php
    require_once("./include/db_info.inc.php");

    // 로그인된 사용자 ID 가져오기
    $user_id = $_SESSION[$OJ_NAME . '_' . 'user_id'];

    // 사용자가 제출한 문제 중 맞았거나 틀린 문제 ID 추출
    $sql = "
        SELECT DISTINCT problem_id
        FROM solution
        WHERE user_id = ?
        AND (result = 4 OR result != 4)
    ";
    $solved_problems = pdo_query($sql, $user_id);

    if (empty($solved_problems)) {
        echo '<div class="ui warning message"><div class="header">전략 없음</div><p>아직 제출한 문제가 없습니다.</p></div>';
    } else {
        // 문제 ID 리스트 구성
        $problem_ids = array_column($solved_problems, 'problem_id');
        $placeholders = implode(',', array_fill(0, count($problem_ids), '?'));

        // 문제 ID에 해당하는 전략 가져오기
        $sql = "SELECT * FROM strategy WHERE problem_id IN ($placeholders) ORDER BY created_at DESC LIMIT 50";
        $list = pdo_query($sql, ...$problem_ids);

        if (empty($list)) {
            echo '<div class="ui info message"><div class="header">전략 없음</div><p>제출한 문제에 대한 전략이 아직 등록되지 않았습니다.</p></div>';
        } else {
            ?>
            <table class="ui celled table" style="font-size: 0.95em;">
                <thead>
                    <tr class="center aligned">
                        <th style="width: 18%;">전략 제목</th>
                        <th style="width: 10%;">문제 ID</th>
                        <th style="width: 22%;">문제 설명</th>
                        <th style="width: 20%;">보조 함수</th>
                        <th style="width: 20%;">예제 코드</th>
                        <th style="width: 10%;">문제 이동</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(mb_strimwidth($item['title'], 0, 25, '...')) ?></strong></td>
                            <td class="center aligned"><?php echo intval($item['problem_id']) ?></td>
                            <td><?php echo htmlspecialchars(mb_strimwidth(strip_tags($item['description']), 0, 50, '...')) ?></td>
                            <td>
                                <div style="white-space: pre-wrap;">
                                    <?php
                                    echo !empty(trim($item['helper_function']))
                                        ? nl2br(htmlspecialchars(trim($item['helper_function'])))
                                        : '-';
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div style="max-height: 100px; overflow-y: auto; background: #f4f4f4; padding: 0.7em; border-radius: 6px; font-family: monospace;">
                                    <code><?php echo htmlspecialchars(mb_strimwidth($item['solution_code'], 0, 200, '...')) ?></code>
                                </div>
                            </td>
                            <td class="center aligned">
                                <a class="ui mini blue basic button" href="problem.php?id=<?php echo intval($item['problem_id']) ?>">
                                    보기
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        }
    }
    ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
