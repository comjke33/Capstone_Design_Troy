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

    $collectingSelf = false;
    $selfBuffer = "";
    $selfTagIndex = null;

    foreach ($lines as $line) {
        $line = rtrim($line);

        // self 블록 시작
        if (preg_match('/\[self_start\((\d+)\)\]/', $line, $m)) {
            $collectingSelf = true;
            $selfBuffer = "";
            $selfTagIndex = $m[1];
            continue;
        }

        // self 블록 종료
        if (preg_match('/\[self_end\((\d+)\)\]/', $line, $m)) {
            if ($collectingSelf && $m[1] == $selfTagIndex) {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($selfBuffer),
                    'depth' => count($stack) - 1
                ];
            }
            $collectingSelf = false;
            $selfBuffer = "";
            $selfTagIndex = null;
            continue;
        }

        // self 블록 내용 수집
        if ($collectingSelf) {
            $selfBuffer .= $line . "\n";
            continue;
        }

        // 일반 블록 시작
        if (preg_match('/\[(func_def|rep|cond|struct|construct)_start\((\d+)\)\]/', $line, $m)) {
            $block = [
                'type' => 'block',
                'tag' => $m[1],
                'index' => (int)$m[2],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            unset($block);
            continue;
        }

        // 일반 블록 종료
        if (preg_match('/\[(func_def|rep|cond|struct|construct)_end\((\d+)\)\]/', $line, $m)) {
            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if ($stack[$i]['tag'] === $m[1] && $stack[$i]['index'] == $m[2]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 일반 코드 라인 (필터링 포함)
        if (
            trim($line) !== '' &&
            trim($line) !== '}' &&
            !preg_match('/^#include\s+<.*>$/', trim($line))
        ) {
            $stack[count($stack) - 1]['children'][] = [
                'type' => 'text',
                'content' => $line,
                'depth' => count($stack) - 1
            ];
        }
    }

    // 최종적으로 content만 추출
    return extractContentsFlat($root['children']);
}
