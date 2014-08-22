var resetSelectIds = new Array ('');
var resetCheckboxNames = new Array('years[]','blocks[]','pbls[]','types[]');
var resetInputIds = new Array('');


$(document).ready(function(){
	$('#evaluateSearch #evaluateSubmitContainer #reset').click(function(){
		resetEvaluationSearch();
	});
	$('#evaluateSearch input').click(function(){
		var inputName = $(this).attr('name');
		var process = allowAnyProcess(inputName);
		if(process) {
			onchangeCheckboxes(inputName, $(this).val());
		}
	});
});

function allowAnyProcess(inputName) {
	if(resetCheckboxNames.length > 0) {
		for(var i=0; i<resetCheckboxNames.length; i++) {
			if(resetCheckboxNames[i] == inputName) {
				return true;
			}
		}
	}
	return false;
}

function onchangeCheckboxes(name,valSelected) {
	if(valSelected == '') {
		$("input[@name='" + name + "']").each(function(){
			if($(this).val() != '') {
				$(this).attr('checked', false);
			} else {
				$(this).attr('checked', true);
			}
		});
	} else {
		var isAnythingChecked = false;
		$("input[@name='" + name + "']").each(function(){
			if($(this).attr('checked') == true) {
				isAnythingChecked = true;
			}
			if($(this).val() == '') {
				$(this).attr('checked', false);
			}
		});
		if(isAnythingChecked == false) {
			$("input[@name='" + name + "']").each(function(){
				if($(this).val() == '') {
					$(this).attr('checked', true);
				}
			});
		}
	}
}

function resetEvaluationSearch() {
	resetInput();
	resetSelect();
	resetCheckboxes();
}

function resetInput() {
	if(resetInputIds.length > 0) {
		for(var i=0; i<resetInputIds.length; i++) {
			$('#' + resetInputIds[i]).val('');
		}
	}
}

function resetSelect() {
	if(resetSelectIds.length > 0) {
		for(var i=0; i<resetSelectIds.length; i++) {
			$("select#" + resetSelectIds[i] + " option:first").attr('selected', true);
		}
	}
}

function resetCheckboxes() {
	if(resetCheckboxNames.length > 0) {
		for(var i=0; i<resetCheckboxNames.length; i++) {
			$("input[@name='" + resetCheckboxNames[i] + "']").each(function() {
				if($(this).attr('checked') == true) {
					$(this).attr('checked',false);
				}
				if($(this).val() == '') {
					$(this).attr('checked',true);
				}
			});
		}
	}
}


