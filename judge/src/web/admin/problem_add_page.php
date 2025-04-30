<?php 
  require_once("../include/db_info.inc.php");
  require_once("admin-header.php");
  if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']) || isset($_SESSION[$OJ_NAME.'_'.'contest_creator']) || isset($_SESSION[$OJ_NAME.'_'.'problem_editor']))) {
    echo "<a href='../loginpage.php'>로그인 후 이용해 주세요!</a>";
    exit(1);
  }
?>
	  
<html>
<head>
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Content-Language" content="ko">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>문제 추가</title>
</head>	  
<?php
  echo "<center><h3>".$MSG_PROBLEM."-".$MSG_ADD."</h3></center>";
  include_once("kindeditor.php") ;
  $source=pdo_query("select source from problem order by problem_id desc limit 1"); //기본적으로 마지막 문제의 분류 태그 사용
  if(!empty($source)&&isset($source[0]))$source=$source[0][0];else $source="";
?>

<hr>
<body leftmargin="30" >
  <div id="main" class="padding">
    <form method=POST action=problem_add.php>
      <input type=hidden name=problem_id value="New Problem">
        <p align=left>
  <div class="ui toggle checkbox">
        <input type="checkbox" id="preview-toggle" checked>
        <label for="preview-toggle">제목 미리보기</label>
    </div>
          <?php echo "<h3>".$MSG_TITLE."</h3>"?>
          <input class="input input-large" style="width:100%;" type=text name='title' > <input type=submit value='<?php echo $MSG_SAVE?>' name=submit> 
	</p>
  <p align="left">
  <h4>문제 태그 선택</h4>
  <style>
    .accordion {
      cursor: pointer;
      padding: 10px;
      width: 100%;
      text-align: left;
      border: none;
      outline: none;
      transition: 0.4s;
      background-color: #eee;
      margin-top: 5px;
    }
    .active, .accordion:hover {
      background-color: #ccc;
    }
    .panel {
      padding-left: 20px;
      display: none;
      overflow: hidden;
      background-color: #f9f9f9;
    }
    .checkbox-group {
      margin-left: 20px;
    }
  </style>

  <?php
  // 🔹 1. 태그 전체 조회
  $tags = pdo_query("SELECT tag_id, name FROM tag");

  // 🔹 2. name => id 맵핑
  $tag_map = [];
  foreach ($tags as $tag) {
      $tag_map[$tag['name']] = $tag['tag_id'];
  }

  // 🔹 3. 카테고리 정의 (태그 이름 기준)
  $categories = [
    "C 언어 기초 문법 태그" => [
      "입출력 및 연산" => ["입출력", "사칙연산", "형 변환"],
      "조건문" => ["if문"],
      "반복문 및 제어문" => ["반복문"],
      "배열 및 문자열" => ["배열", "문자열 처리"],
      "함수" => ["기본 함수", "포인터 함수"],
      "포인터" => ["포인터 기본", "포인터와 배열", "포인터 연산"]
    ],
    "기초 알고리즘 태그" => [
      "수학 관련 문제" => ["최대공약수(GCD) / 최소공배수(LCM)", "피보나치 수열", "소수 판별", "약수 구하기", "배수와 나머지", "수학 관련 문제"],
      "배열과 정렬 알고리즘" => ["최대값/최소값 찾기", "배열 정렬", "중복 제거", "배열 뒤집기"],
      "탐색 알고리즘" => ["순차 탐색"],
      "구현 문제" => ["좌표 이동", "행렬 연산", "문자열 조작"]
    ]
  ];

  // 🔹 4. 렌더링
  foreach ($categories as $main_cat => $subcats) {
      echo "<button type='button' class='accordion'>$main_cat</button><div class='panel'>";
      foreach ($subcats as $subcat => $tag_names) {
          echo "<button type='button' class='accordion'>$subcat</button><div class='panel checkbox-group'>";
          foreach ($tag_names as $tag_name) {
              if (isset($tag_map[$tag_name])) {
                  $id = $tag_map[$tag_name];
                  $label = htmlspecialchars($tag_name, ENT_QUOTES, 'UTF-8');
                  echo "<label><input type='checkbox' name='tag_ids[]' value='$id'> $label</label><br>";
              }
          }
          echo "</div>";
      }
      echo "</div>";
  }
  ?>
</p>

<script>
  // 🔸 아코디언 동작 스크립트
  document.querySelectorAll(".accordion").forEach(function(btn) {
    btn.addEventListener("click", function () {
      this.classList.toggle("active");
      var panel = this.nextElementSibling;
      panel.style.display = (panel.style.display === "block") ? "none" : "block";
    });
  });
</script>
        <p align=left>
          <?php echo $MSG_Time_Limit?>
          <input class="input input-mini" type=number min="0.001" max="300" step="0.001" name=time_limit size=20 value=1> 초
          <?php echo $MSG_Memory_Limit?>
          <input class="input input-mini" type=number min="1" max="2048" step="1" name=memory_limit size=20 value=128> MiB<br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Description."(<64kB)</h4>"?>
	  <textarea class="kindeditor" rows=13 name=description cols=80><span class='md auto_select'>&nbsp;
&nbsp;</span></textarea><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Input."(<64kB)</h4>"?>
          <textarea class="kindeditor" rows=13 name=input cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Output."(<64kB)</h4>"?>
          <textarea  class="kindeditor" rows=13 name=output cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Sample_Input."(<64kB)</h4>"?>
          <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_input></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Sample_Output."(<64kB)</h4>"?>
          <textarea  class="input input-large" style="width:100%;" rows=13 name=sample_output></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Test_Input."</h4>"?>
          <?php echo "(".$MSG_HELP_MORE_TESTDATA_LATER.")"?><br>
          <textarea class="input input-large" style="width:100%;" rows=13 name=test_input></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_Test_Output."</h4>"?>
          <?php echo "(".$MSG_HELP_MORE_TESTDATA_LATER.")"?><br>
          <textarea class="input input-large" style="width:100%;" rows=13 name=test_output></textarea><br><br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_HINT."(<64kB)</h4>"?>
          <textarea class="kindeditor" rows=13 name=hint cols=80><span class='md'>
</span></textarea><br></textarea><br>
        </p>
        <p align=left>
          <!-- 모범 코드 제목 -->
          <h4>모범코드</h4>
          <!-- 코드 입력 텍스트 영역 -->
          <textarea class="input input-large" style="width:100%;" rows=13 name=exemplary_code></textarea><br><br>
        </span></textarea><br>
        </p>
        <p>
          <?php echo "<h4>".$MSG_SPJ."</h4>"?>
	  <input type=radio name=spj value='0' checked ><?php echo $MSG_NJ?> 추가 테스트 데이터는 문제 추가 후 제공됩니다.<br> 
	  <input type=radio name=spj value='1' ><?php echo $MSG_SPJ?> <?php echo "(".$MSG_HELP_SPJ.")"?><br>
	  <input type=radio name=spj value='2' ><?php echo $MSG_RTJ?>(빈칸 문제 선택용, 사용법은<a target='_blank' href='http://hustoj.com'>hustoj.com</a>)<br>
        </p>
        <p align=left>
          <?php echo "<h4>".$MSG_SOURCE."</h4>"?>
          <textarea name=source style="width:100%;" rows=1><?php echo htmlentities($source,ENT_QUOTES,'UTF-8') ?></textarea><br><br>
        </p>
        <p align=left><?php echo "<h4>".$MSG_CONTEST."</h4>"?>
          <select name=contest_id>
            <?php
            $sql="SELECT `contest_id`,`title` FROM `contest` WHERE `start_time`>NOW() order by `contest_id`";
            $result=pdo_query($sql);
            echo "<option value=''>없음</option>";
            if (count($result)==0) {
            }
            else {
              foreach ($result as $row) {
                echo "<option value='{$row['contest_id']}'>{$row['contest_id']} {$row['title']}</option>";
              }
            }?>
          </select>
        </p>

        <div align=center>
          <?php require_once("../include/set_post_key.php");?>
          <input type=submit value='<?php echo $MSG_SAVE?>' name=submit>
        </div>
     
    </form>
  </div>
<script src="<?php echo $OJ_CDN_URL."/template/bs3/"?>marked.min.js"></script>
<script>
  function transform(){
        let height=document.body.clientHeight;
        let width=parseInt(document.body.clientWidth*0.6);
        let width2=parseInt(document.body.clientWidth*0.4);
	if(width<500) width2=300;
        let submitURL="../problem.php?id=1000";
        console.log(width);
        let main=$("#main");
        let problem=main.html();
                main.removeClass("container");
                main.css("width",width2);
                main.css("margin-left","10px");
                main.parent().append("<div id='preview' class='container' style='opacity:0.95;position:fixed;z-index:1000;top:49px;right:-"+width2+"px'></div>");
                $("#preview").html("<iframe id='previewFrame' src='"+submitURL+"&spa' width='"+width+"px' height='"+height+"px' ></iframe>");
        $("#submit").remove();
        setTimeout('hide()',1500);	
	$("input").keyup(sync);
	$("textarea").keyup(sync);
  }
  function hide(){
	let preview=$("#previewFrame").contents();
	preview.find(".ui.buttons").hide();
	preview.find("span.ui.label").eq(2).hide();
	preview.find("span.ui.label").eq(3).hide();
	preview.find("span.ui.label").eq(4).hide();
	preview.find("span.ui.label").eq(5).hide();
	preview.find("#show_tag_div").parent().hide();
	sync();
//	preview.find("h1:first").parent().parent().hide();
  }
  function sync(){
	console.log("sync...");
	let preview=$("#previewFrame").contents();
	let title=$("input[name=title]").val();
	preview.find("h1:first").html(title);
	let time=$("input[name=time_limit]").val();
	preview.find("span.ui.label").eq(0).html("<?php echo $MSG_Time_Limit ?>："+time);
	let memory=$("input[name=memory_limit]").val();
	preview.find("span.ui.label").eq(1).html("<?php echo $MSG_Memory_Limit ?>："+memory);
	
	let description=$("textarea").eq(1).val();
	preview.find("#description").html(description);
	preview.find("#description .md").each(function(){
		$(this).html(marked.parse($(this).html()));
	});
  
	let input=$("textarea").eq(3).val();
	preview.find("#input").html(input);
	preview.find("#input .md").each(function(){
		$(this).html(marked.parse($(this).html()));
	});
	let output=$("textarea").eq(5).val();
	preview.find("#output").html(output);
	preview.find("#output .md").each(function(){
		$(this).html(marked.parse($(this).html()));
	});

	let sinput=$("textarea").eq(6).val();
	preview.find("#sinput").html(sinput);
	let soutput=$("textarea").eq(7).val();
	preview.find("#soutput").html(soutput);
	let hint=$("textarea").eq(11).val();
	preview.find("#hint").html(hint);
	preview.find("#hint .md").each(function(){
		$(this).html(marked.parse($(this).html()));
	});
	if($("#previewFrame")[0] != undefined) $("#previewFrame")[0].contentWindow.MathJax.typeset();
  }
 
   $(document).ready(function(){
            // 기본적으로 미리보기 기능을 활성화
           <?php if (!(isset($OJ_OLD_FASHINED) && $OJ_OLD_FASHINED )) echo " transform();" ?>
            
            // 체크박스 클릭 이벤트 리스너
            $('#preview-toggle').change(function() {
                if(this.checked) {
                    transform();
                } else {
                    // 미리보기 기능 종료
                    untransform();
                }
            });
        });
function untransform() {
    console.log("미리보기 종료");
    // 원래 #main 요소 스타일 복원
    let main = $("#main");
    main.addClass("padding");
    main.css("width", "");
    main.css("margin-left", "");

    // 미리보기 iframe 제거Q
    $("#preview").remove();

  
    // 동기화 이벤트 제거
    $("input").off('keyup', sync);
    $("textarea").off('keyup', sync);
}

</script>
</body>
</html>
