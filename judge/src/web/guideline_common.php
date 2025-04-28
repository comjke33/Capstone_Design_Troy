<?php
// 📦 공통 파싱 함수 모음

function parse_blocks_with_loose_text($text, $depth = 0) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\3\)\]/s";
    $blocks = [];
    $offset = 0;

    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];
        $full_len = strlen($m[0][0]);
        $end_pos = $start_pos + $full_len;
        
        // Extract any content before the tag
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
                // Add normal text lines without tags
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line),
                    'depth' => $depth + $indent_level
                ];
            }
        }

        // Extract the block content (between start and end)
        $type = $m[1][0];
        $idx = $m[3][0];
        $content = $m[4][0];

        // Recurse into any nested blocks (if any)
        $children = parse_blocks_with_loose_text($content, $depth + 1);
        
        // Only add the start and end tags for blocks that need them
        array_unshift($children, ['type' => 'text', 'content' => "[{$type}_start({$idx})]", 'depth' => $depth + 1]);
        array_push($children, ['type' => 'text', 'content' => "[{$type}_end({$idx})]", 'depth' => $depth + 1]);

        // Add the block and its children
        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => $children
        ];

        // Move offset past the current match
        $offset = $end_pos;
    }

    // Process any remaining text after the last match
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
            // Add normal text lines without tags
            $blocks[] = [
                'type' => 'text',
                'content' => rtrim($line),
                'depth' => $depth + $indent_level
            ];
        }
    }

    return $blocks;
}


function extract_tagged_blocks($text) {
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\d+)\)\]|\[(func_def|rep|cond|self|struct|construct)_end\\((\d+)\)\]/";
    preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
    $stack = [];
    $blocks = [];
    foreach ($matches[0] as $i => $match) {
        $full = $match[0];
        $pos = $match[1];
        if (strpos($full, '_start(') !== false) {
            preg_match("/\[(\w+)_start\((\d+)\)\]/", $full, $m);
            $stack[] = [
                'type' => $m[1],
                'index' => intval($m[2]),
                'start' => $pos + strlen($full),
                'token_pos' => $pos
            ];
        } elseif (strpos($full, '_end(') !== false) {
            preg_match("/\[(\w+)_end\((\d+)\)\]/", $full, $m);
            $type = $m[1];
            $index = intval($m[2]);
            for ($j = count($stack) - 1; $j >= 0; $j--) {
                if ($stack[$j]['type'] === $type && $stack[$j]['index'] === $index) {
                    $start = $stack[$j]['start'];
                    $token_pos = $stack[$j]['token_pos'];
                    $end = $pos;
                    $content = substr($text, $start, $end - $start);
                    $blocks[] = [
                        'type' => $type,
                        'index' => $index,
                        'content' => trim($content),
                        'pos' => $token_pos
                    ];
                    array_splice($stack, $j, 1);
                    break;
                }
            }
        }
    }
    usort($blocks, fn($a, $b) => $a['pos'] <=> $b['pos']);
    return array_map(fn($b) => [
        'type' => $b['type'],
        'index' => $b['index'],
        'content' => $b['content']
    ], $blocks);
}
?>
