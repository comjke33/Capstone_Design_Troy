<?php
function guidelineFilter($text) {
    $lines = preg_split('/\r\n|\r|\n/', $text);  // 윈도우/리눅스 호환 줄바꿈 모두 대응

    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    $textBuffer = ""; // 여러 줄 누적용

    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그: 누적 텍스트 먼저 처리
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct|main_def)_start\((\d+)\)\]/', $line, $m)) {
            if (trim($textBuffer) !== '') { //비어있지 않은 경우
                $stack[count($stack) - 1]['children'][] = [ 
                //stack은 현재 트리구조, count(%stack)-1은 스택 맨 위(현재 작업중)
                //children은 text or block이 담김
                    'type' => 'text', 
                    'content' => rtrim($textBuffer),
                    'depth' => count($stack) - 1 //깊이 지정(들여쓰기 할 떄 사용)
                ];
                $textBuffer = "";
            }

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

        // 종료 태그: 누적 텍스트 먼저 처리 후 pop
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct|main_def)_end\((\d+)\)\]/', $line, $m)) {
            if (trim($textBuffer) !== '') {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($textBuffer),
                    'depth' => count($stack) - 1
                ];
                $textBuffer = "";
            }

            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if ($stack[$i]['type'] === $m[1] && $stack[$i]['index'] == $m[2]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 빈 줄 포함 텍스트 누적
        $textBuffer .= $line . "\n";
    }

    // 마지막 남은 텍스트 처리
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
    $lines = preg_split('/\r\n|\r|\n/', $text);  // 윈도우/리눅스 호환 줄바꿈 모두 대응

    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    $collectingCode = false;  // 코드 누적 여부
    $blockBuffer = "";        // 블록 내용 누적

    foreach ($lines as $line) {
        $line = rtrim($line);

        // `#include` 라인은 무시
        if (preg_match('/^#include\s+<.*>$/', trim($line))) {
            continue;  // 해당 라인은 처리하지 않음
        }

        // `textarea` 부분이 `}` 또는 공백일 경우 무시
        if (trim($line) === '}' || trim($line) === '') {
            continue;  // 해당 라인은 처리하지 않음
        }

        // [start] 태그 감지
        if (preg_match('/\[(\w+)_start\((\d+)\)\]/', $line, $m)) {
            // 기존에 누적된 텍스트가 있으면 처리
            if (!empty($blockBuffer)) {
                // 이전 블록 내용이 있다면, 그것을 하나의 블록으로 처리
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($blockBuffer),
                    'depth' => count($stack) - 1
                ];
                $blockBuffer = "";  // 초기화
            }

            // 새 블록 시작
            $block = [
                'type' => 'block',
                'tag' => $m[1], // 태그 이름
                'index' => (int)$m[2], // 태그 번호
                'depth' => count($stack) - 1,
                'children' => []
            ];
            $stack[count($stack) - 1]['children'][] = &$block;
            $stack[] = &$block;
            unset($block);
            continue;
        }

        // [end] 태그 처리
        if (preg_match('/\[(\w+)_end\((\d+)\)\]/', $line, $m)) {
            // 종료 태그를 만나면, 현재 블록에 누적된 텍스트를 처리하고 pop
            if (!empty($blockBuffer)) {
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($blockBuffer),
                    'depth' => count($stack) - 1
                ];
                $blockBuffer = "";  // 초기화
            }
            array_pop($stack);  // 스택에서 pop
            continue;
        }

        // `if`, `while`, 삼항 연산자, `for`문 등의 괄호 포함된 코드 구문 처리
        if (preg_match('/\b(?:if|for|while)\s*\(.*\)/', $line) || preg_match('/\?.*\:.*;/', $line)) {
            // if/while/for/삼항 연산자 등 괄호가 포함된 라인 처리
            $blockBuffer .= $line . "\n"; // 블록 내용 누적
            continue;
        }

        // 코드 라인 누적
        if (trim($line) !== '') {
            $blockBuffer .= $line . "\n"; // 블록 내용 누적
        }
    }

    // 마지막으로 남은 텍스트가 있으면 처리
    if (!empty($blockBuffer)) {
        $stack[count($stack) - 1]['children'][] = [
            'type' => 'text',
            'content' => rtrim($blockBuffer),
            'depth' => count($stack) - 1
        ];
    }

    // 최종 트리 배열을 평탄화(flatten)해서 반환
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
