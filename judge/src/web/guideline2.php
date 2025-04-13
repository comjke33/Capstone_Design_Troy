<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. 파일 경로 설정 및 내용 불러오기
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// 2. 들여쓰기 기반 트리 파싱
function parse_indent_blocks($lines) {
    $blocks = [];
    $stack = [];

    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        $indent_level = (strlen($line) - strlen($trimmed)) / 4; // 스페이스 4칸 기준

        $block = [
            'content' => $trimmed,
            'depth' => $indent_level
        ];

        while (!empty($stack) && $indent_level <= end($stack)['depth']) {
            array_pop($stack);
        }

        if (empty($stack)) {
            $blocks[] = &$block;
            $stack[] = &$block;
        } else {
            $parent = &$stack[count($stack) - 1];
            if (!isset($parent['children'])) $parent['children'] = [];
            $parent['children'][] = &$block;
            $stack[] = &$block;
        }
        unset($block);
    }

    return $blocks;
}

// 3. 렌더링 함수
function render_indent_tree($blocks) {
    $html = "";

    foreach ($blocks as $block) {
        $indent = 50 * $block['depth'];
        $text = htmlspecialchars($block['content']);

        $html .= "<div style='
                      margin-left: {$indent}px;
                      margin-bottom: 12px;
                      background: #f2f2f2;
                      border-radius: 6px;
                      padding: 10px;
                      font-family: monospace;
                  '>" . $text . "</div>";

        $html .= "<textarea rows='3' style='
                      width: 100%;
                      margin-left: {$indent}px;
                      margin-bottom: 20px;
                      border: 1px solid #ccc;
                      border-radius: 4px;
                  '></textarea>";

        if (isset($block['children'])) {
            $html .= render_indent_tree($block['children']);
        }
    }

    return $html;
}

// 4. 문제 번호 출력
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo '<div class="problem-id" style="
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 20px;
        ">
        문제 번호: ' . htmlspecialchars($sid) . '
      </div>';

// 5. 파싱 및 렌더링 실행
$block_tree   = parse_indent_blocks($file_contents);
$html_output  = render_indent_tree($block_tree);

// 6. HTML 출력
echo "<div class='code-container' style='
          font-family: Arial, sans-serif;
          line-height: 1.6;
          max-width: 1000px;
          margin: 0 auto;
      '>";
echo $html_output;
echo "</div>";

// 7. 하단 템플릿 포함
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
