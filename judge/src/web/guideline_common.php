<?php

function parse_blocks($text, $depth = 0) {
    $lines = explode("\n", $text);
    $blocks = [];
    $stack = [];

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그 감지
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $line, $start_matches)) {
            $stack[] = [
                'type' => $start_matches[1],
                'index' => $start_matches[2],
                'depth' => $depth,
                'buffer' => []
            ];
            continue;
        }

        // 종료 태그 감지
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $end_matches)) {
            $end_type = $end_matches[1];
            $end_index = $end_matches[2];

            for ($i = count($stack) - 1; $i >= 0; $i--) {
                if ($stack[$i]['type'] === $end_type && $stack[$i]['index'] === $end_index) {
                    $matched = array_splice($stack, $i, 1)[0];
                    $buffer_text = implode("\n", $matched['buffer']);
                    $children = parse_blocks($buffer_text, $matched['depth'] + 1);

                    $block = [
                        'type' => $matched['type'],
                        'index' => $matched['index'],
                        'depth' => $matched['depth'],
                        'children' => $children
                    ];

                    if (!empty($stack)) {
                        $stack[count($stack) - 1]['buffer'][] = "__BLOCK__" . json_encode($block);
                    } else {
                        $blocks[] = $block;
                    }

                    continue 2;
                }
            }

            // 매치되지 않는 종료 태그는 무시
            continue;
        }

        // 일반 텍스트 처리
        if (!empty($stack)) {
            $stack[count($stack) - 1]['buffer'][] = $line;
        } elseif (trim($line) !== '') {
            $blocks[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth
            ];
        }
    }

    // 닫히지 않은 블록 처리
    foreach ($stack as $unmatched) {
        $buffer_text = implode("\n", $unmatched['buffer']);
        $children = parse_blocks($buffer_text, $unmatched['depth'] + 1);

        $blocks[] = [
            'type' => $unmatched['type'],
            'index' => $unmatched['index'],
            'depth' => $unmatched['depth'],
            'children' => $children,
            'unmatched' => true
        ];
    }

    // __BLOCK__ 문자열을 복원
    foreach ($blocks as &$block) {
        if (isset($block['children'])) {
            foreach ($block['children'] as &$child) {
                if (is_string($child) && strpos($child, "__BLOCK__") === 0) {
                    $child = json_decode(substr($child, 9), true);
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
