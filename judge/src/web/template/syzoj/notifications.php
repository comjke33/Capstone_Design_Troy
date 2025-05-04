<?php
@session_start();
require_once "include/db_info.inc.php"; // DB 연결

$user_id = $_SESSION[$OJ_NAME . '_user_id'];

// mistake_type 이름과 코멘트 매핑
$mistake_names = [
    1 => "변수 선언 오류",
    2 => "함수 반환 오류",
    3 => "포인터 오류",
    4 => "배열 인덱스 오류",
    5 => "입출력 형식 오류",
    6 => "연산자 사용 오류",
    7 => "정수/실수 리터럴 오류",
    8 => "표현식 누락",
    -1 => "기타 오류"
];

$mistake_comments = [
    1 => "변수를 선언할 때 오타나 누락이 없었는지 다시 확인하세요.",
    2 => "함수가 값을 제대로 반환하는지 점검해보세요.",
    3 => "포인터 사용 전에 초기화했는지 꼭 확인하세요.",
    4 => "배열의 인덱스 범위를 초과하지 않았는지 체크하세요.",
    5 => "scanf/printf의 형식 지정자를 다시 점검하세요.",
    6 => "비교 및 산술 연산자 사용을 주의하세요.",
    7 => "숫자 리터럴 표기법에 오류가 없는지 확인하세요.",
    8 => "표현식을 빠뜨리지 않았는지 확인하세요.",
    -1 => "기타 오류입니다. 코드 리뷰를 권장합니다."
];

// 15개 이상 틀린 영역 조회
$sql = "SELECT mistake_type, mistake_count FROM user_weakness WHERE user_id = ? AND mistake_count >= 3";
$result = pdo_query($sql, $user_id);
?>

// Chart.js용 데이터 구성
$labels = [];
$data = [];
foreach ($result as $row) {
    $labels[] = $mistake_names[$row['mistake_type']];
    $data[] = $row['mistake_count'];
}

// LLM 코멘트 (직접 입력)
$ai_comment = "최근 반복된 실수들을 보면 포인터와 배열 관련 오류가 빈번합니다. 해당 개념을 집중적으로 복습해보세요!";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>취약 유형 리포트</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="ui container" style="margin-top: 30px;">
    <h2 class="ui header">📊 나의 취약 유형 리포트</h2>

    <div class="ui segment">
        <h4 class="ui header">💬 AI 코멘트</h4>
        <div class="ui message"><?php echo $ai_comment; ?></div>
    </div>

    <?php if (count($result) > 0) { ?>
        <div class="ui segment">
            <canvas id="mistakeChart"></canvas>
        </div>

        <table class="ui celled table">
            <thead>
            <tr>
                <th>취약 유형</th>
                <th>실수 횟수</th>
                <th>코멘트</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($result as $row) {
                $type = $row['mistake_type']; ?>
                <tr>
                    <td><?php echo $mistake_names[$type]; ?></td>
                    <td><?php echo $row['mistake_count']; ?></td>
                    <td><?php echo $mistake_comments[$type]; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <div class="ui positive message">
            현재 15회 이상 반복된 취약 유형이 없습니다. 잘하고 계십니다!
        </div>
    <?php } ?>
</div>

<script>
    const ctx = document.getElementById('mistakeChart');
    const mistakeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: '실수 횟수',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => `${ctx.raw}회` } }
            }
        }
    });
</script>

</body>
</html>


<?php include("template/$OJ_TEMPLATE/footer.php");?>