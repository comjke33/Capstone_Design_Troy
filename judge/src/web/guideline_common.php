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
        if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $line, $m)) {
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
            메모리 참조 해제
            unset($block);
            continue;
        }

        // 종료 태그만나면 동일한 type, index가진 블록 찾아 pop
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

    //최종 결과를 반환(root는 제외)
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

        //self 블록만 별도로 처리하는 이뉴는
        //사용자 입력 유도 혹은 하나의 코드조각을 하나의 줄로 인식하기 위해 사용하기 때문에
        
        //self_start감지
        if (preg_match('/\[self_start\((\d+)\)\]/', $line, $m)) {
            $collectingSelf = true;
            $selfBuffer = "";
            $selfTagIndex = $m[1];
            continue;
        }

        //self_end 만나면 
        if (preg_match('/\[self_end\((\d+)\)\]/', $line, $m)) {
            //self 블록 수집 중인지 확인
            //같은 블록의 태그가 서로 같은 블록인지(ex. [self_start] 코드 [self_end])
            if ($collectingSelf && $m[1] == $selfTagIndex) {
                //지금까지 저장된 $selfBuffer내용 children 배열의 추가
                $stack[count($stack) - 1]['children'][] = [
                    'type' => 'text',
                    'content' => rtrim($selfBuffer),
                    'depth' => count($stack) - 1
                ];
            }
            //self 초기화
            $collectingSelf = false;
            $selfBuffer = "";
            $selfTagIndex = null;
            // 다시 순회
            continue;
        }

        // 🔹 self 내부 내용 누적
        if ($collectingSelf) {
            $selfBuffer .= $line . "\n";
            continue;
        }

        // 이외의 일반구조 블록 시작
        if (preg_match('/\[(func_def|rep|cond|struct|construct)_start\((\d+)\)\]/', $line, $m)) {
            //현재 스택의 top에 children 추가
            //스택 push, unset(참조해제), 다음줄 이동
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

        //일반구조 종료 블록 만나면
        if (preg_match('/\[(func_def|rep|cond|struct|construct)_end\((\d+)\)\]/', $line, $m)) {
            //뒤에서 부터 앞으로 순회(가장 최근에 열려있는 블록부터 닫기)
            for ($i = count($stack) - 1; $i >= 1; $i--) {
                if (isset($stack[$i]['tag']) && $stack[$i]['tag'] === $m[1] && $stack[$i]['index'] == $m[2]) {
                    array_pop($stack);
                    break;
                }
            }
            continue;
        }

        // 일반 코드 줄 처리 (예외 필터링)
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

    // tree 배열 --> flat 배열로 변환
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
