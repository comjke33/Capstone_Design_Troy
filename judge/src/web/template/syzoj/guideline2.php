<div class="ui container">
    <div class="ui two column stackable grid">

        <!-- ✅ 왼쪽: 코드 작성 -->
        <div class="column ten wide">
            <div class="ui segment">
                <h4 class="ui dividing header">한 문단씩 풀기</h4>

                <!-- Form to input code for each section -->
                <form method="post" class="ui form">
                    <?php
                    // 3.1 함수 블록 출력
                    foreach ($functions[0] as $index => $function) {
                        $func_name = htmlspecialchars($functions[1][$index]);
                        $function_content = nl2br(htmlspecialchars($functions[2][$index]));
                        echo "<div class='field'>";
                        echo "<label><strong>Function: $func_name</strong></label>";
                        echo "<textarea name='code_{$index}_function' rows='6' placeholder='여기에 코드를 작성하세요...'>$function_content</textarea>";
                        echo "</div>";
                    }

                    // 3.2 반복문 블록 출력
                    foreach ($loops[0] as $index => $loop) {
                        $loop_info = htmlspecialchars($loops[1][$index]);
                        $loop_content = nl2br(htmlspecialchars($loops[2][$index]));
                        echo "<div class='field'>";
                        echo "<label><strong>Loop: $loop_info</strong></label>";
                        echo "<textarea name='code_{$index}_loop' rows='6' placeholder='여기에 코드를 작성하세요...'>$loop_content</textarea>";
                        echo "</div>";
                    }

                    // 3.3 조건문 블록 출력
                    foreach ($conditionals[0] as $index => $conditional) {
                        $conditional_info = htmlspecialchars($conditionals[1][$index]);
                        $conditional_content = nl2br(htmlspecialchars($conditionals[2][$index]));
                        echo "<div class='field'>";
                        echo "<label><strong>Conditional: $conditional_info</strong></label>";
                        echo "<textarea name='code_{$index}_conditional' rows='6' placeholder='여기에 코드를 작성하세요...'>$conditional_content</textarea>";
                        echo "</div>";
                    }

                    // 3.4 self-contained 블록 출력
                    foreach ($self_blocks[0] as $index => $self_block) {
                        $self_block_info = htmlspecialchars($self_blocks[1][$index]);
                        $self_block_content = nl2br(htmlspecialchars($self_blocks[2][$index]));
                        echo "<div class='field'>";
                        echo "<label><strong>Self Block: $self_block_info</strong></label>";
                        echo "<textarea name='code_{$index}_self_block' rows='6' placeholder='여기에 코드를 작성하세요...'>$self_block_content</textarea>";
                        echo "</div>";
                    }
                    ?>
                    <button type="submit" class="ui blue button">제출</button>
                </form>
            </div>
        </div>

        <!-- ✅ 오른쪽: 피드백 안내 -->
        <div class="column six wide">
            <div class="ui segment">
                <h4 class="ui dividing header">피드백</h4>
                <div class="ui info message">
                    <p><strong>문단을 정확히 작성하면 체크 표시가 나타납니다.</strong></p>
                    <p>모든 문단이 정답일 경우에만 '완료' 버튼이 활성화됩니다.</p>
                </div>

                <div class="ui segment">
                    <h5 class="ui dividing header">피드백 안내</h5>
                    <p><strong>Function 피드백:</strong> 함수 정의 및 내용이 정확한지 확인합니다.</p>
                    <p><strong>Loop 피드백:</strong> 반복문이 정확하게 작성되었는지 확인합니다.</p>
                    <p><strong>Conditional 피드백:</strong> 조건문이 정확하게 작성되었는지 확인합니다.</p>
                    <p><strong>Self Block 피드백:</strong> 자체적으로 정의된 블록이 올바르게 작성되었는지 확인합니다.</p>
                </div>
            </div>
        </div>

    </div> <!-- End of UI Grid -->
</div> <!-- End of UI Container -->

<!-- 아래는 내부 스타일 시트 -->
<style>
    /* Main container styling */
    .ui.container {
        margin-top: 20px;
    }

    /* Segment styles */
    .ui.segment {
        margin-bottom: 20px;
        padding: 20px;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    /* Styling for the form fields */
    .field label {
        font-weight: bold;
        font-size: 16px;
    }

    textarea {
        width: 100%;
        border: 1px solid #ddd;
        padding: 10px;
        font-family: monospace;
        font-size: 14px;
        border-radius: 4px;
        background-color: #f8f8f8;
        color: #333;
        box-sizing: border-box;
    }

    /* Submit button styling */
    .ui.blue.button {
        margin-top: 15px;
    }

    /* Feedback section styles */
    .ui.info.message {
        background-color: #f0f0f0;
        padding: 15px;
        border-radius: 5px;
    }

    .ui.segment h5 {
        font-weight: bold;
        color: #2c3e50;
        font-size: 18px;
    }

    h4, h5 {
        font-size: 20px;
        margin-bottom: 15px;
        color: #2980b9;
    }

    h3 {
        font-size: 16px;
        margin: 10px 0;
    }

    /* Styling for different code block sections */
    .code-block {
        padding: 15px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        background-color: #fafafa;
        border-radius: 5px;
    }

    .code-block.function {
        background-color: #e0f7fa; /* Light cyan */
    }

    .code-block.loop {
        background-color: #fce4ec; /* Light pink */
    }

    .code-block.conditional {
        background-color: #e8f5e9; /* Light green */
    }

    .code-block.self-block {
        background-color: #fff9c4; /* Light yellow */
    }

    /* Make the columns responsive */
    @media screen and (max-width: 768px) {
        .column.ten.wide, .column.six.wide {
            width: 100% !important;
        }
    }
</style>