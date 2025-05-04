<?php $show_title = "제출 - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>

<style>
	#source {
		width: 80%;
		height: 600px;
	}

	.ace_gutter-cell {
		background-color: #ffffff;
	}

	.ace-chrome .ace_marker-layer .ace_active-line {
		background-color: rgba(0, 0, 199, 0.3);
	}

	.button,
	input,
	optgroup,
	select,
	textarea {
		font-family: sans-serif;
		font-size: 150%;
		line-height: 1.2;

	}
</style>

<center>

	<script src="<?php echo $OJ_CDN_URL ?>include/checksource.js"></script>
	<form id=frmSolution action="submit.php<?php if (isset($_GET['spa']))
		echo "?spa" ?>" method="post"
			onsubmit='do_submit()'>
		<?php if (isset($id)) { ?>
			<span style="color:#0000ff">Problem <b>
					<?php echo $id ?>
				</b></span>
			<input id=problem_id type='hidden' value='<?php echo $id ?>' name="id">
		<?php } else {
		//$PID="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
//if ($pid>25) $pid=25;
		?>
			Problem <span class=blue><b><?php echo chr($pid + ord('A')) ?></b></span> of Contest <span class=blue><b><?php echo $cid ?></b></span>
			<input id="cid" type='hidden' value='<?php echo $cid ?>' name="cid">
			<input id="pid" type='hidden' value='<?php echo $pid ?>' name="pid">
		<?php } ?>
		<span id="language_span">Language:
			<select id="language" name="language" onChange="reloadtemplate($(this).val());">
				<?php
				$lang_count = count($language_ext);
				if (isset($_GET['langmask']))
					$langmask = $_GET['langmask'];
				else
					$langmask = $OJ_LANGMASK;
				$lang = (~((int) $langmask)) & ((1 << ($lang_count)) - 1);
				//$lastlang=$_COOKIE['lastlang'];
//if($lastlang=="undefined") $lastlang=1;
				for ($i = 0; $i < $lang_count; $i++) {
					if ($lang & (1 << $i))
						echo "<option value=$i " . ($lastlang == $i ? "selected" : "") . ">
" . $language_name[$i] . "
</option>";
				}
				?>
			</select>
			<?php if ($OJ_VCODE) { ?>
				<?php echo $MSG_VCODE ?>:
				<input name="vcode" size=4 type=text><img id="vcode" alt="click to change" src="vcode.php"
					onclick="this.src='vcode.php?'+Math.random()">
			<?php } ?>
			<button id="Submit" type="button" class="ui primary icon button" onclick="do_submit();">
				<?php echo $MSG_SUBMIT ?>
			</button>
			<?php if (isset($OJ_ENCODE_SUBMIT) && $OJ_ENCODE_SUBMIT) { ?>
				<input class="btn btn-success" title="WAF gives you reset ? try this." type=button
					value="Encoded <?php echo $MSG_SUBMIT ?>" onclick="encoded_submit();">
				<input type=hidden id="encoded_submit_mark" name="reverse2" value="reverse" />
			<?php } ?>
			<?php if (isset($OJ_TEST_RUN) && $OJ_TEST_RUN) { ?>
				<input id="TestRun" class="btn btn-info" type=button value="<?php echo $MSG_TR ?>" onclick=do_test_run();>

			<?php } ?>
		</span>
		<?php if ($spj <= 1): ?>
			<button onclick="increaseFontSize(event)"
				style="background-color: bisque; position: absolute; top: 5px; right:120px;" v-if="false">
				<i>✚</i>
			</button>
			<button onclick="decreaseFontSize(event)"
				style="background-color: bisque; position: absolute; top: 5px; right: 80px;" v-if="false">
				<i>━</i>
			</button>
		<?php endif; ?>

		<?php if ($OJ_ACE_EDITOR) {

			if (isset($OJ_TEST_RUN) && $OJ_TEST_RUN)
				$height = "400px";
			else
				$height = "500px";
			?>



			<pre style="width:90%;height:<?php echo $height ?>" cols=180 rows=16
				id="source"><?php echo htmlentities($view_src, ENT_QUOTES, "UTF-8") ?></pre>
			<input type=hidden id="hide_source" name="source" value="" />

		<?php } else { ?>
			<textarea style="width:80%;height:600" cols=180 rows=25 id="source"
				name="source"><?php echo htmlentities($view_src, ENT_QUOTES, "UTF-8") ?></textarea>
		<?php } ?>

		<?php if (isset($OJ_TEST_RUN) && $OJ_TEST_RUN) { ?>
			<?php echo $MSG_Input ?>:<textarea style="width:30%" cols=40 rows=5 id="input_text"
				name="input_text"><?php echo $view_sample_input ?></textarea>
			<?php echo $MSG_Output ?>:
			<textarea style="width:30%" cols=10 rows=5 id="out" name="out" disabled="true">SHOULD BE:
	<?php echo $view_sample_output ?>
	</textarea>
		<?php } ?>


		<?php if (isset($OJ_BLOCKLY) && $OJ_BLOCKLY) { ?>
			<input id="blockly_loader" type=button class="btn" onclick="openBlockly()"
				value="<?php echo $MSG_BLOCKLY_OPEN ?>" style="color:white;background-color:rgb(169,91,128)">
			<input id="transrun" type=button class="btn" onclick="loadFromBlockly() " value="<?php echo $MSG_BLOCKLY_TEST ?>"
				style="display:none;color:white;background-color:rgb(90,164,139)">
			<div id="blockly" class="center">Blockly</div>
		<?php } ?>
	</form>
</center>

<script>
	var sid = 0; // 현재 확인 중인 solution_id
    var i = 0;
    var using_blockly = false; // Blockly 사용 여부
    var judge_result = [<?php foreach ($judge_result as $result) { echo "'$result',"; } ?>'']; // 채점 결과 문자열 배열

    // 채점 결과를 출력하는 함수 (테스트 실행 시 사용)
    function print_result(solution_id) {
        sid = solution_id;
        $("#out").load("status-ajax.php?tr=1&solution_id=" + solution_id); // AJAX로 결과 받아오기
    }

    // 주기적으로 결과를 갱신하는 함수 (Test Run 또는 채점 중일 때)
    function fresh_result(solution_id) {
        var tb = window.document.getElementById('result');
        if (solution_id == undefined) {
            tb.innerHTML = "Vcode Error!"; // 인증 코드 오류
            if ($("#vcode") != null) $("#vcode").click(); // 새로고침
            return;
        }
        sid = solution_id;

        // 브라우저 호환성을 위한 XMLHttpRequest 객체 생성
        var xmlhttp;
        if (window.XMLHttpRequest) {
            xmlhttp = new XMLHttpRequest(); // 최신 브라우저용
        }
        else {
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP"); // 구형 IE용
        }

        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                var r = xmlhttp.responseText;
                var ra = r.split(",");
                var loader = "<img width=18 src=image/loader.gif>"; // 로딩 표시
                var tag = "span";
                if (ra[0] < 4) tag = "span disabled=true"; // 아직 채점 중
                else tag = "a"; // 완료된 경우

                // 결과 상태에 따라 링크 생성
                if (ra[0] == 11) {
                    tb.innerHTML = "<" + tag + " href='ceinfo.php?sid=" + solution_id + "' class='badge badge-info' target=_blank>" + judge_result[ra[0]] + "</" + tag + ">";
                } else {
                    tb.innerHTML = "<" + tag + " href='reinfo.php?sid=" + solution_id + "' class='badge badge-info' target=_blank>" + judge_result[ra[0]] + "AC:" + ra[4] + "</" + tag + ">";
                }

                if (ra[0] < 4) tb.innerHTML += loader; // 채점 중이면 로딩 애니메이션

                // 메모리/시간 결과 출력
                tb.innerHTML += "Memory:" + ra[1] + "&nbsp;&nbsp;";
                tb.innerHTML += "Time:" + ra[2] + "";

                // 반복 호출을 통한 주기적 갱신 또는 최종 결과 표시
                if (ra[0] < 4)
                    window.setTimeout("fresh_result(" + solution_id + ")", 2000);
                else {
                    window.setTimeout("print_result(" + solution_id + ")", 2000);
                    count = 1;
                }
            }
        }

        xmlhttp.open("GET", "status-ajax.php?solution_id=" + solution_id, true);
        xmlhttp.send(); // AJAX 요청 전송
    }

    // iframe에서 채점 결과의 solution_id 값을 가져오는 함수 (테스트 실행용)
    function getSID() {
        var ofrm1 = document.getElementById("testRun").document;
        var ret = "0";

        if (ofrm1 == undefined) {
            ofrm1 = document.getElementById("testRun").contentWindow.document;
            var ff = ofrm1;
            ret = ff.innerHTML;
        }
        else {
            var ie = document.frames["frame1"].document;
            ret = ie.innerText;
        }

        return ret + "";
    }

    var count = 0; // 자동 재활성화를 위한 카운터 (submit 버튼 재활성화 타이머용)

	function encoded_submit() {
	// 현재 문제 ID 또는 대회 문제 ID를 가져옴
	var mark = "<?php echo isset($id) ? 'problem_id' : 'cid'; ?>";
	var problem_id = document.getElementById(mark);

	// ACE 에디터가 사용 중이면, 숨겨진 입력란에 코드 복사
	if (typeof (editor) != "undefined")
		$("#hide_source").val(editor.getValue());

	// 문제 번호 설정
	if (mark == 'problem_id')
		problem_id.value = '<?php if (isset($id)) echo $id ?>';
	else
		problem_id.value = '<?php if (isset($cid)) echo $cid ?>';

	// 제출 대상 설정 및 인코딩 마크 삽입
	document.getElementById("frmSolution").target = "_self";
	document.getElementById("encoded_submit_mark").name = "encoded_submit";

	// 소스 코드 Base64 인코딩
	var source = $("#source").val();
	if (typeof (editor) != "undefined") {
		source = editor.getValue();
		$("#hide_source").val(encode64(utf16to8(source))); // 유니코드 → UTF8 → Base64
	} else {
		$("#source").val(encode64(utf16to8(source)));
	}

	// submit 폼 전송
	document.getElementById("frmSolution").submit();
    }

function do_submit() {
	$("#Submit").attr("disabled", "true");   // 중복 클릭 방지용 버튼 비활성화
    $.post("update_submit_count.php", function(response){
        console.log("Submit count updated:", response);
    });
	if (using_blockly)
		translate(); // Blockly 코드 변환

	if (typeof (editor) != "undefined") {
		$("#hide_source").val(editor.getValue()); // 에디터 내용 저장
	}

	

	setTimeout(() => {
		
	}, (function() {	var mark = "<?php echo isset($id) ? 'problem_id' : 'cid'; ?>";
		var problem_id = document.getElementById(mark);},20000));
	// var mark = "<?php echo isset($id) ? 'problem_id' : 'cid'; ?>";
	// var problem_id = document.getElementById(mark);

	if (mark == 'problem_id')
		problem_id.value = '<?php if (isset($id)) echo $id ?>';
	else
		problem_id.value = '<?php if (isset($cid)) echo $cid ?>';

	document.getElementById("frmSolution").target = "_self";

<?php if (isset($_GET['spa'])) { ?>
	// SPA 모드일 경우 AJAX로 제출
	$.post("submit.php?ajax", $("#frmSolution").serialize(), function (data) { fresh_result(data); });
	$("#Submit").prop('disabled', true);
	$("#TestRub").prop('disabled', true);
	count = <?php echo $OJ_SUBMIT_COOLDOWN_TIME ?> * 2;
	handler_interval = window.setTimeout("resume();", 1000);
<?php } else { ?>
	// 기본 모드일 경우 폼 제출
	document.getElementById("frmSolution").submit();
<?php } ?>
}

var handler_interval; // 제출 쿨다운 타이머

function do_test_run() {
	if (handler_interval) window.clearInterval(handler_interval); // 기존 타이머 제거

	var loader = "<img width=18 src=image/loader.gif>"; // 로딩 이미지
	var tb = window.document.getElementById('result');
	var source = $("#source").val();

	// ACE 에디터 내용 반영
	if (typeof (editor) != "undefined") {
		source = editor.getValue();
		$("#hide_source").val(source);
	}

	if (source.length < 10) return alert("too short!"); // 너무 짧은 코드 방지
	if (tb != null) tb.innerHTML = loader;

	var mark = "<?php echo isset($id) ? 'problem_id' : 'cid'; ?>";
	var problem_id = document.getElementById(mark);
	problem_id.value = -problem_id.value; // 음수화 → test run 플래그

	document.getElementById("frmSolution").target = "testRun"; // iframe 사용 안 함

	// AJAX로 test run 실행
	$.post("submit.php?ajax", $("#frmSolution").serialize(), function (data) { fresh_result(data); });

	// 버튼 비활성화 및 카운트다운
	$("#Submit").prop('disabled', true);
	$("#TestRub").prop('disabled', true);
	problem_id.value = -problem_id.value;
	count = <?php echo isset($OJ_SUBMIT_COOLDOWN_TIME) ? $OJ_SUBMIT_COOLDOWN_TIME : 5 ?> * 2;
	handler_interval = window.setTimeout("resume();", 1000);
}

function resume() {
	count--; // 카운트 감소

	var s = $("#Submit")[0];
	var t = $("#TestRub")[0];

	if (count < 0) {
		// 버튼 재활성화
		s.disabled = false;
		if (t != null) t.disabled = false;
		$("#Submit").text("<?php echo $MSG_SUBMIT ?>");
		if (t != null) t.value = "<?php echo $MSG_TR ?>";
		if (handler_interval) window.clearInterval(handler_interval);
		if ($("#vcode") != null) $("#vcode").click(); // CAPTCHA 갱신
	} else {
		// 남은 시간 표시
		$("#Submit").text("<?php echo $MSG_SUBMIT ?>(" + count + ")");
		if (t != null) t.value = "<?php echo $MSG_TR ?>(" + count + ")";
		window.setTimeout("resume();", 1000);
	}
}

function switchLang(lang) {
	// 언어 선택에 따라 ACE 모드 전환
	var langnames = new Array("c_cpp", "c_cpp", "pascal", "java", "ruby", "sh", "python", "php", "perl", "csharp", "objectivec", "vbscript", "scheme", "c_cpp", "c_cpp", "lua", "javascript", "golang");
	editor.getSession().setMode("ace/mode/" + langnames[lang]);
}

function reloadtemplate(lang) {
	console.log("lang=" + lang);
	document.cookie = "lastlang=" + lang.value; // 쿠키에 저장

	var url = window.location.href;
	var i = url.indexOf("sid=");
	if (i != -1) url = url.substring(0, i - 1);

	switchLang(lang); // 에디터 구문 강조 변경
}

function openBlockly() {
	// 블록리 인터페이스 열기
	$("#source").hide();
	$("#TestRun").hide();
	$("#language")[0].scrollIntoView();
	$("#language").val(6).hide(); // Python 고정
	$("#EditAreaArroundInfos_source").hide();
	$('#blockly').html('<iframe name=\'frmBlockly\' width=90% height=580 src=\'blockly/demos/code/index.html\'></iframe>');
	$("#blockly_loader").hide();
	$("#transrun").show();
	using_blockly = true;
}

function translate() {
	// Blockly에서 Python 코드 가져오기
	var blockly = $(window.frames['frmBlockly'].document);
	var tb = blockly.find('td[id=tab_python]');
	var python = blockly.find('pre[id=content_python]');
	tb.click(); // Python 탭 클릭
	blockly.find('td[id=tab_blocks]').click(); // 다시 블록 탭으로 돌아감

	if (typeof (editor) != "undefined") editor.setValue(python.text());
	else $("#source").val(python.text());
	$("#language").val(6); // 언어: Python
}

function loadFromBlockly() {
	translate();       // 블록리 → 코드 변환
	do_test_run();     // 테스트 실행
	$("#frame_source").hide(); // 기존 에디터 숨김
}

</script>

<script language="Javascript" type="text/javascript" src="<?php echo $OJ_CDN_URL ?>include/base64.js"></script>

<?php if ($OJ_ACE_EDITOR) { ?>
	<!-- ACE Editor 및 언어 도구 확장 스크립트 로딩 -->
	<script src="<?php echo $OJ_CDN_URL ?>ace/ace.js"></script>
	<script src="<?php echo $OJ_CDN_URL ?>ace/ext-language_tools.js"></script>
	<script>
		ace.require("ace/ext/language_tools"); // 자동완성 기능 활성화에 필요한 확장 모듈 불러오기
		var editor = ace.edit("source"); // ACE Editor 인스턴스 생성 (id="source" 요소에 적용)
		editor.setTheme("ace/theme/xcode"); // 기본 테마를 Xcode 스타일로 설정
		switchLang(<?php echo $lastlang ?>); // 마지막으로 사용한 언어 구문 강조 설정

		// ACE 에디터 설정 구성
		editor.setOptions({
			enableBasicAutocompletion: true,     // 기본 자동완성 기능 활성화
			enableSnippets: true,                // 코드 스니펫 기능 활성화
			enableLiveAutocompletion: true,      // 실시간 자동완성 기능 활성화

			// fontFamily: "Consolas",            // (선택사항) 폰트 설정 예시
			// theme: "ace/theme/ambiance",       // (선택사항) 어두운 테마 예시
			fontSize: "18px"                     // 폰트 크기 설정
		});

		reloadtemplate($("#language").val()); // 언어 선택 값 기준으로 구문 강조 다시 설정

		// 코드 자동 저장 기능: 5초마다 localStorage에 백업 저장
		function autoSave() {
			var mark = "<?php echo isset($id) ? 'problem_id' : 'cid'; ?>";
			var problem_id = $("#" + mark).val();
			if (!!localStorage) {
				let key = "<?php echo $_SESSION[$OJ_NAME . '_user_id'] ?>source:" + location.href;
				if (typeof (editor) != "undefined")
					$("#hide_source").val(editor.getValue()); // 현재 에디터 내용 저장
				localStorage.setItem(key, $("#hide_source").val()); // localStorage에 백업
			}
		}

		// 페이지 로딩 완료 시 실행되는 함수
		$(document).ready(function () {
			$("#source").css("height", window.innerHeight - 180); // 편집 영역 높이 조정

			// 저장된 자동 백업이 있을 경우 불러오기
			if (!!localStorage) {
				let key = "<?php echo $_SESSION[$OJ_NAME . '_user_id'] ?>source:" + location.href;
				let saved = localStorage.getItem(key);
				if (saved != null && saved != "" && saved.length > editor.getValue().length) {
					//let load = confirm("자동 저장된 코드가 있습니다. 지금 불러올까요? (한 번만 선택할 수 있습니다)");
					//if(load){
					console.log("loading " + saved.length); // 불러온 코드 길이 확인
					if (typeof (editor) != "undefined")
						editor.setValue(saved); // 자동 저장된 코드 복원
					//}
				}
			}

			// 5초마다 자동 저장 실행
			window.setInterval('autoSave();', 5000);
		});
	</script>

	<!-- 에디터 사용자 편의 기능: 폰트 크기 조절 / 테마 전환 -->
	<script>
		function increaseFontSize(event) {
			event.preventDefault();
			var currentSize = parseInt(editor.getFontSize());
			editor.setFontSize(currentSize + 3); // 폰트 크기 키우기
		}

		function decreaseFontSize(event) {
			event.preventDefault();
			var currentSize = parseInt(editor.getFontSize());
			editor.setFontSize(currentSize - 3); // 폰트 크기 줄이기
		}

	</script>
<?php } ?>

</body>
</html>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
