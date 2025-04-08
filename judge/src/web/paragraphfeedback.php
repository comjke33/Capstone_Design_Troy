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
        'description' => 'printf를 이용하여 Hello World 출력하기',
        'answer' => "printf("Hello World");",
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
