<?php
$OJ_TEMPLATE = "syzoj";
$show_title = "문단 피드백";
$sid = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;

include("template/$OJ_TEMPLATE/header.php");

if (!$sid) {
    echo "<div class='ui warning message'><div class='header'>solution_id가 없습니다.</div></div>";
    include("template/$OJ_TEMPLATE/footer.php");
    exit;
}

// 예시 문단
$paragraphs = [
    [
        'description' => '1. 사용자로부터 양의 정수 n 입력 받기',
        'answer' => "int n;\nscanf(\"%d\", &n);",
        'hint' => "scanf 사용 시 주소 연산자 & 가 필요합니다. 변수 선언은 소문자 int 입니다."
    ],
    [
        'description' => '2. 팩토리얼 값(fact) 계산하기',
        'answer' => "int fact = 1;\nfor(int i = 1; i <= n; i++) {\n    fact *= i;\n}",
        'hint' => "for 문은 1부터 n까지 반복해야 하고, fact는 누적 곱으로 계산됩니다."
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

include("template/$OJ_TEMPLATE/paragraphfeedback.php");
include("template/$OJ_TEMPLATE/footer.php");
