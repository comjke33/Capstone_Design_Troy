<?php
// 0. 데이터베이스 연결
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 1. 파일 경로 설정 및 내용 불러오기
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// 2. 중첩 블록 트리 파싱 + 블록 외 문장 포함
function parse_blocks_with_loose_text($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];

        // 블록 앞 일반 텍스트 저장
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            $blocks[] = [
                'type'     => 'text',
                'index'    => null,
                'content'  => $before_text,
                'children' => []
            ];
        }

        $full      = $m[0][0];
        $end_pos   = $start_pos + strlen($full);
        $type      = $m[1][0];
        $idx       = $m[2][0];
        $content   = $m[3][0];
        $children  = parse_blocks_with_loose_text($content);

        $blocks[] = [
            'type'     => $type,
            'index'    => $idx,
            'content'  => $content,
            'children' => $children
        ];

        $offset = $end_pos;
    }

    // 마지막 일반 텍스트 저장
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        $blocks[] = [
            'type'     => 'text',
            'index'    => null,
            'content'  => $tail,
            'children' => []
        ];
    }

    return $blocks;
}

// 3. 블록 트리를 HTML로 렌더링
function render_tree($blocks, $parent_color = '#eeeeee', $depth = 0) {
    $html = "";

    foreach ($blocks as $block) {
        $color_map = [
            'func_def' => '#e0f7fa',
            'rep'      => '#fce4ec',
            'cond'     => '#e8f5e9',
            'self'     => '#fff9c4',
            'struct'   => '#ffecb3',
            'text'     => $parent_color
        ];

        $color  = $color_map[$block['type']];
        $indent = 50 * $depth;

        if (empty($block['children'])) {
            // 태그 제거 및 문장 분리
            $cleaned   = preg_replace("/\\[(func_def|rep|cond|self|struct)_(start|end)\\(\\d+\\)\]/", "", $block['content']);
            $sentences = preg_split('/(?<=\.)\s*/u', trim($cleaned), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($sentences as $s) {
                $s = trim($s);
                if ($s === '') continue;

                $html .= "<div style='
                              margin-bottom: 15px;
                              margin-left: {$indent}px;
                              background: $color;
                              border-radius: 6px;
                              padding: 12px;
                              font-family: monospace;
                          '>";

                $html .= "<div style='margin-bottom: 8px;'>" . htmlspecialchars($s) . "</div>";

                $html .= "<textarea rows='3' style='
                              width: 100%;
                              background: white;
                              border: 1px solid #ccc;
                              border-radius: 4px;
                          '></textarea></div>";
            }
        } else {
            $title = strtoupper($block['type']) . " 블록 (ID: {$block['index']})";
            $html .= "<div style='
                          font-weight: bold;
                          background: $color;
                          padding: 8px 12px;
                          margin-left: {$indent}px;
                          border-radius: 4px;
                          font-family: monospace;
                          margin-bottom: 8px;
                      '>" . htmlspecialchars($title) . "</div>";
            $html .= render_tree($block['children'], $color, $depth + 1);
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
$block_tree   = parse_blocks_with_loose_text($file_contents);
$html_output  = render_tree($block_tree);

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