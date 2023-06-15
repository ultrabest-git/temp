<script language="JavaScript" type="text/javascript">
$('tr').click(function(){	
					var buf = $(this).children('td').find('.glyphicon');
					if(buf.hasClass('glyphicon-plus'))
					{
						buf.removeClass('glyphicon glyphicon-plus');
						buf.addClass('glyphicon glyphicon-minus');
					}
					else
					{
						buf.removeClass('glyphicon glyphicon-minus');
						buf.addClass('glyphicon glyphicon-plus');
					}
});
$('#excel').on('click', function (e) {
		$.ajax({
				url: '/local/php/xls.php',
				method: 'post',
				dataType: 'json',
				data: {'data': <?php echo json_encode($arResult)?>,'params':<?php echo json_encode($arParams['COURSE_ID'])?>},
				success: function(file){
					var link = document.createElement('a');
					link.setAttribute('href', file);
					link.setAttribute('download', 'Отчет');
					link.click();
					return false;
				}
			   });	
	
});

function getElementsByClass(searchClass, node, tag)
	{
		var classElements = new Array();
 
		if (node == null)
			node = document;
 
		if (tag == null)
			tag = '*';
 
		var els = node.getElementsByTagName(tag);
		var elsLen = els.length - 1;
		var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
 
		for (i = 0, j = 0; i <= elsLen; i++)
			if (pattern.test(els[i].className))
				{
					classElements[j] = els[i];
					j++;
				}
 
		return classElements;
	}
 
	function OpenClose(CName,clases)
	{
		var Elements = getElementsByClass(CName, document, "tr");
		var ElementsLength = Elements.length - 1;
		for (i = 0; i <= ElementsLength; i++)
			if (Elements[i].style.display == "")
				{
					Elements[i].style.display = "none";

				}
			else
				{
					Elements[i].style.display = "";
				}
	}
</script>
