<?php
require_once("../template/syzoj/header.php");

// solution_id 받기
$solution_id = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;
if (!$solution_id) {
    echo "solution_id가 없습니다.";
    require_once("../template/syzoj/footer.php");
    exit;
}

// 예시 데이터 (향후 DB로 교체 가능)
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

// 사용자 입력/결과 처리
$user_inputs = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($correct_lines as $index => $answer) {
        $input = $_POST["line_$index"] ?? '';
        $user_inputs[$index] = $input;
        $results[$index] = trim($input) === $answer;
    }
}

// 템플릿 파일 include (뷰 역할)
include("../template/syzoj/linefeedback.php");

require_once("../template/syzoj/footer.php");
?>
