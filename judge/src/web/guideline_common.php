

<?php

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return substr($haystack, 0, strlen($needle)) === $needle;
    }
}     

function parse_blocks($text, $depth = 0) {
    $lines = explode("\n", $text);
    $stack = [];
    $blocks = [];

    foreach ($lines as $line) {
        $line = rtrim($line);
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_(start)\((\d+)\)\]/', $line, $start_matches)) {
            // 시작 태그면 스택에 push
            $stack[] = [
                'type' => $start_matches[1],
                'index' => $start_matches[3],
                'depth' => $depth,
                'start_line' => $line,
                'content_lines' => []
            ];
        } elseif (preg_match('/\[(func_def|rep|cond|self|struct|construct)_(end)\((\d+)\)\]/', $line, $end_matches)) {
            // 종료 태그면 스택 pop 및 자식 파싱
            $last = array_pop($stack);
            if ($last['type'] === $end_matches[1] && $last['index'] === $end_matches[3]) {
                $children = parse_blocks(implode("\n", $last['content_lines']), $depth + 1);
                $blocks[] = [
                    'type' => $last['type'],
                    'index' => $last['index'],
                    'depth' => $last['depth'],
                    'start_tag' => $last['start_line'],
                    'end_tag' => $line,
                    'children' => $children
                ];
            } else {
                throw new Exception("Unmatched tag: " . $line);
            }
        } else {
            // 텍스트는 가장 최근 시작 태그에 쌓기
            if (!empty($stack)) {
                $stack[count($stack) - 1]['content_lines'][] = $line;
            } else {
                if (trim($line) !== '') {
                    $blocks[] = [
                        'type' => 'text',
                        'content' => $line,
                        'depth' => $depth
                    ];
                }
            }
        }
    }

    return $blocks;
}


function extract_tagged_blocks($text) {
    $tag_pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";

    $blocks = [];
    $pos = 0;
    $length = strlen($text);

    while (preg_match($tag_pattern, $text, $match, PREG_OFFSET_CAPTURE, $pos)) {
        $current_tag = $match[0][0];
        $current_pos = $match[0][1];

        // 현재 태그 이후부터 다음 태그까지 읽는다
        $next_pos = $current_pos + strlen($current_tag);
        if (preg_match($tag_pattern, $text, $next_match, PREG_OFFSET_CAPTURE, $next_pos)) {
            $next_tag_pos = $next_match[0][1];
            $between_text = substr($text, $next_pos, $next_tag_pos - $next_pos);
        } else {
            // 마지막 태그이면 끝까지
            $between_text = substr($text, $next_pos);
        }

        // 태그 내 내용만 추출하고, 태그는 제거
        $between_text = preg_replace($tag_pattern, '', $between_text);
        $lines = explode("\n", $between_text);

        // 코드가 비어 있거나 } 하나만 있는 경우 건너뛰기
        $block_content = "";
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || $trimmed === '}') {
                continue;  // 빈 줄이나 }만 있는 경우 건너뜀
            }
            $block_content .= $trimmed . "\n";  // 코드 내용 추가
        }

        // 코드 내용이 비어 있지 않으면 블록에 추가
        if (!empty($block_content)) {
            $blocks[] = [
                'type' => 'text',
                'content' => $block_content
            ];
        }

        // 다음 검색 위치 갱신
        $pos = $next_pos;
    }

    return $blocks;
}
