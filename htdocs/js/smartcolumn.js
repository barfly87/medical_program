function smartColumns(elemWidth) {
	$("ul.column").css({ 'width' : "100%"});
	
	var colWrap = $("ul.column").width();
	var colNum = Math.floor(colWrap / elemWidth);
	var colFixed = Math.floor(colWrap / colNum);
	
	$("ul.column").css({ 'width' : colWrap});
	$("ul.column li").css({ 'width' : colFixed});
}