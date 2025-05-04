<?php
// 📦 공통 파싱 함수 모음

function parse_blocks($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)(?=\[.*_\3\(\d+\)\])/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $matches[0][1];
        $full_len = strlen($matches[0][0]);
        $end_pos = $start_pos + $full_len;

        // 앞의 텍스트
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                if (trim($line) !== '') {
                    $blocks[] = [
                        'type' => 'text',
                        'content' => rtrim($line),
                        'depth' => $depth  // ✅ 들여쓰기 정보 추가
                    ];
                }
            }
        }

        $tag_type = $matches[1][0];
        $tag_index = $matches[3][0];
        $content = $matches[4][0];

        // ✅ 자식은 depth + 1
        $children = parse_blocks($content, $depth + 1);

        $blocks[] = [
            'type' => $tag_type,
            'index' => $tag_index,
            'content' => $content,
            'children' => $children,
            'depth' => $depth  // ✅ 자기 depth도 기록
        ];

        $offset = $end_pos;
    }

    // 나머지
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            if (trim($line) !== '') {
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line),
                    'depth' => $depth 
                ];
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
                'content_lines' => []
            ];
            continue;
        }

        // 종료 태그 감지
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $end_matches)) {
            $last = array_pop($stack);
            if ($last['type'] === $end_matches[1] && $last['index'] === $end_matches[2]) {
                $child_lines = $last['content_lines'];
                $children = [];

                foreach ($child_lines as $cl) {
                    $trimmed = trim($cl);
                    if ($trimmed === '' || $trimmed === '}') continue;

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

        // 일반 텍스트 처리
        if (!empty($stack)) {
            $stack[count($stack) - 1]['content_lines'][] = $line;
        } elseif (trim($line) !== '' && trim($line) !== '}') {
            $blocks[] = [
                'type' => 'text',
                'content' => $line,
                'depth' => $depth
            ];
        }
    }

    return $blocks;
}