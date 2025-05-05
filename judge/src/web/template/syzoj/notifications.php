<?php
@session_start();
require_once "include/db_info.inc.php"; // DB 연결

$user_id = $_SESSION[$OJ_NAME . '_user_id'];

// mistake_type 이름과 코멘트 매핑
$mistake_names = [
    1 => "변수 선언 오류",
    2 => "함수 선언 누락",
    3 => "함수 반환 오류",
    4 => "포인터 오류",
    5 => "배열 인덱스 오류",
    6 => "입출력 형식 지정자 오류",
    7 => "연산자 사용 오류",
    8 => "정수/실수 리터럴 오류",
    9 => "표현식 누락",
    10 => "형 변환 오류",
    11 => "세미콜론 누락",
    12 => "괄호 닫힘 오류",
    13 => "함수 인자 개수/타입 오류",
    14 => "함수 정의 중복",
    15 => "비교 연산자 오류",
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
    9 => "표현식 누락",
    10 => "형 변환 오류",
    11 => "세미콜론 누락",
    12 => "괄호 닫힘 오류",
    13 => "함수 인자 개수/타입 오류",
    14 => "함수 정의 중복",
    15 => "비교 연산자 오류",
    -1 => "기타 오류입니다. 코드 리뷰를 권장합니다."
];

// 현재 기록
$sql_now = "SELECT mistake_type, mistake_count FROM user_weakness_now WHERE user_id = ? AND mistake_count >= 3";
$result_now = pdo_query($sql_now, $user_id);

// 이전 기록(수정예정정)
$sql_prev = "SELECT mistake_type, mistake_count FROM user_weakness_dec WHERE user_id = ? AND mistake_count >= 3";
$result_prev = pdo_query($sql_prev, $user_id);

// Chart.js용 데이터 구성
$labels = [];
$data_now = [];
$data_prev = [];

// 현재 제출 데이터
foreach ($result_now as $row) {
    $labels[] = $mistake_names[$row['mistake_type']];
    $data_now[] = $row['mistake_count'];
}

// 이전 제출 데이터
foreach ($result_prev as $row) {
    $data_prev[] = $row['mistake_count'];
}

// LLM 코멘트 (직접 입력)
$sql_comment = "SELECT comment FROM comment WHERE user_id = ?";
$result_comment = pdo_query($sql_now, $user_id);




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
        <div class="ui message"><?php echo $result_comment; ?></div>
    </div>

    <?php if (count($result_now) > 0) { ?>
        <div class="ui segment">
            <div style="display: flex; gap: 30px; justify-content: space-between; flex-wrap: nowrap;">
                <div style="width: 50%;">
                    <h4 class="ui header">📁 저번 제출</h4>
                    <canvas id="mistakeChartPrev"></canvas>
                </div>
                <div style="width: 50%;">
                    <h4 class="ui header">📌 이번 제출</h4>
                    <canvas id="mistakeChartNow"></canvas>
                </div>
            </div>
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
            <?php foreach ($result_now as $row) {
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
    const labels = <?php echo json_encode($labels); ?>;
    const dataNow = <?php echo json_encode($data_now); ?>;
    const dataPrev = <?php echo json_encode($data_prev); ?>;

    new Chart(document.getElementById('mistakeChartPrev'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '이전 실수 횟수',
                data: dataPrev,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
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

    new Chart(document.getElementById('mistakeChartNow'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '현재 실수 횟수',
                data: dataNow,
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

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>