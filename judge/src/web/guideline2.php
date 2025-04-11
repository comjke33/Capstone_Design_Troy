<?php
require_once("./include/db_info.inc.php");

// 파일 불러오기
$file_path = "/home/troy0012/test/test.txt";
$file_contents = file_get_contents($file_path);

// 문단 단위로 자르기
$correct_paragraphs = preg_split("/\r?\n\r?\n+/", trim($file_contents));

// 설명 텍스트 생성
$descriptions = array_map(fn($i) => ($i + 1) . ". 문단 작성하기", array_keys($correct_paragraphs));

// 사용자 입력 및 결과 비교
$user_inputs = [];
$results = [];
foreach ($correct_paragraphs as $i => $answer) {
    $input = $_POST["para_$i"] ?? '';
    $user_inputs[$i] = $input;
    $results[$i] = trim($input) === trim($answer);
}

// solution_id (URL 파라미터로 받음)
$sid = $_GET['solution_id'] ?? '';

// 템플릿 호출 (UI 출력은 여기서)
require_once("./template/syzoj/guideline2.php");
