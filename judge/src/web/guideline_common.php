<?php
// 📦 공통 파싱 함수 모음

function parse_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)(?=\[.*_\3\(\d+\)\])/s"; // 태그 패턴
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $matches[0][1]; // 태그 시작 위치
        $full_len = strlen($matches[0][0]); // 태그의 전체 길이
        $end_pos = $start_pos + $full_len; // 태그의 끝 위치

        // 태그 앞에 있는 텍스트를 추출
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            // 불필요한 텍스트도 처리 (예: 다른 내용)
            foreach (explode("\n", $before_text) as $line) {
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line)
                ];
            }
        }

        // 태그 및 내용 처리
        $tag_type = $matches[1][0];  // 예: func_def
        $tag_index = $matches[3][0]; // 예: 1
        $content = $matches[4][0];   // 태그 사이의 내용

        // 태그 내 코드가 여러 줄일 경우에도 하나의 블록으로 처리
        $block_content = $content;
        $block_content = preg_replace('/\[(.*?)\]/', '', $block_content);  // 태그 제거

        // 코드 내용이 비어 있거나 } 하나만 있는 경우 건너뛰기
        $lines = explode("\n", $block_content);
        $final_content = "";
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || $trimmed === '}') {
                continue;  // 빈 줄이나 }만 있는 경우 건너뜀
            }
            $final_content .= $trimmed . "\n";  // 코드 내용 추가
        }

        // 코드 내용이 비어 있지 않으면 블록에 추가
        if (!empty($final_content)) {
            $blocks[] = [
                'type' => 'text',
                'content' => $final_content
            ];
        }

        // 다음 파싱을 위한 오프셋 갱신
        $offset = $end_pos;
    }

    // 남은 텍스트 처리
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $blocks[] = [
                'type' => 'text',
                'content' => rtrim($line)
            ];
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
