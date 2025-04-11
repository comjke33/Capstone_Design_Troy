<?php
$file_path = "/home/Capstone_Design_Troy/test/test.txt";
$file_contents = file_get_contents($file_path);

// Pattern matching to extract function definitions, loops, etc.
preg_match_all("/\bfunc_def\b(.*?)(?=\bfunc_def\b|$)/s", $file_contents, $functions);

// Loop through functions, creating HTML
foreach ($functions[0] as $function) {
    echo "<div class='function-box'>";
    echo "<h3>Function: " . extract_function_name($function) . "</h3>";
    echo "<p>" . nl2br($function) . "</p>";
    echo "</div>";
}

// Extract loop structures
preg_match_all("/\brep_start\b(.*?)(?=\brep_start\b|$)/s", $file_contents, $loops);

// Loop through loops
foreach ($loops[0] as $loop) {
    echo "<div class='loop-box'>";
    echo "<h3>Loop: " . extract_loop_info($loop) . "</h3>";
    echo "<p>" . nl2br($loop) . "</p>";
    echo "</div>";
}

// Example helper functions
function extract_function_name($function) {
    preg_match("/func_def_start\((.*?)\)/", $function, $matches);
    return $matches[1] ?? 'Unknown Function';
}

function extract_loop_info($loop) {
    preg_match("/rep_start\((.*?)\)/", $loop, $matches);
    return $matches[1] ?? 'Unknown Loop';
}
?>
