<?php
// ✅ 헤더 파일 포함 (공통 레이아웃 구성 등)
include("template/syzoj/header.php");

// ✅ 데이터베이스 연결 설정 포함
include("include/db_info.inc.php");

// ✅ 문제 설명 텍스트 파일 경로
$file_path = "/home/Capstone_Design_Troy/test/guideline_code1.txt";
$file_contents = file_get_contents($file_path); // ✅ 누락된 부분 보완

// ✅ 정답 코드 줄 단위로 불러오기
$txt_path = "/home/Capstone_Design_Troy/test/tagged_code1.txt";
$txt_contents = file_get_contents($txt_path);


// ✅ 문제 파일 파싱 함수 정의
function parse_blocks_with_loose_text($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;

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

        $type = $m[1][0];
        $idx = $m[2][0];
        $content = $m[3][0];

        $start_tag = "[{$type}_start({$idx})]";
        $end_tag = "[{$type}_end({$idx})]";

        $children = parse_blocks_with_loose_text($content, $depth + 1);
        array_unshift($children, [
            'type' => 'text',
            'content' => $start_tag,
            'depth' => $depth + 1
        ]);
        array_push($children, [
            'type' => 'text',
            'content' => $end_tag,
            'depth' => $depth + 1
        ]);

        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => $children
        ];

        $offset = $end_pos;
    }

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

function render_tree_with_answers($problem_blocks, $answer_blocks, &$answer_index = 0) {
    $html = '';
    foreach ($problem_blocks as $i => $pblock) {
        $indent_px = 10 * $pblock['depth'];

        if (isset($pblock['children'])) {
            $atype = $pblock['type'];
            $html .= "<div class='block-wrap block-{$atype}' style='margin-left: {$indent_px}px;'>";
            $html .= render_tree_with_answers($pblock['children'], $answer_blocks[$i]['children'] ?? [], $answer_index);
            $html .= "</div>";
        } else {
            $line = htmlspecialchars($pblock['content']);
            if (preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $line)) {
                $html .= "<div style='margin-bottom:8px; padding-left: {$indent_px}px;'>‍‍‍‍️️️️</div>";
            } else {
                $ans = htmlspecialchars($answer_blocks[$i]['content'] ?? '');
                $disabled = $answer_index > 0 ? "disabled" : "";
                $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                $html .= "<div style='flex:1'>";
                $html .= "<div class='code-line'>{$line}</div>";
                $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}>{$ans}</textarea>";
                $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button' {$disabled}>제출</button>";
                $html .= "</div><div style='width:50px; text-align:center; margin-top:20px;'><span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span></div>";
                $html .= "</div>";
                $answer_index++;
            }
        }
    }
    return $html;
}


// ✅ 파라미터에서 문제 ID 획득
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';

// ✅ 트리 구조 파싱
$block_tree = parse_blocks_with_loose_text($file_contents);

// ✅ 정답 트리 파싱
$answer_lines = explode("\n", $txt_contents); // 줄 단위로 나누기



// ✅ 렌더링에 필요한 변수 설정
$answer_index = 0;
$OJ_BLOCK_TREE = $block_tree;
$OJ_SID = $sid;
$OJ_CORRECT_ANSWERS = render_tree_with_answers($answer_lines); // ✅ 정답 트리 저장

// ✅ HTML 출력
include("template/$OJ_TEMPLATE/guideline2.php");
include("template/$OJ_TEMPLATE/footer.php");
?>
