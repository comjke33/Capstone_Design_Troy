// 2. test.txt 파일 읽기
$file_path = "/home/troy0012/test/test.txt";  // test.txt 파일 경로

// 파일이 존재하는지 확인
if (!file_exists($file_path)) {
    echo "❌ test.txt 파일을 찾을 수 없습니다.";
    exit;
}

// 파일 내용을 읽어오기
$feedback_code = file_get_contents($file_path);
if ($feedback_code === false) {
    echo "❌ test.txt 파일을 읽을 수 없습니다. 오류: " . error_get_last()['message'];
    exit;
}

// 파일을 읽을 수 있으면 출력
echo "파일 내용: $feedback_code";
