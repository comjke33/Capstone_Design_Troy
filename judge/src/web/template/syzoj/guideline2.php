<div class="ui container">
    <div class="ui two column stackable grid">

        <!-- ✅ 왼쪽: 코드 작성 -->
        <div class="column ten wide">
            <div class="ui segment">
                <h4 class="ui dividing header">한 문단씩 풀기</h4>

                <!-- Form to input code for each section -->
                <form method="post" class="ui form">

                    <!-- Function Block -->
                    <div class="ui segment">
                        <h4 class="ui dividing header">Function Block</h4>
                        <?php
                        foreach ($functions[0] as $index => $function) {
                            $func_name = htmlspecialchars($functions[1][$index]);
                            $function_content = nl2br(htmlspecialchars($functions[2][$index]));
                            echo "<div class='field'>";
                            echo "<label><strong>Function: $func_name</strong></label>";
                            echo "<textarea name='code_{$index}_function' rows='6' placeholder='여기에 코드를 작성하세요...'>$function_content</textarea>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Loop Block -->
                    <div class="ui segment">
                        <h4 class="ui dividing header">Loop Block</h4>
                        <?php
                        foreach ($loops[0] as $index => $loop) {
                            $loop_info = htmlspecialchars($loops[1][$index]);
                            $loop_content = nl2br(htmlspecialchars($loops[2][$index]));
                            echo "<div class='field'>";
                            echo "<label><strong>Loop: $loop_info</strong></label>";
                            echo "<textarea name='code_{$index}_loop' rows='6' placeholder='여기에 코드를 작성하세요...'>$loop_content</textarea>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Conditional Block -->
                    <div class="ui segment">
                        <h4 class="ui dividing header">Conditional Block</h4>
                        <?php
                        foreach ($conditionals[0] as $index => $conditional) {
                            $conditional_info = htmlspecialchars($conditionals[1][$index]);
                            $conditional_content = nl2br(htmlspecialchars($conditionals[2][$index]));
                            echo "<div class='field'>";
                            echo "<label><strong>Conditional: $conditional_info</strong></label>";
                            echo "<textarea name='code_{$index}_conditional' rows='6' placeholder='여기에 코드를 작성하세요...'>$conditional_content</textarea>";
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <!-- Self-Contained Block -->
                    <div class="ui segment">
                        <h4 class="ui dividing header">Self Block</h4>
                        <?php
                        foreach ($self_blocks[0] as $index => $self_block) {
                            $self_block_info = htmlspecialchars($self_blocks[1][$index]);
                            $self_block_content = nl2br(htmlspecialchars($self_blocks[2][$index]));
                            echo "<div class='field'>";
                            echo "<label><strong>Self Block: $self_block_info</strong></label>";
                            echo "<textarea name='code_{$index}_self_block' rows='6' placeholder='여기에 코드를 작성하세요...'>$self_block_content</textarea>";
                            echo "</div>";
                        }
                        ?>
                    </div>

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

                <!-- Feedback for Function Block -->
                <div class="ui segment">
                    <h5 class="ui dividing header">Function Feedback</h5>
                    <p>함수 정의 및 내용이 정확한지 확인합니다.</p>
                </div>

                <!-- Feedback for Loop Block -->
                <div class="ui segment">
                    <h5 class="ui dividing header">Loop Feedback</h5>
                    <p>반복문이 정확하게 작성되었는지 확인합니다.</p>
                </div>

                <!-- Feedback for Conditional Block -->
                <div class="ui segment">
                    <h5 class="ui dividing header">Conditional Feedback</h5>
                    <p>조건문이 정확하게 작성되었는지 확인합니다.</p>
                </div>

                <!-- Feedback for Self Block -->
                <div class="ui segment">
                    <h5 class="ui dividing header">Self Block Feedback</h5>
                    <p>자체적으로 정의된 블록이 올바르게 작성되었는지 확인합니다.</p>
                </div>
            </div>
        </div>

    </div> <!-- End of UI Grid -->
</div> <!-- End of UI Container -->
