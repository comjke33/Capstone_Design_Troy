<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
    <h1>한줄씩 풀기</h1>
    <span>문제 번호: <?= htmlspecialchars($OJ_SID) ?></span>
</div>

<!-- 스타일 불러오기 -->
<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout" style="display: flex; justify-content: space-between; gap: 20px;">

    <!-- 왼쪽 패널: 문제 설명과 텍스트 입력 영역 -->
    <div class="left-panel" style="flex: 0.2; padding-right: 10px; overflow-y: hidden; position: relative; display: flex; justify-content: center; align-items: center;">
        
        <!-- 슬라이더를 추가해서 이미지를 마우스 커서의 Y 위치에 따라 위 아래로 움직이게 하기 -->
        <div id="slider-container" style="position: absolute; height: 100%; width: 100px; overflow-y: hidden;">
            <img src="/image/feedback.jpg" alt="Feedback" id="feedback-img" 
                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;">
        </div>

    </div>

    <!-- 가운데 패널: 문제 설명 및 텍스트 입력 영역 -->
    <div class="center-panel" style="flex-grow: 1; padding: 20px; overflow-y: auto;">
        <?php
            function render_tree_plain($blocks, &$answer_index = 0) {
                $html = "";
            
                foreach ($blocks as $block) {
                    $indent_px = 10 * ($block['depth'] ?? 0);
            
                    if (isset($block['children'])) {
                        $html .= "<div class='block-wrap block-{$block['type']}' style='margin-left: {$indent_px}px;'>";
                        $html .= render_tree_plain($block['children'], $answer_index);
                        $html .= "</div>";
                    } elseif ($block['type'] === 'text') {
                        $raw = trim($block['content']);
            
                        // 태그라인 무시
                        if ($raw === '' || preg_match("/^\[(func_def|rep|cond|self|struct|construct)_(start|end)\(\d+\)\]$/", $raw)) {
                            continue;
                        }
            
                        $line = htmlspecialchars($block['content']);
                        if (strpos($line, '[start]') !== false && strpos($line, '[end]') !== false) {
                            $line = preg_replace('/\[(.*?)\]/', '', $line);  // 태그 제거
                            $line = trim($line);
                        }

                        // 정답 코드가 존재하는지 먼저 확인
                        $has_correct_answer = isset($GLOBALS['OJ_CORRECT_ANSWERS'][$answer_index]);            
                        $disabled = $has_correct_answer ? "" : "disabled";
            
                        // 가이드라인 설명 및 코드 입력 영역
                        $html .= "<div class='submission-line' style='padding-left: {$indent_px}px;'>";
                        $html .= "<div style='flex: 1'>";
                        $html .= "<div class='code-line'>{$line}</div>";
                        $html .= "<textarea id='ta_{$answer_index}' class='styled-textarea' data-index='{$answer_index}' {$disabled}></textarea>";
                        if ($has_correct_answer) {
                            $html .= "<button onclick='submitAnswer({$answer_index})' id='btn_{$answer_index}' class='submit-button'>제출</button>";
                            $html .= "<button onclick='showAnswer({$answer_index})' id='view_btn_{$answer_index}' class='view-button'>답안 확인</button>";
                        }
                        // 정답이 표시될 공간 추가 (textarea와 제출 버튼 사이)
                        $html .= "<div id='answer_area_{$answer_index}' class='answer-area' style='display:none; margin-top: 10px;'></div>";
                        $html .= "</div><div style='width: 50px; text-align: center; margin-top: 20px;'>";
                        $html .= "<span id='check_{$answer_index}' class='checkmark' style='display:none;'>✔️</span>";
                        $html .= "</div></div>";
            
                        $answer_index++;
                    }
                }
            
                return $html;
            }

            // 주어진 코드를 파싱하여 문제와 설명을 출력
            $answer_index = 0;
            echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <!-- 오른쪽 패널: 피드백 부분 -->
    <div class="right-panel" style="flex: 0.3; padding-left: 20px; border-left: 1px solid #ddd; height: 100vh;">
        <h3>피드백 부분</h3>
        <div class="feedback-content" style="padding: 20px; background-color: #f9f9f9; height: calc(100% - 40px);">
            <!-- 피드백 내용이 여기에 표시됩니다. -->
        </div>
    </div>

</div>

<!-- js 불러오기 -->
<script src="/template/syzoj/js/guideline.js"></script>

