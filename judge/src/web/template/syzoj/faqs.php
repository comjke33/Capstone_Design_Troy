<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 2em">
    <h2 class="ui header" style="font-weight: 500;">문제 해결 전략 게시판</h2>

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
        <table class="ui very basic table" style="font-size: 0.95em;">
            <thead>
                <tr>
                    <th style="width: 18%;">전략 제목</th>
                    <th style="width: 10%;">문제 ID</th>
                    <th style="width: 22%;">문제 설명</th>
                    <th style="width: 20%;">보조 함수</th>
                    <th style="width: 20%;">예제 코드</th>
                    <th style="width: 10%; text-align: center;">보기</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $item): ?>
                    <tr>
                        <td>
                            <a href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                <?php echo htmlspecialchars($item['title']) ?>
                            </a>
                        </td>
                        <td><?php echo intval($item['problem_id']) ?></td>
                        <td><?php echo mb_substr(strip_tags($item['description']), 0, 50) ?>...</td>
                        <td>
                            <?php
                            if (!empty($item['helper_function'])) {
                                echo nl2br(htmlspecialchars(trim($item['helper_function'])));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <div style="max-height: 120px; overflow-y: auto; background: #f9f9f9; padding: 0.5em; border-radius: 4px;">
                                <code style="white-space: pre-wrap;"><?php echo htmlspecialchars(mb_substr($item['title'], 0, 20)) ?>...</code>
                            </div>
                        </td>
                            <td style="text-align: center;">
                            <a class="ui mini basic button" href="problem.php?id=<?php echo intval($item['problem_id']) ?>">
                                문제 이동
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
