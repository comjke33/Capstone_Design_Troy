<?php
// 📦 공통 파싱 함수 모음

function parse_blocks_v2($text) {
    $lines = explode("\n", $text);
    $stack = [];
    $root = [];
    $current = &$root;
    $depth = 0;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') continue;

        if (preg_match("/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/", $trimmed, $start_match)) {
            $block = [
                'type' => $start_match[1],
                'index' => $start_match[2],
                'content' => '',
                'children' => [],
                'depth' => $depth
            ];
            $depth++;
            $stack[] = [&$current, $depth - 1]; // 현재 작업중인 블록과 그 depth 저장
            $current[] = $block;
            $current = &$current[array_key_last($current)]['children'];
        } elseif (preg_match("/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/", $trimmed)) {
            if (!empty($stack)) {
                list($parent, $prev_depth) = array_pop($stack);
                $current = &$parent;
                $depth = $prev_depth;
            }
        } else {
            $current[] = [
                'type' => 'text',
                'content' => rtrim($line),
                'depth' => $depth
            ];
        }
    }

    return $root;
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
