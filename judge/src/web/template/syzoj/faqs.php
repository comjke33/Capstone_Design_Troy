<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 2em; margin-bottom: 3em;">
    <h2 class="ui center aligned icon header">
        <i class="lightbulb outline icon"></i>
        문제 해결 전략 게시판
        <div class="sub header">기초 문제와 함께 자주 사용하는 보조 함수 및 예제 코드를 제공합니다.</div>
    </h2>

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
        <div class="ui segment">
            <table class="ui celled striped table">
                <thead class="center aligned">
                    <tr>
                        <th><i class="file alternate outline icon"></i>전략 제목</th>
                        <th><i class="hashtag icon"></i>기초 문제 ID</th>
                        <th><i class="book icon"></i>문제 설명</th>
                        <th><i class="code icon"></i>보조 함수 목록</th>
                        <th><i class="terminal icon"></i>예제 코드</th>
                        <th><i class="eye icon"></i>자세히 보기</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td>
                                <a class="ui header" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                    <?php echo htmlspecialchars($item['title']) ?>
                                </a>
                            </td>
                            <td><?php echo $item['problem_id'] ?></td>
                            <td><?php echo nl2br(htmlspecialchars(mb_substr(strip_tags($item['description']), 0, 50))) ?>...</td>
                            <td>
                                <div class="ui list">
                                    <?php
                                    if (!empty($item['helper_function'])) {
                                        $funcs = explode("\n", $item['helper_function']);
                                        foreach ($funcs as $func) {
                                            echo '<div class="item"><i class="angle right icon"></i>' . htmlspecialchars(trim($func)) . '</div>';
                                        }
                                    } else {
                                        echo '<div class="item">-</div>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div style="max-height: 140px; overflow-y: auto;">
                                    <pre style="background-color: #f9f9f9; padding: 0.8em; border-radius: 6px; font-size: 0.85em; max-width: 320px;"><?php echo htmlspecialchars(mb_substr(strip_tags($item['solution_code']), 0, 200)) ?>...</pre>
                                </div>
                            </td>
                            <td class="center aligned">
                                <a class="ui small primary button" href="faqs.php?action=detail&id=<?php echo $item['id'] ?>">
                                    보기
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
