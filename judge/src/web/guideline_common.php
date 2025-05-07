<?php
function guidelineFilter($text) {
    $lines = explode("\n", $text);
    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그일 경우 새 블록 생성 및 스택에 추가
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $line, $m)) {
            $block = [
                'type' => $m[1],
                'index' => $m[2],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            unset($block);
            continue;
        }

        // 종료 태그일 경우 해당 블록을 스택에서 제거
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $m)) {
            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if ($stack[$i]['type'] === $m[1] && $stack[$i]['index'] == $m[2]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 일반 텍스트는 현재 블록에 추가
        if (trim($line) !== '') {
            $stack[count($stack) - 1]['children'][] = [
                'type' => 'text',
                'content' => $line,
                'depth' => count($stack) - 1
            ];
        }
    }

    return $root['children'];
}

function codeFilter($text) {
    $tag_pattern = "/\[(func_def|rep|cond|self|struct|construct)_(start|end)\((\d+)\)\]/";

    $blocks = [];
    $pos = 0;
    $length = strlen($text);
    $depth = -1; // root 이전부터 시작

    while (preg_match($tag_pattern, $text, $match, PREG_OFFSET_CAPTURE, $pos)) {
        $tag = $match[1][0];           // func_def, rep, cond, ...
        $tag_type = $match[2][0];      // start or end
        $index = (int)$match[3][0];
        $full_match = $match[0][0];
        $match_pos = $match[0][1];

        $next_pos = $match_pos + strlen($full_match);

        if ($tag_type === "start") {
            $depth++;

            // 다음 태그까지의 내용을 추출
            if (preg_match($tag_pattern, $text, $next_match, PREG_OFFSET_CAPTURE, $next_pos)) {
                $next_tag_pos = $next_match[0][1];
                $between = substr($text, $next_pos, $next_tag_pos - $next_pos);
            } else {
                $between = substr($text, $next_pos);
            }

            // 내부 태그 제거
            $between = preg_replace($tag_pattern, '', $between);
            $lines = explode("\n", $between);

            $content = "";
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || $trimmed === '}') continue;
                $content .= $line . "\n";
            }

            if (trim($content) !== '') {
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($content),
                    'depth' => $depth,
                    'children' => []
                ];
            }

        } else if ($tag_type === "end") {
            $depth--;
        }

        $pos = $next_pos;
    }

    return $blocks;
}
