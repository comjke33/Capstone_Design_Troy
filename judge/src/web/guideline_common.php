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
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);

    $blocks = [];
    $stack = [];

    foreach ($matches[0] as $i => $match) {
        $full = $match[0];
        $pos = $match[1];

        if (strpos($full, '_start(') !== false) {
            // start 태그면 스택에 push
            preg_match("/\[(\w+)_start\((\d+)\)\]/", $full, $m);
            $stack[] = [
                'type' => $m[1],
                'index' => intval($m[2]),
                'start' => $pos + strlen($full),  // start 이후부터 본문 시작
                'token_pos' => $pos
            ];
        } elseif (strpos($full, '_end(') !== false) {
            // end 태그면 스택에 있는 start랑 매칭
            preg_match("/\[(\w+)_end\((\d+)\)\]/", $full, $m);
            $type = $m[1];
            $index = intval($m[2]);

            for ($j = count($stack) - 1; $j >= 0; $j--) {
                if ($stack[$j]['type'] === $type && $stack[$j]['index'] === $index) {
                    $start = $stack[$j]['start'];
                    $token_pos = $stack[$j]['token_pos'];
                    $end = $pos;

                    $content = substr($text, $start, $end - $start);

                    // 태그 제거: 각 줄별로 태그가 끼어있을 수 있어서 추가로 정리
                    $content = preg_replace($pattern, '', $content);

                    $blocks[] = [
                        'type' => $type,
                        'index' => $index,
                        'content' => trim($content), // 공백 정리
                        'pos' => $token_pos
                    ];

                    array_splice($stack, $j, 1); // 매칭된 start 제거
                    break;
                }
            }
        }
    }

    // 블록들을 코드상 등장 순서대로 정렬
    usort($blocks, fn($a, $b) => $a['pos'] <=> $b['pos']);

    // 블록 결과 리턴
    return array_map(fn($b) => [
        'type' => $b['type'],
        'index' => $b['index'],
        'content' => $b['content']
    ], $blocks);
}