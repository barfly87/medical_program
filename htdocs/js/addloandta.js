$(document).ready(function() {
	$("#graphicalview div.enabled").css('cursor', 'pointer');
	$(".buttonscontainer span").hover(buttonover, buttonout);
	$("#newlolink").click(function(){
		$("#graphicalview").hide();
		$("#newlo").show();
	});
	
	$("#cancelnewlo").click(function(){
		$("#graphicalview").show();
		$("#newlo").hide();
	});
	
	$("#existinglolink").click(function(){
		$("#graphicalview").hide();
		$("#existinglo").show();
	});
	
	$("#cancelexistinglo").click(function(){
		$("#graphicalview").show();
		$("#existinglo").hide();
	});

	$("#newtalink").click(function(){
		$("#graphicalview").hide();
		$("#newta").show();
	});
	
	$("#cancelnewta").click(function(){
		$("#graphicalview").show();
		$("#newta").hide();
	});

	$("#existingtalink").click(function(){
		$("#graphicalview").hide();
		$("#existingta").show();
	});
	
	$("#cancelexistingta").click(function(){ 
		$("#graphicalview").show();
		$("#existingta").hide();
	});

	$("#strengthlink").click(function(){
		$("#graphicalview").hide();
		$("#strength").show();
	});

	$("#cancellink").click(function(){
		$("#graphicalview").show();
		$("#strength").hide();
	});
});