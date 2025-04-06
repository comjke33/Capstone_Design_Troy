<?php
if ($ok==true){

// 언어처리
$brush=strtolower($language_name[$slanguage]);
if ($brush=='pascal') $brush='delphi';
if ($brush=='obj-c') $brush='c';
if ($brush=='python3'||$brush=='cangjie') $brush='python';
if ($brush=='clang') $brush='c';
if ($brush=='clang++') $brush='c++';
if ($brush=='freebasic') $brush='vb';
if ($brush=='swift') $brush='csharp';
echo "<pre class=\"brush:".$brush.";\">";
ob_start();
echo "/**************************************************************\n";
//echo "\tProblem: $sproblem_id\n\tUser: $suser_id\n";
echo "\tProblem: $sproblem_id\n\tUser: $suser_id [$nick] \n";
echo "\tLanguage: ".$language_name[$slanguage]."\n\tResult: ".$judge_result[$sresult]."\n";
if ($sresult==4){
echo "\tTime:".$stime." ms\n";
echo "\tMemory:".$smemory." kb\n";
}
echo "****************************************************************/\n\n";
$auth=ob_get_contents();
ob_end_clean();

// 실제 코드 내용은 htmlentities를 사용하여 HTML 태그가 출력되지 않도록 안전하게 변환
echo htmlentities(str_replace("\n\r","\n",$view_source),ENT_QUOTES,"utf-8")."\n".$auth."</pre>";
}else{
echo "I am sorry, You could not view this code!";
}
?>
