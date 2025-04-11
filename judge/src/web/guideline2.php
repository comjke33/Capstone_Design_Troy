<?php
$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// Define patterns to extract function definitions, loops, and conditionals
preg_match_all("/func_def_start\((.*?)\)(.*?)func_def_end\((.*?)\)/s", $file_contents, $functions);
preg_match_all("/rep_start\((.*?)\)(.*?)rep_end\((.*?)\)/s", $file_contents, $loops);
preg_match_all("/cond_start\((.*?)\)(.*?)cond_end\((.*?)\)/s", $file_contents, $conditionals);
preg_match_all("/self_start\((.*?)\)(.*?)self_end\((.*?)\)/s", $file_contents, $self_blocks);

// Main container for all code blocks
echo "<div class='main-container'>";

// Render functions within a sub-container
echo "<div class='functions-container'>";
foreach ($functions[0] as $index => $function) {
    $func_name = extract_function_name($function);
    $function_content = $functions[2][$index];
    echo "<div class='code-block function'>";
    echo "<h3>Function: $func_name</h3>";
    echo "<p>" . nl2br($function_content) . "</p>";
    echo "</div>";
}
echo "</div>"; // End of functions-container

// Render loops within a sub-container
echo "<div class='loops-container'>";
foreach ($loops[0] as $index => $loop) {
    $loop_info = extract_loop_info($loop);
    $loop_content = $loops[2][$index];
    echo "<div class='code-block loop'>";
    echo "<h3>Loop: $loop_info</h3>";
    echo "<p>" . nl2br($loop_content) . "</p>";
    echo "</div>";
}
echo "</div>"; // End of loops-container

// Render conditionals within a sub-container
echo "<div class='conditionals-container'>";
foreach ($conditionals[0] as $index => $conditional) {
    $conditional_info = extract_conditional_info($conditional);
    $conditional_content = $conditionals[2][$index];
    echo "<div class='code-block conditional'>";
    echo "<h3>Conditional: $conditional_info</h3>";
    echo "<p>" . nl2br($conditional_content) . "</p>";
    echo "</div>";
}
echo "</div>"; // End of conditionals-container

// Render self-contained blocks within a sub-container
echo "<div class='self-blocks-container'>";
foreach ($self_blocks[0] as $index => $self_block) {
    $self_block_info = extract_self_block_info($self_block);
    $self_block_content = $self_blocks[2][$index];
    echo "<div class='code-block self-block'>";
    echo "<h3>Self-Block: $self_block_info</h3>";
    echo "<p>" . nl2br($self_block_content) . "</p>";
    echo "</div>";
}
echo "</div>"; // End of self-blocks-container

// Close main container
echo "</div>"; // End of main-container

// Helper functions for extracting details
function extract_function_name($function) {
    preg_match("/func_def_start\((.*?)\)/", $function, $matches);
    return $matches[1] ?? 'Unknown Function';
}

function extract_loop_info($loop) {
    preg_match("/rep_start\((.*?)\)/", $loop, $matches);
    return $matches[1] ?? 'Unknown Loop';
}

function extract_conditional_info($conditional) {
    preg_match("/cond_start\((.*?)\)/", $conditional, $matches);
    return $matches[1] ?? 'Unknown Conditional';
}

function extract_self_block_info($self_block) {
    preg_match("/self_start\((.*?)\)/", $self_block, $matches);
    return $matches[1] ?? 'Unknown Self-Block';
}
?>
