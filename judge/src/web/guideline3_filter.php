<?php
function guidelineFilter($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);

    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];
    $textBuffer = "";

    $insideBlock = false;

    foreach ($lines as $line) {
        $line = rtrim($line);

        // ✅ [blockN] 스타일 시작
        if (preg_match('/^\[block(\d+)\]$/', $line, $m)) {
            if (trim($textBuffer) !== '') {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($textBuffer),
                    'depth' => count($stack) - 1
                ];
                $textBuffer = "";
            }

            $block = [
                'type' => 'block',
                'index' => (int)$m[1],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            $insideBlock = true;
            unset($block);
            continue;
        }

        // ✅ 빈 줄이면 block 종료 + flush buffer
        if ($line === '') {
            if ($insideBlock && trim($textBuffer) !== '') {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($textBuffer),
                    'depth' => count($stack) - 1
                ];
                $textBuffer = "";
            }

            if ($insideBlock && count($stack) > 1 && $stack[count($stack) - 1]['type'] === 'block') {
                array_pop($stack);
                $insideBlock = false;
            }
            continue;
        }

        // ✅ 일반 줄 → block 내부이면 누적
        if ($insideBlock) {
            $textBuffer .= $line . "\n";
            continue;
        }

        // ✅ 블록 외부 일반 텍스트도 누적
        $textBuffer .= $line . "\n";
    }

    // 파일 끝까지 읽은 후 남은 내용 정리
    if (trim($textBuffer) !== '') {
        $stack[count($stack) - 1]['children'][] = [
            'type' => 'text',
            'content' => rtrim($textBuffer),
            'depth' => count($stack) - 1
        ];
    }

    return $root['children'];
}

function codeFilter($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);

    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    $blockBuffer = "";
    $insideBlock = false;

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 무시할 줄
        if (preg_match('/^#include\s+<.*>$/', $line) || trim($line) === '' || trim($line) === '}') {
            continue;
        }

        // [blockN] 태그 (추가 확장)
        if (preg_match('/^\[block(\d+)\]$/', $line, $m)) {
            if (trim($blockBuffer) !== '') {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($blockBuffer),
                    'depth' => count($stack) - 1
                ];
                $blockBuffer = "";
            }

            $block = [
                'type' => 'block',
                'tag' => 'block',
                'index' => (int)$m[1],
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            $insideBlock = true;
            unset($block);
            continue;
        }

        
        // 일반 코드 누적
        $blockBuffer .= $line . "\n";
    }

    // 마지막 남은 buffer 처리
    if (trim($blockBuffer) !== '') {
        $stack[count($stack) - 1]['children'][] = [
            'type' => 'text',
            'content' => rtrim($blockBuffer),
            'depth' => count($stack) - 1
        ];
    }

    return extractContentsFlat($root['children']);
}

// 트리 형태로 저장된 코드 블록 구조를 1차원(flat) 배열로 변환
function extractContentsFlat($blocks) { //트리 구조
    $results = []; //1차원 배열 

    foreach ($blocks as $block) {
        if (isset($block['type']) && $block['type'] === 'text' && isset($block['content'])) {
            //block type='text', content 값 존재시
            $results[] = ['content' => $block['content']]; 
        } elseif (isset($block['children']) && is_array($block['children'])) {
            //block에 children 배열이 있으면, 자식들을 전부 펼쳐서 $results와 재귀 결과를  array_merge()로 합쳐서 정리
            $results = array_merge($results, extractContentsFlat($block['children'])); 
            // 이 코드는 < 기호를 잘못인식하는 문제 O 렌더링에서 처리할 예정
        }
    }
    return $results; //평탄화된 tree -> array 배열 변환
}
