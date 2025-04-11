<?php
require_once("./include/db_info.inc.php");

$solution_id = isset($_GET['solution_id']) ? intval($_GET['solution_id']) : 0;
$source = "";
$sid = 0;

// ✅ 예제 설명 및 정답
$description = '아래에 "Hello World"를 출력하는 C 코드를 작성하세요.';
$correct_source = 
'#include <stdio.h>

int main() {
    printf("Hello World");
    return 0;
}';

$user_input = $_POST['full_source'] ?? '';
$result = false;

if ($solution_id > 0) {
    $sql = "SELECT solution_id, source FROM source_code WHERE solution_id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $solution_id);
    $stmt->execute();
    $stmt->bind_result($sid, $source);
    $stmt->fetch();
    $stmt->close();
}

// ✅ 정답 판별
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $normalized_input = preg_replace('/\s+/', '', $user_input);
    $normalized_answer = preg_replace('/\s+/', '', $correct_source);
    $result = ($normalized_input === $normalized_answer);
}

// 👉 화면 렌더링
include("template/$OJ_TEMPLATE/guideline3.php");
