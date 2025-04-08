<?php
// solution_id 받기
$solution_id = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;
if (!$solution_id) {
    echo "solution_id가 없습니다.";
    exit;
}

// 예시: 실제로는 DB에서 받아올 부분
$correct_lines = [
    'int n;',
    'scanf("%d", &n);',
    'int result = 1;',
    'for(int i = 1; i <= n; i++) result *= i;',
];

$descriptions = [
    '입력받을 변수 n 선언',
    '변수 n에 입력 받기',
    '팩토리얼 값을 저장할 변수 선언 및 초기화',
    '팩토리얼 계산하기'
];

// 사용자가 제출한 답 확인
$user_inputs = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($correct_lines as $index => $answer) {
        $input = $_POST["line_$index"] ?? '';
        $user_inputs[$index] = $input;
        $results[$index] = trim($input) === $answer;
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>한 줄씩 피드백</title>
    <style>
        .correct { background-color: #d4edda; }
        .hint { color: gray; font-size: 0.9em; }
        .step { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h3>(1단계)</h3>
    <form method="post">
        <?php foreach ($correct_lines as $i => $line): ?>
        <div class="step">
            <p><?= ($i+1) . '. ' . $descriptions[$i] ?>
                <?php if (isset($results[$i]) && $results[$i]): ?>
                    ✅
                <?php endif; ?>
            </p>
            <textarea name="line_<?= $i ?>" rows="2" cols="40"
                class="<?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
                <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>
            ><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
            <button type="submit" name="submit" value="<?= $i ?>">제출</button>
            <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
                <div class="hint">힌트: Scanf를 사용할 때는 변수 앞에 ~~</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </form>
</body>
</html>
