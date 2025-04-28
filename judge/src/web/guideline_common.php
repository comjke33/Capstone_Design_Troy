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

function extract_content_lines($text) {
    $lines = explode("\n", $text);  // 한 줄씩 나눈다
    $in_block = false;              // 현재 태그 안에 있는지 표시
    $result = [];

    foreach ($lines as $line) {
        $line = trim($line); // 앞뒤 공백 제거

        // 시작 태그를 만나면 블록 안으로
        if (preg_match("/\[(func_def|rep|cond|self|struct|construct)_start\(\d+\)\]/", $line)) {
            $in_block = true;
            continue; // 태그 줄은 건너뛴다
        }

        // 끝 태그를 만나면 블록 밖으로
        if (preg_match("/\[(func_def|rep|cond|self|struct|construct)_end\(\d+\)\]/", $line)) {
            $in_block = false;
            continue; // 태그 줄은 건너뛴다
        }

        // 블록 안에 있고 내용이 있으면 저장
        if ($in_block && $line !== '') {
            $result[] = $line;
        }
    }

    return $result;
}
