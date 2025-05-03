<?php
// 📦 공통 파싱 함수 모음

function parse_blocks($text, $depth = 0) {
    $lines = explode("\n", $text);
    $blocks = [];
    $stack = [];
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') continue;

        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $trimmed, $start_match)) {
            // 새 블록 시작
            $stack[] = [
                'type' => $start_match[1],
                'index' => $start_match[2],
                'depth' => $depth,
                'children' => [],
                'open_tag' => $trimmed
            ];
        } elseif (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $trimmed, $end_match)) {
            // 블록 종료
            $completed = array_pop($stack);
            if (!empty($stack)) {
                // 부모가 있으면 부모의 children에 추가
                $stack[count($stack) - 1]['children'][] = $completed;
            } else {
                $blocks[] = $completed;
            }
        } else {
            // 일반 텍스트
            $text_block = [
                'type' => 'text',
                'content' => $trimmed,
                'depth' => $depth + count($stack)
            ];
            if (!empty($stack)) {
                $stack[count($stack) - 1]['children'][] = $text_block;
            } else {
                $blocks[] = $text_block;
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
