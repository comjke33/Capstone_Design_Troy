<?php include("template/$OJ_TEMPLATE/header.php");?>

<div class="ui container" style="margin-top: 2em">
    <h2 class="ui dividing header">문제 해결 전략 게시판</h2>

    <?php
    require_once("./include/db_info.inc.php");
    $sql = "SELECT * FROM strategy ORDER BY created_at DESC LIMIT 50";
    $list = pdo_query($sql);
    ?>

    <?php if (!isset($list) || !is_array($list) || count($list) === 0): ?>
        <div class="ui warning message">
            <div class="header">전략 없음</div>
            <p>표시할 문제 해결 전략이 없습니다.</p>
        </div>
    <?php else: ?>
        <table class="ui celled striped table">
            <thead>
                <tr>
                    <th>전략 제목</th>
                    <th>기초 문제 ID</th>
                    <th>문제 설명</th>
                    <th>보조 함수 목록</th>
                    <th>예제 코드</th>
                    <th>자세히 보기</th>
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
                        <td><?php echo $item['problem_id'] ?></td>
                        <td><?php echo mb_substr(strip_tags($item['description']), 0, 50) ?>...</td>
                        <td>
                            <ul>
                                <?php
                                if (!empty($item['helper_function'])) {
                                    $funcs = explode("\n", $item['helper_function']);
                                    foreach ($funcs as $func) {
                                        echo '<li>' . htmlspecialchars(trim($func)) . '</li>';
                                    }
                                } else {
                                    echo '<li>-</li>';
                                }
                                ?>
                            </ul>
                        </td>
                        <td>
                            <pre style="overflow-x: auto; max-width: 300px; font-size: 0.9em;">
<?php echo htmlspecialchars(mb_substr(strip_tags($item['solution_code']), 0, 200)) ?>...</pre>
                        </td>
                        <td>
                            <a class="ui primary mini button" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                전략 보기
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>