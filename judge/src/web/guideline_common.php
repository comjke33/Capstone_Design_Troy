<?php
// 📦 공통 파싱 함수 모음

function parse_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)(?=\[.*_\3\(\d+\)\])/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $matches[0][1];
        $full_len = strlen($matches[0][0]);
        $end_pos = $start_pos + $full_len;

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

        // 추출된 태그와 그 안의 내용 처리
        $tag_type = $matches[1][0];  // 예: func_def
        $tag_index = $matches[3][0]; // 예: 1
        $content = $matches[4][0];   // 태그 사이의 실제 내용

        // 태그 안에 다른 태그가 있을 수 있기 때문에 재귀적으로 처리
        $children = parse_blocks($content);

        $blocks[] = [
            'type' => $tag_type,
            'index' => $tag_index,
            'content' => $content,
            'children' => $children
        ];

        // 다음 파싱을 위해 오프셋 갱신
        $offset = $end_pos;
    }

    // 텍스트의 나머지 부분도 처리
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
    // 태그 패턴
    $tag_pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";

    $blocks = [];
    $stack = [];
    $pos = 0;

    // 텍스트를 순서대로 스캔
    while (preg_match($tag_pattern, $text, $match, PREG_OFFSET_CAPTURE, $pos)) {
        $full_tag = $match[0][0];
        $tag_pos = $match[0][1];
        $type = $match[1][0];
        $direction = $match[2][0];
        $index = intval($match[3][0]);

        $before_text = substr($text, $pos, $tag_pos - $pos); // 태그 앞에 있는 코드

        // 앞에 있는 코드 처리 (공백/줄바꿈 무시하고 유효한 코드만)
        $lines = explode("\n", $before_text);
        foreach ($lines as $line) {
            $trimmed = rtrim($line);
            if ($trimmed !== '') {
                $blocks[] = [
                    'type' => 'text', // 일반 코드
                    'content' => $trimmed
                ];
            }
        }

        if ($direction === 'start') {
            // start 태그는 스택에 쌓아
            $stack[] = [
                'type' => $type,
                'index' => $index
            ];
        } elseif ($direction === 'end') {
            // end 태그는 스택에서 짝 맞는 걸 제거
            for ($i = count($stack) - 1; $i >= 0; $i--) {
                if ($stack[$i]['type'] === $type && $stack[$i]['index'] === $index) {
                    array_splice($stack, $i, 1);
                    break;
                }
            }
        }

        // 다음 검색 위치 갱신
        $pos = $tag_pos + strlen($full_tag);
    }

    // 마지막 남은 텍스트 처리
    $after_text = substr($text, $pos);
    $lines = explode("\n", $after_text);
    foreach ($lines as $line) {
        $trimmed = rtrim($line);
        if ($trimmed !== '') {
            $blocks[] = [
                'type' => 'text',
                'content' => $trimmed
            ];
        }
    }

    return $blocks;
}
