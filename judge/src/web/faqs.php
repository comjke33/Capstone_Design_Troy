<?php 
// 캐시 및 기본 설정
$cache_time=10;
$OJ_CACHE_SHARE=false;
require_once('./include/cache_start.php');
require_once('./include/db_info.inc.php');
require_once('./include/setlang.php');
$view_title = "문제 해결 전략 게시판";

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    // 문제 목록 불러오기
    $sql = "SELECT id, title FROM strategy ORDER BY id DESC";
    $problems = pdo_query($sql);
    include("template/$OJ_TEMPLATE/faqs.php");

} else if ($action === 'detail' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT title, description, sample_code FROM strategy WHERE id=?";
    $res = pdo_query($sql, $id);
    if (count($res) > 0) {
        $problem = $res[0];
        include("template/$OJ_TEMPLATE/faqs_detail.php");
    } else {
        echo "문제를 찾을 수 없습니다.";
    }

} else if ($action === 'verify' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = $_POST['code'] ?? '';
    $problem_id = intval($_POST['problem_id'] ?? 0);

    // 기준 문제 보조 코드 로드
    $sql = "SELECT helper_functions FROM strategy WHERE id=?";
    $res = pdo_query($sql, $problem_id);

    if (count($res) > 0) {
        $helper_funcs = explode("\n", $res[0]['helper_functions']);
        $match_count = 0;

        foreach ($helper_funcs as $func) {
            if (trim($func) !== '' && strpos($submitted_code, trim($func)) !== false) {
                $match_count++;
            }
        }

        $accuracy = count($helper_funcs) > 0 ? ($match_count / count($helper_funcs)) * 100 : 0;
        $result_msg = "확인된 보조 함수 수: $match_count / 정확도: " . round($accuracy, 2) . "%";

        include("template/$OJ_TEMPLATE/faqs_result.php");
    } else {
        echo "문제 정보를 불러올 수 없습니다.";
    }
} else {
    echo "잘못된 접근입니다.";
}

if(file_exists('./include/cache_end.php'))
    require_once('./include/cache_end.php');
?>