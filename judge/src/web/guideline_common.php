<?php

function parse_blocks($text, $depth = 0) {
    // ✅ 파싱할 패턴 정의
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)(?=\[.*_\3\(\d+\)\])/s";
    
    $blocks = [];  // 결과를 담을 배열
    $offset = 0;   // 현재 읽기 위치

    while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        // ✅ 현재 매치된 태그의 위치와 길이
        $start_pos = $matches[0][1];
        $full_len = strlen($matches[0][0]);
        $end_pos = $start_pos + $full_len;

        // ✅ 현재 매치된 태그 앞의 일반 텍스트 처리
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                if (trim($line) !== '') {
                    $blocks[] = [
                        'type' => 'text',          // 일반 텍스트로 분류
                        'content' => rtrim($line), // 줄 끝 공백 제거
                        'depth' => $depth          // 현재 깊이 기록
                    ];
                }
            }
        }

        // ✅ 태그 정보 추출
        $tag_type = $matches[1][0]; // func_def, rep, cond, ...
        $tag_index = $matches[3][0]; // (1), (2), 같은 숫자
        $content = $matches[4][0];   // 태그 안의 내용

        // ✅ 재귀적으로 안쪽 블록 파싱 (들여쓰기 깊이 +1)
        $children = parse_blocks($content, $depth + 1);

        $blocks[] = [
            'type' => $tag_type,     // 블록 종류
            'index' => $tag_index,   // 태그 번호
            'content' => $content,   // 내용
            'children' => $children, // 안쪽 블록들
            'depth' => $depth        // 현재 깊이
        ];

        $offset = $end_pos; // 다음 위치로 이동
    }

    // ✅ 남은 텍스트 (마지막 부분) 처리
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

    return $blocks; // 최종 블록 리스트 반환
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
