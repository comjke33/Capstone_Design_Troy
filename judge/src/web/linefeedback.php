<?php
$OJ_TEMPLATE = "syzoj";
$show_title = "한 줄 피드백";
$sid = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;

include("template/$OJ_TEMPLATE/header.php");

if (!$sid) {
    echo "<div class='ui warning message'><div class='header'>solution_id가 없습니다.</div></div>";
    include("template/$OJ_TEMPLATE/footer.php");
    exit;
}

// 예시 정답 줄
$correct_lines = [
    'printf("Hello World");'
];

$descriptions = [
    'printf를 이용하여 Hello World 출력하기'
];

// 사용자 입력 처리
$user_inputs = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($correct_lines as $index => $answer) {
        $input = $_POST["line_$index"] ?? '';
        $user_inputs[$index] = $input;
        $results[$index] = trim($input) === $answer;
    }
}

include("template/$OJ_TEMPLATE/linefeedback.php");
include("template/$OJ_TEMPLATE/footer.php");
