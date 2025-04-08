<?php
require_once("./header.php");

// solution_id 받기
$solution_id = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;
if (!$solution_id) {
    echo "solution_id가 없습니다.";
    require_once("./footer.php");
    exit;
}

// 문단별 정답/힌트
$paragraphs = [
    [
        'description' => '1. 사용자로부터 양의 정수 n 입력 받기',
        'answer' => "int n;\nscanf(\"%d\", &n);",
        'hint' => "scanf 사용 시 주소 연산자 & 가 필요합니다. 또한 변수 선언은 소문자 int 입니다."
    ],
    [
        'description' => '2. 팩토리얼 값(fact) 계산하기',
        'answer' => "int fact = 1;\nfor(int i = 1; i <= n; i++) {\n    fact *= i;\n}",
        'hint' => "for 문은 1부터 n까지 반복되어야 하며, fact는 누적 곱으로 계산합니다."
    ]
];

$user_inputs = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($paragraphs as $index => $block) {
        $input = $_POST["para_$index"] ?? '';
        $user_inputs[$index] = $input;

        $normalized_input = preg_replace('/\s+/', '', $input);
        $normalized_answer = preg_replace('/\s+/', '', $block['answer']);
        $results[$index] = ($normalized_input === $normalized_answer);
    }
}
?>

<h2>(2단계)</h2>
<form method="post">
    <?php foreach ($paragraphs as $i => $block): ?>
        <div class="step">
            <p><?= $block['description'] ?>
                <?php if (isset($results[$i]) && $results[$i]): ?>
                    ✅
                <?php endif; ?>
            </p>
            <textarea name="para_<?= $i ?>" rows="6" cols="60"
                class="<?= isset($results[$i]) && $results[$i] ? 'correct' : '' ?>"
                <?= isset($results[$i]) && $results[$i] ? 'readonly' : '' ?>
            ><?= htmlspecialchars($user_inputs[$i] ?? '') ?></textarea>
            <br>
            <button type="submit">제출</button>
            <?php if (isset($results[$i]) && !$results[$i] && !empty($user_inputs[$i])): ?>
                <div class="hint">피드백 칸<br><?= $block['hint'] ?></div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</form>

<?php require_once("./footer.php"); ?>
