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
    $lines = explode("\n", $text);
    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그 처리
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

        // 종료 태그 처리
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $m)) {
            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if ($stack[$i]['type'] === $m[1] && $stack[$i]['index'] == $m[2]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 일반 코드 줄 추가
        if (trim($line) !== '') {
            $stack[count($stack) - 1]['children'][] = [
                'type' => 'text',
                'content' => $line,
                'depth' => count($stack) - 1
            ];
        }
    }

    // 내부에서 재귀적으로 태그 없는 코드 추출
    $extract_code = function($blocks) use (&$extract_code) {
        $code = '';
        foreach ($blocks as $block) {
            if ($block['type'] === 'text') {
                $code .= $block['content'] . "\n";
            } elseif (!empty($block['children'])) {
                $code .= $extract_code($block['children']);
            }
        }
        return $code;
    };

    // 최종 코드 출력
    return $extract_code($root['children']);
}
