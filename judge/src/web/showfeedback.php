<?php
require_once("./include/db_info.inc.php");

$sid = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
$source = "";
$result = false;

// 예제 문제 및 정답
$description = "'Hello World'를 출력하는 C 코드를 작성하시오.";
$correct_source = '#include <stdio.h>

int main() {
    printf("Hello World");
    return 0;
}';

$user_input = $_POST['full_source'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $normalized_input = preg_replace('/\s+/', '', $user_input);
    $normalized_answer = preg_replace('/\s+/', '', $correct_source);
    $result = ($normalized_input === $normalized_answer);
}

include("template/$OJ_TEMPLATE/showfeedback.php");
