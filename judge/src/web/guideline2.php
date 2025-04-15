<?php
// ✅ 헤더 파일 포함 (공통 레이아웃 구성 등)
include("template/syzoj/header.php");

// ✅ 데이터베이스 연결 설정 포함
include("include/db_info.inc.php");

// ✅ 입력 파일 경로 (문제 설명 및 정답 코드 구조 포함된 파일)
$file_path = "/home/Capstone_Design_Troy/test/test1.txt";
$file_contents = file_get_contents($file_path); // 텍스트 파일 내용을 문자열로 불러옴

// ✅ 정답 배열 정의 — index별 정답을 나열해야 함 (텍스트 순서에 맞춰 대응)
// 🟩 [답안 부분]
// JSON에서 코드 정답 불러오기 (헤더 줄, 빈 줄 제외)
$json_path = "/home/Capstone_Design_Troy/test/question_and_code_test1.json";
$json_contents = file_get_contents($json_path);
$json_data = json_decode($json_contents, true);

$answer_code_raw = $json_data[0]['code'];



// 줄 단위로 나눈 후, 헤더와 빈 줄을 제외하고 정답 배열 생성
$answer_lines = explode("\n", $answer_code_raw);
$correct_answers = [];

foreach ($answer_lines as $line) {
    $trimmed = trim($line);
    if (
        $trimmed !== "" &&                // 빈 줄 제외
        strpos($trimmed, "#include") !== 0 // 헤더 줄 제외
    ) {
        $correct_answers[] = $trimmed;   // 정답 배열에 추가
    }
}

// ✅ 주어진 텍스트를 계층적 코드 블록으로 파싱하는 함수 정의
// 🟧 [문제 구조 파싱 부분]
function parse_blocks_with_loose_text($text, $depth = 0) {
    // 🔍 블록 태그 (예: [cond_start(0)] ~ [cond_end(0)]) 탐지용 정규식
    $pattern = "/\[(func_def|rep|cond|self|struct|construct)_start\\((\\d+)\\)\](.*?)\[(func_def|rep|cond|self|struct|construct)_end\\(\\2\\)\]/s";
    $blocks = [];   // 전체 블록 배열
    $offset = 0;    // 현재 파싱 시작 위치

    // 🔄 텍스트에 블록이 존재할 때마다 반복
    while (preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE, $offset)) {
        $start_pos = $m[0][1];           // 블록 시작 위치
        $full_len = strlen($m[0][0]);    // 블록 전체 길이
        $end_pos = $start_pos + $full_len;

        // 📤 블록 앞의 일반 텍스트(문제 설명 등) 추출
        $before_text = substr($text, $offset, $start_pos - $offset);
        if (trim($before_text) !== '') {
            foreach (explode("\n", $before_text) as $line) {
                $indent_level = (strlen($line) - strlen(ltrim($line))) / 4; // 들여쓰기 계산
                $blocks[] = [
                    'type' => 'text',
                    'content' => rtrim($line),
                    'depth' => $depth + $indent_level
                ];
            }
        }

        // 🧱 블록 타입 및 내용 추출
        $type = $m[1][0];      // 블록 종류 (cond, rep 등)
        $idx = $m[2][0];       // 블록 인덱스 (0, 1 등)
        $content = $m[3][0];   // 블록 안의 내용

        // 블록 시작 및 종료 태그 생성
        $start_tag = "[{$type}_start({$idx})]";
        $end_tag = "[{$type}_end({$idx})]";

        // ⏬ 블록 내부 재귀 파싱
        $children = parse_blocks_with_loose_text($content, $depth + 1);

        // 시작/끝 태그를 children 앞뒤로 삽입
        array_unshift($children, [
            'type' => 'text',
            'content' => $start_tag,
            'depth' => $depth + 1
        ]);
        array_push($children, [
            'type' => 'text',
            'content' => $end_tag,
            'depth' => $depth + 1
        ]);

        // 최종 블록 저장
        $blocks[] = [
            'type' => $type,
            'index' => $idx,
            'depth' => $depth,
            'children' => $children
        ];

        // 다음 검색 시작 위치 업데이트
        $offset = $end_pos;
    }

    // 🔚 마지막 남은 일반 텍스트 처리 (맨 마지막 블록 이후)
    $tail = substr($text, $offset);
    if (trim($tail) !== '') {
        foreach (explode("\n", $tail) as $line) {
            $indent_level = (strlen($line) - strlen(ltrim($line))) / 4;
            $blocks[] = [
                'type' => 'text',
                'content' => rtrim($line),
                'depth' => $depth + $indent_level
            ];
        }
    }

    // 🧩 계층적 블록 배열 반환
    return $blocks;
}

// ✅ URL 파라미터로부터 problem_id 획득 (없으면 공백 처리)
$sid = isset($_GET['problem_id']) ? urlencode($_GET['problem_id']) : '';

// ✅ 문제 파일 파싱 결과 저장
$block_tree = parse_blocks_with_loose_text($file_contents);

// ✅ 출력에 사용할 변수들 설정 (템플릿에 전달)
// 🟥 [문제 렌더링 + 답안 입력 영역 구성 준비]
$answer_index = 0;
$OJ_BLOCK_TREE = $block_tree;              // 전체 트리 구조
$OJ_SID = $sid;                            // 문제 ID
$OJ_CORRECT_ANSWERS = $correct_answers;    // 정답 리스트

// ✅ 실제 HTML 렌더링 수행 (템플릿 파일 호출)
include("template/$OJ_TEMPLATE/guideline2.php");

// ✅ 페이지 하단 푸터 포함
include("template/$OJ_TEMPLATE/footer.php");
?>
