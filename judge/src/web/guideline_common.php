<?php

function parse_blocks($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)\n?/s";
    $blocks = [];
    $stack = [];

    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $line = rtrim($line);
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)/', $line, $start_matches)) {
            $stack[] = [
                'type' => $start_matches[1],
                'index' => $start_matches[2],
                'depth' => $depth,
                'content_lines' => []
            ];
            continue;
        }

        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)/', $line, $end_matches)) {
            $last = array_pop($stack);
            if ($last['type'] === $end_matches[1] && $last['index'] === $end_matches[2]) {
                $child_lines = $last['content_lines'];
                $children = [];
                foreach ($child_lines as $cl) {
                    if (strpos($cl, "__BLOCK__") === 0) {
                        $children[] = json_decode(substr($cl, 9), true);
                    } else {
                        $children[] = [
                            'type' => 'text',
                            'content' => $cl,
                            'depth' => $depth + 1
                        ];
                    }
                }
                $block = [
                    'type' => $last['type'],
                    'index' => $last['index'],
                    'depth' => $last['depth'],
                    'children' => $children
                ];
                if (!empty($stack)) {
                    $stack[count($stack) - 1]['content_lines'][] = "__BLOCK__" . json_encode($block);
                } else {
                    $blocks[] = $block;
                }
            }
            continue;
        }

        if (!empty($stack)) {
            $stack[count($stack) - 1]['content_lines'][] = $line;
        } elseif (trim($line) !== '') {
            $blocks[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth
            ];
        }
    }

    foreach ($blocks as &$block) {
        if (isset($block['children'])) {
            foreach ($block['children'] as &$child) {
                if (is_string($child) && strpos($child, '__BLOCK__') === 0) {
                    $child = json_decode(substr($child, 9), true);
                }
            }
        }
    }

    return $blocks;
}y

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
