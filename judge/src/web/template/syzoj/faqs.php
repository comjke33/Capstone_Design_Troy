<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; margin-bottom: 4em;">
    <h2 class="ui dividing header">문제 해결 전략 게시판</h2>

    <?php
    require_once("./include/db_info.inc.php");
    $sql = "SELECT * FROM strategy ORDER BY created_at DESC LIMIT 50";
    $list = pdo_query($sql);
    ?>

    <?php if (empty($list)): ?>
        <div class="ui warning message">
            <div class="header">전략 없음</div>
            <p>표시할 문제 해결 전략이 없습니다.</p>
        </div>
    <?php else: ?>
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
                        <td>
                            <strong><?php echo htmlspecialchars(mb_strimwidth($item['title'], 0, 25, '...')) ?></strong>
                        </td>
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
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
