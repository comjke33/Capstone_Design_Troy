<?php $show_title=$id." - $MSG_Source_Compare - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<!--CodeMirror + Mergely: 코드 비교에 사용되는 JS/CSS 라이브러리 -->
<link type="text/css" rel="stylesheet" href="mergely/codemirror.css" />
<link type="text/css" rel="stylesheet" href="mergely/mergely.css" />

<body style="width: 100%;">
	<table  style="width: 100%;"><tr>
		<!-- 공백 무시 비교기능 켜고 끄기 -->
		<td style="width: 50%;"><input type="checkbox" id="ignorews">ignore witespaces</td>
	</table>
	<br/>

	<table  style="width: 100%;"><tr>

		<!-- 좌측/우측 코드 이름 표시 및 코드 저장 기능 제공 -->
		<td style="width: 50%;"><tt id="path-lhs"></tt> &nbsp; <a id="save-lhs" class="save-link" href="#">save</a></td>
		<td style="width: 50%;"><tt id="path-rhs"></tt> &nbsp; <a id="save-rhs" class="save-link" href="#">save</a></td>
	</table>

	<div id="mergely-resizer" style="height: 450px;">

		<!-- 코드 비교가 렌더링 되는 영역 -->
		<div id="compare">
		</div>
	</div>

</body>
	<script type="text/javascript" src="mergely/codemirror.js"></script>
	<script type="text/javascript" src="mergely/mergely.js"></script>
	
	<script type="text/javascript">
        $(document).ready(function () {

						// 비교기 초기화
		        $('#compare').mergely({
                                width: 'auto',
                                height: 'auto', // containing div must be given a height
                                cmsettings: { readOnly: false },
                        });

												//좌우 소스 로드
                        var lhs_url =  'getsource.php?id=<?php echo intval($_GET['left'])?>';
                        var rhs_url = 'getsource.php?id=<?php echo intval($_GET['right'])?>';
                        $.ajax({
                                type: 'GET', async: true, dataType: 'text',
				url: lhs_url,
                                success: function (response) {
                                        $('#path-lhs').text(lhs_url);
                                        $('#compare').mergely('lhs', response);
                                }
                        });
                        $.ajax({
                                type: 'GET', async: true, dataType: 'text',
				url: rhs_url,
                                success: function (response) {
                                        $('#path-rhs').text(rhs_url);
                                        $('#compare').mergely('rhs', response);
                                }
                        });
			
			function checkFileList(files) {
				if (typeof window.FileReader !== 'function')
					error_msg("The file API isn't supported on this browser yet.");

				if (files.length>0) readFile(files[0], "lhs");
				if (files.length>1) readFile(files[1], "rhs");
			}

			function readFile(file, side) {
				var reader = new FileReader();
				reader.onload = function file_onload() {
					// document.getElementById('td1').innerHTML = ..
					$('#path-'+side).text(file.name);
					$('#compare').mergely(side, reader.result);
				}
				reader.readAsBinaryString(file);

			}
			function handleDragOver(evt) {
				evt.stopPropagation();
				evt.preventDefault();
				evt.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
			}
			function handleFileSelect(evt) {
				document.getElementById('drop_zone').visibility = "collapse";
				evt.stopPropagation();
				evt.preventDefault();
				var files = evt.dataTransfer.files; // FileList object.
				checkFileList(files);
			}
			var dropZone = document.getElementById('drop_zone');
			document.body.addEventListener('dragover', handleDragOver, false);
			//파일 끌어다 놓으면 좌/우 영역에 표시 가능
			//FileReader를 통해 로컬 텍스트 파일 읽어 비교 가능
			document.body.addEventListener('drop', handleFileSelect, false);

			//비교 결과 저장 기능
			function download_content(a, side) {
				//a.innerHTML = "preparing content..";
				
				//체크박스를 통해 공백 무시 비교를 켜고 끔
				var txt = $('#compare').mergely('get', side);
				var datauri = "data:plain/text;charset=UTF-8," + encodeURIComponent(txt);
				a.setAttribute('download', side+".txt");
				a.setAttribute('href', datauri);
				//a.innerHTML = "content ready.";
			}
			document.getElementById('save-lhs').addEventListener('mouseover', function() { download_content(this, "lhs"); }, false);
			document.getElementById('save-rhs').addEventListener('mouseover', function() { download_content(this, "lhs"); }, false);
			document.getElementById('ignorews').addEventListener('change', function() {
				$('#compare').mergely('options', { ignorews: this.checked });
			}, false);


		});
	</script>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
