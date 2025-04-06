<?php $show_title="$view_title - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class='padding'>
<h1><?php echo  $view_title ?></h1>

<!-- JS가 이 내용을 마크다운으로 변환 -->
<span class="md">
	<?php echo $view_content; ?> 
</span>

<!-- md요소 HTML 변환 -->
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/js/"?>marked.min.js"></script>
<script> 
         $(document).ready(function(){
		$(".md").each(function(){
			$(this).html(marked.parse($(this).html()));             // html() make > to &gt;   text() keep >
		});
	  	// adding note for ```input1  ```output1 in description

            // 샘플 입력 및 출력을 추가
	        for(let i=1;i<10;i++){
                        $(".language-input"+i).parent().before("<div><?php echo $MSG_Sample_Input?>"+i+":</div>");
                        $(".language-output"+i).parent().before("<div><?php echo $MSG_Sample_Output?>"+i+":</div>");
                }

	       
        $(".md table tr td").css({
            "border": "1px solid grey",
            "text-align": "center",
            "width": "200px",
            "height": "30px"
        });

        $(".md table th").css({
            "border": "1px solid grey",
            "width": "200px",
            "height": "30px",
            "background-color": "#9e9e9ea1",
            "text-align": "center"
        });
  });
</script>
</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
