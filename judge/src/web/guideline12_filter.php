<?php
function guidelineFilter($text) {
    //텍스트를 줄 단위로 읽기
    $lines = explode("\n", $text);

    //루트 노드와 스택 초기화
    $root = ['children' => [], 'depth' => -1];
    $stack = [ &$root ];

    //우측 공백제거
    foreach ($lines as $line) {
        $line = rtrim($line);

        // 시작 태그일 경우 새 블록 생성 및 스택에 추가
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct|main_def)_start\((\d+)\)\]/', $line, $m)) {
            $block = [
                'type' => $m[1], // 태그 이름
                'index' => $m[2], // 태그 번호
                'depth' => count($stack) - 1,
                'children' => []
            ];
            //스택 맨 위 부모의 children 추가
            $stack[count($stack) - 1]['children'][] = &$block;
            //새 블록 푸시(이 블록 안에 내용 쌓기)
            $stack[] = &$block;
            //메모리 참조 해제
            unset($block);
            continue;
        }

        // 종료 태그만나면 동일한 type, index가진 블록 찾아 pop
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct|main_def)_end\((\d+)\)\]/', $line, $m)) {
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

    //최종 결과를 반환(root는 제외)
    return $root['children'];
}


function codeFilter($text) {
    $lines = explode("\n", $text);
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
        }
    }
    return $results; //평탄화된 tree -> array 배열 변환
}
