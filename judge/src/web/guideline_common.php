<?php

function parse_blocks($text, $depth = 0) {
    $lines = explode("\n", $text);
    $blocks = [];
    $stack = [];

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 블록 시작
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $line, $start_matches)) {
            $stack[] = [
                'type' => $start_matches[1],
                'index' => $start_matches[2],
                'depth' => $depth,
                'content_lines' => []
            ];
            continue;
        }

        // 블록 종료
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $end_matches)) {
            $end_type = $end_matches[1];
            $end_index = $end_matches[2];

            for ($i = count($stack) - 1; $i >= 0; $i--) {
                if ($stack[$i]['type'] === $end_type && $stack[$i]['index'] === $end_index) {
                    $matched = array_splice($stack, $i, 1)[0];
                    $children = [];

                    foreach ($matched['content_lines'] as $cl) {
                        if (is_array($cl)) {
                            $children[] = $cl;
                        } else {
                            $children[] = [
                                'type' => 'text',
                                'content' => $cl,
                                'depth' => $matched['depth'] + 1
                            ];
                        }
                    }

                    $block = [
                        'type' => $matched['type'],
                        'index' => $matched['index'],
                        'depth' => $matched['depth'],
                        'children' => $children
                    ];

                    if (!empty($stack)) {
                        $stack[count($stack) - 1]['content_lines'][] = $block;
                    } else {
                        $blocks[] = $block;
                    }

                    continue 2;
                }
            }

            // 매치되지 않는 end는 무시
            continue;
        }

        // 일반 줄 처리
        if (!empty($stack)) {
            $stack[count($stack) - 1]['content_lines'][] = $line;
        } elseif (trim($line) !== '') {
            // 📌 start/end 사이 독립 줄도 포함
            $blocks[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth
            ];
        }
    }

    // 닫히지 않은 블록 처리
    foreach ($stack as $unmatched) {
        $children = [];
        foreach ($unmatched['content_lines'] as $cl) {
            if (is_array($cl)) {
                $children[] = $cl;
            } else {
                $children[] = [
                    'type' => 'text',
                    'content' => $cl,
                    'depth' => $unmatched['depth'] + 1
                ];
            }
        }

        $blocks[] = [
            'type' => $unmatched['type'],
            'index' => $unmatched['index'],
            'depth' => $unmatched['depth'],
            'children' => $children,
            'unmatched' => true
        ];
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
