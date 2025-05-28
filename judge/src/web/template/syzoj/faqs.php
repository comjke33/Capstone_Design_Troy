
<?php
require_once("./include/db_info.inc.php");
require_once('./include/setlang.php');


// 문제 ID가 GET으로 전달된 경우 필터링 조건 생성
$filter = "";
$params = [];

if (!empty($_GET['problem_id']) && is_numeric($_GET['problem_id'])) {
    $filter = "WHERE problem_id = ?";
    $params[] = intval($_GET['problem_id']);
}

// strategy 테이블에서 목록 조회
$sql = "SELECT * FROM strategy $filter ORDER BY created_at DESC";
$list = pdo_query($sql, ...$params);  // pdo_query는 유틸 함수
?>

<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<div class="ui container" style="margin-top: 3em; margin-bottom: 4em;">

    <div class="ui grid">
        <div class="eight wide column">
            <h2 class="ui dividing header" style="margin-bottom: 0;">
                문제 해결 전략 게시판
            </h2>
        </div>
        <div class="eight wide column" style="text-align: right; padding-top: 1.1em;">
            <a href="faqs_add.php" class="ui teal labeled icon button">
                <i class="plus icon"></i> 전략 추가
            </a>
        </div>
    </div>

    
    <form method="get" class="ui form" style="margin-bottom: 1.5em;">
        <div class="inline fields">
            <label for="problem_id" style="line-height: 2.1;">문제 ID 검색:</label>
            <div class="field">
                <input type="number" id="problem_id" name="problem_id" placeholder="문제 번호 입력" value="<?= htmlspecialchars($_GET['problem_id'] ?? '') ?>">
            </div>
            <div class="field">
                <button class="ui primary button" type="submit">검색</button>
            </div>
        </div>
    </form>

    <?php if (empty($list)): ?>
        <div class="ui warning message">
            <div class="header">전략 없음</div>
            <p>표시할 문제 해결 전략이 없습니다.</p>
        </div>
    <?php else: ?>
        <table class="ui celled striped selectable table" style="font-family: 'Pretendard', sans-serif; font-size: 0.95em;">

            <thead>
                <tr class="center aligned">
                    <th style="width: 20%;">전략 제목</th>
                    <th style="width: 10%;">문제 ID</th>
                    <th style="width: 25%;">문제 설명</th>
                    <th style="width: 20%;">보조 함수</th>
                    <th style="width: 20%;">예제 코드</th>
                    <th style="width: 10%;">상세 보기</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $item): ?>
                    <tr>
                        <td title="<?= htmlspecialchars($item['title']) ?>" style="font-weight: bold;">
                            <?= htmlspecialchars(mb_strimwidth($item['title'], 0, 30, '...')) ?>
                        </td>
                        <td class="center aligned"><?= intval($item['problem_id']) ?></td>
                        <td><?= htmlspecialchars(mb_strimwidth(strip_tags($item['description']), 0, 70, '...')) ?></td>
                        <td style="white-space: pre-wrap; font-family: monospace; font-size: 0.9em;">
                            <?php
                            echo !empty(trim($item['helper_function']))
                                ? nl2br(htmlspecialchars(trim($item['helper_function'])))
                                : '-';
                            ?>
                        </td>
                        <td>
                            <div style="max-height: 90px; overflow-y: auto; background: #f9f9f9; padding: 0.5em; border-radius: 5px; font-family: monospace; font-size: 0.85em;">
                                <code><?= htmlspecialchars(mb_strimwidth($item['solution_code'], 0, 180, '...')) ?></code>
                            </div>
                        </td>
                        <td class="center aligned">
                            <a href="faqs_view.php?id=<?= intval($item['id']) ?>" class="ui mini blue icon button" title="상세 보기">
                                <i class="eye icon"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
