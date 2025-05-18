<?php
function guidelineFilter($text) {
    $lines = explode("\n", $text);
    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그 [block(1)]
        if (preg_match('/\[block\((\d+)\)\]/', $line, $m)) {
            $block = [
                'type' => 'block',
                'index' => $m[1],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            unset($block);
            continue;
        }

        // 종료 태그 [/block(1)]
        if (preg_match('/\[\/block\((\d+)\)\]/', $line, $m)) {
            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if ($stack[$i]['type'] === 'block' && $stack[$i]['index'] == $m[1]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 일반 텍스트
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
    $blockBuffer = "";

    foreach ($lines as $line) {
        $line = rtrim($line);

        if (preg_match('/^#include\s+<.*>$/', trim($line))) continue;
        if (trim($line) === '}' || trim($line) === '') continue;

        // [block(1)]
        if (preg_match('/\[block\((\d+)\)\]/', $line, $m)) {
            if (!empty($blockBuffer)) {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($blockBuffer),
                    'depth' => count($stack) - 1
                ];
                $blockBuffer = "";
            }

            $block = [
                'type' => 'block',
                'index' => (int)$m[1],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            unset($block);
            continue;
        }

        // [/block(1)]
        if (preg_match('/\[\/block\((\d+)\)\]/', $line, $m)) {
            if (!empty($blockBuffer)) {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($blockBuffer),
                    'depth' => count($stack) - 1
                ];
                $blockBuffer = "";
            }
            array_pop($stack);
            continue;
        }

        if (trim($line) !== '') {
            $blockBuffer .= $line . "\n";
        }
    }

    if (!empty($blockBuffer)) {
        $stack[count($stack) - 1]['children'][] = [
            'type' => 'text',
            'content' => rtrim($blockBuffer),
            'depth' => count($stack) - 1
        ];
    }

    return extractContentsFlat($root['children']);
}


function extractContentsFlat($blocks) {
    $results = [];
    foreach ($blocks as $block) {
        if (isset($block['type']) && $block['type'] === 'text' && isset($block['content'])) {
            $results[] = ['content' => $block['content']];
        } elseif (isset($block['children']) && is_array($block['children'])) {
            $results = array_merge($results, extractContentsFlat($block['children']));
        }
    }
    return $results;
}
