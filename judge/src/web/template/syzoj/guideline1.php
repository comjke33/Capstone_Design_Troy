<?php include("template/$OJ_TEMPLATE/header.php");?>
<?php include("../../guideline_common.php");?>

<div class='problem-id' style='font-weight:bold; font-size:20px; margin-bottom: 24px;'>
</div>

<link rel="stylesheet" href="/template/syzoj/css/guideline.css">

<div class="main-layout">
    <!-- 좌측 패널 -->
    <div class="left-panel">
        <div id="flowchart-images"></div>
    </div>

    <!-- 가운데 패널 -->
    <div class="center-panel">
        <h1>한줄씩 풀기</h1>
        <span>문제 번호: <?= htmlspecialchars($problem_id) ?></span>
        <?php
        function parse_blocks_v2($text, $depth = 0) {
            $lines = explode("\n", $text);
            $blocks = [];
            $stack = [];
        
            foreach ($lines as $line) {
                $line = rtrim($line);
        
                // [xxx_start(n)] 감지
                if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_start\((\d+)\)\]/', $line, $start_matches)) {
                    $stack[] = [
                        'type' => $start_matches[1],
                        'index' => $start_matches[2],
                        'depth' => $depth,
                        'content_lines' => []
                    ];
                    continue;
                }
        
                // [xxx_end(n)] 감지
                if (preg_match('/\[(func_def|rep|cond|self|struct|construct)_end\((\d+)\)\]/', $line, $end_matches)) {
                    $last = array_pop($stack);
                    if ($last['type'] === $end_matches[1] && $last['index'] === $end_matches[2]) {
                        // 자식 파싱 (재귀)
                        $children = parse_blocks_v2(implode("\n", $last['content_lines']), $depth + 1);
                        $block = [
                            'type' => $last['type'],
                            'index' => $last['index'],
                            'depth' => $last['depth'],
                            'children' => $children
                        ];
        
                        if (!empty($stack)) {
                            $stack[count($stack) - 1]['content_lines'][] = "__BLOCK__" . json_encode($block);
                        } else {
                            $blocks[] = $block;
                        }
                    }
                    continue;
                }
        
                // 일반 텍스트
                if (!empty($stack)) {
                    $stack[count($stack) - 1]['content_lines'][] = $line;
                } elseif (trim($line) !== '') {
                    $blocks[] = [
                        'type' => 'text',
                        'content' => $line,
                        'depth' => $depth
                    ];
                }
            }
        
            // 내부 BLOCK을 다시 children 배열로 디코딩
            foreach ($blocks as &$block) {
                if (isset($block['children'])) {
                    foreach ($block['children'] as &$child) {
                        if (is_string($child) && str_starts_with($child, "__BLOCK__")) {
                            $child = json_decode(substr($child, 9), true);
                        }
                    }
                }
            }
        
            return $blocks;
        }        
        
        $answer_index = 0;
        echo render_tree_plain($OJ_BLOCK_TREE, $answer_index);
        ?>
    </div>

    <!-- 오른쪽 패널 -->
    <div class="right-panel">
        <h2>📋 피드백 창</h2>
    </div>
</div>

<script>
    
const correctAnswers = <?= json_encode($OJ_CORRECT_ANSWERS) ?>;
const problemId = <?= json_encode($problem_id) ?>

function submitAnswer(index) {
    const ta = document.getElementById(`ta_${index}`);
    const btn = document.getElementById(`btn_${index}`);
    const check = document.getElementById(`check_${index}`);
    const input = ta.value.trim();
    const correct = (correctAnswers[index]?.content || "").trim();

    if (input === correct) {
        ta.readOnly = true;
        ta.style.backgroundColor = "#d4edda";
        ta.style.border = "1px solid #d4edda";
        ta.style.color = "#155724";
        btn.style.display = "none";
        check.style.display = "inline";
        const nextIndex = index + 1;
        const nextTa = document.getElementById(`ta_${nextIndex}`);
        const nextBtn = document.getElementById(`btn_${nextIndex}`);
        if (nextTa && nextBtn) {
            nextTa.disabled = false;
            nextBtn.disabled = false;
            nextTa.focus();
            nextTa.addEventListener('input', () => autoResize(nextTa));
        }
    } else {
        ta.style.backgroundColor = "#ffecec";
        ta.style.border = "1px solid #e06060";
        ta.style.color = "#c00";
    }
}

function showAnswer(index) {
    const correctCode = correctAnswers[index]?.content.trim();
    if (!correctCode) return;
    const answerArea = document.getElementById(`answer_area_${index}`);
    const answerHtml = `<strong>정답:</strong><br><pre class='code-line'>${correctCode}</pre>`;
    answerArea.innerHTML = answerHtml;
    answerArea.style.display = 'block';
}

function autoResize(ta) {
    ta.style.height = 'auto';
    ta.style.height = ta.scrollHeight + 'px';
}

let currentTextarea = null;
let animationRunning = false;

function updateImageForTextarea(index, ta) {
    currentTextarea = ta;
    fetch(`../../get_flowchart_image.php?problem_id=${problemId}&index=${index}`)
        .then(res => res.json())
        .then(data => {
            let img = document.getElementById("floating-img");
            if (!img) {
                img = document.createElement("img");
                img.id = "floating-img";
                document.body.appendChild(img);
            }

            img.src = data.url;
            console.log("서버 디버그 데이터:", data.debug);

            if (!animationRunning) {
                animationRunning = true;
                smoothFollowImage(); // 따라오기 시작
            }
        });
}

function smoothFollowImage() {
    const img = document.getElementById("floating-img");
    if (!img || !currentTextarea) {
        animationRunning = false;
        return;
    }

    const taRect = currentTextarea.getBoundingClientRect();
    const scrollY = window.scrollY || document.documentElement.scrollTop;

    let targetTop = taRect.top + scrollY - img.offsetHeight + 100;

    // 화면 기준 제한
    const minTop = scrollY + 10; // 화면 상단 + 여백
    const maxTop = scrollY + window.innerHeight - img.offsetHeight - 10; // 화면 하단 - 이미지 높이

    // 제한된 위치로 보정
    targetTop = Math.max(minTop, Math.min(targetTop, maxTop));

    const currentTop = parseFloat(img.style.top) || 0;
    const nextTop = currentTop + (targetTop - currentTop) * 0.1;

    img.style.top = `${nextTop}px`;

    requestAnimationFrame(smoothFollowImage);
}


// textarea 클릭 시 이미지 로드
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll("textarea[id^='ta_']").forEach((ta, idx) => {
        ta.addEventListener("focus", () => updateImageForTextarea(idx, ta));
    });

});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>