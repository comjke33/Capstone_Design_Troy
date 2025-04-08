<?php
$solution_id = isset($_GET['solution_id']) ? $_GET['solution_id'] : null;
if (!$solution_id) {
    echo "solution_id가 없습니다.";
    exit;
}

// 이후 피드백 로직 수행
echo "선택된 solution_id: " . htmlspecialchars($solution_id);
?>
