<?php
// 상단 헤더와 DB 연결 정보 포함
include("template/syzoj/header.php");
include("include/db_info.inc.php");

// 파일 경로와 파일 내용 불러오기
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path);

// 중첩된 블록을 파싱하고, 들여쓰기 정보를 함께 트리 구조로 변환하는 함수
function parse_blocks_with_loose_text($text, $depth = 0) {
    // 블록 태그 패턴 정의: [type_start(n)] ... [type_end(n)]
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    // 정규식으로 블록 탐색
    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

        // 블록 시작 전 일반 텍스트 처리
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line),
                    'depth' => $depth + $indent_level
                ];
            }
        }

        // 블록 태그 파싱
        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];

        // 자식 블록 시작 전에 파이프 마커 삽입
        array_unshift($blocks, [
            'type' => 'marker',
            'content' => "| {$type}_start({$idx})",
            'depth' => $depth + 1
        ]);

        // 자식 블록 재귀적으로 파싱
        $children = parse_blocks_with_loose_text($content, $depth + 1);

        // 자식 블록 끝에 파이프 추가
        array_push($children, [
            'type' => 'pipe',
            'content' => "|",
            'depth' => $depth + 1
        ]);

        // 결과에 자식 블록 추가
        $blocks = array_merge($blocks, $children);

        $offset = $end_pos;
    }

    // 남은 일반 텍스트 처리
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
            $blocks[] = [
                'type' => 'text',
                'content' => rtrim($line),
                'depth' => $depth + $indent_level
            ];
        }
    }

    return $blocks;
}

// 파싱된 블록 트리를 HTML로 렌더링하는 함수
function render_tree_plain($blocks) {
    $html = "";

    foreach ($blocks as $block) {
        $indent_px = 40 * $block['depth']; // 들여쓰기 계산
        $line = htmlspecialchars($block['content']);

        // 마커는 회색으로 출력
        if ($block['type'] === 'marker') {
            $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px; color: #999;'>$line</div>";

        // 파이프는 흐린 회색으로 출력
        } elseif ($block['type'] === 'pipe') {
            $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px; color: #ddd;'>$line</div>";

        // 일반 텍스트 및 설명 줄
        } else {
            $html .= "<div style='margin-bottom:4px; padding-left: {$indent_px}px; white-space: pre-wrap;'>$line</div>";

            // 블록 태그는 숨기고 설명 문장에만 textarea 추가
            if (!preg_match("/^\\[(func_def|rep|cond|self|struct|construct)_(start|end)\\(\\d+\\)\\]$/", $line)) {
                $html .= "<div style='padding-left: {$indent_px}px;'><textarea rows='2' style='width: calc(100% - {$indent_px}px); margin-bottom: 10px;'></textarea></div>";
            }
        }
    }

    return $html;
}

// GET 요청에서 문제 ID 받아오기
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';
echo "<div class='problem-id' style='font-weight:bold; font-size:18px; margin-bottom: 20px;'>문제 번호: " . htmlspecialchars($sid) . "</div>";

// 트리 파싱 및 렌더링
$block_tree = parse_blocks_with_loose_text($file_contents);
$html_output = render_tree_plain($block_tree);

// 렌더링된 HTML 출력
echo "<div class='code-container' style='font-family: monospace; line-height: 1.5; max-width: 1000px; margin: 0 auto;'>";
echo $html_output;
echo "</div>";

// 하단 템플릿 포함
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
