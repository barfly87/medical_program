var search = new Object();
var resetSelectIds = new Array ('qoption','block','blockweek','pbl','theme');
var resetCheckboxNames = new Array('discipline[]','stage[]','acttype[]');
var resetInputIds = new Array('qstr');

search['simple'] = new Object();
search['simple']['typeName']  = 'simple';
search['simple']['linkText'] = 'Simple Search';
search['simple']['heading'] = 'Search';

search['advanced'] = new Object();
search['advanced']['typeName'] = 'advanced';
search['advanced']['linkText'] = 'Advanced Search';
search['advanced']['heading'] = 'Advanced Search';

$(document).ready(function(){
    $('#searchType').click(function(){
    	linkText = $(this).text();
    	if(linkText == search['advanced']['linkText']) {
    		processAdvancedSearch(1000);
    	} else {
    		processSimpleSearch(1000);
   	    }
    });
    $('#searchType').mouseover(function(){
        $(this).attr('style','cursor:pointer');
    }); 
    $('#luceneHelp').click(function(){
    	luceneHelp();
    });
});

function processSimpleSearch(fadeInVal){
	$('#searchType').text(search['advanced']['linkText']); 
	$('#title').hide();
	$('#title').text(search['simple']['heading']);
	$('#title').fadeIn(fadeInVal);
	$('#process').attr('value',search['simple']['typeName']);
	var rowCount = $('#searchTable tr').length;
	//In jQuery row first row index is '0' 
	var rowsToBeShown = new Array(0,1, rowCount-2, rowCount-1);
	
    $('#searchTable tr').each(function() {
        var index = this.rowIndex;
        var hide = true;
        for(var i=0; i< rowsToBeShown.length; i++) {
            if(rowsToBeShown[i] == index) {
                hide = false;
                break;
            }
        }
        if(hide == true) {
            $(this).hide();
        } else {
            $(this).fadeIn(fadeInVal);
        }
    });     
}

function processAdvancedSearch(fadeInVal) {
    $('#searchType').text(search['simple']['linkText']);
    $('#title').hide();
    $('#title').text(search['advanced']['heading']);
    $('#title').fadeIn(fadeInVal);
    $('#process').attr('value',search['advanced']['typeName']);
    $('#searchTable tr').each(function() {
        $(this).fadeIn(fadeInVal);
    });     
}

function luceneHelp() {
	window.open(BASE_URL + '/help/search', 'myobjectivewindow','resizable=1,scrollbars=1,menubar=1,toolbar=0,locationbar=0,left=0,top=0,width=780,height=658',false);
}

function resetSearch() {
	resetInput();
	resetSelect();
	resetCheckboxes();
}

function resetInput() {
	for(var i=0; i<resetInputIds.length; i++) {
		$('#' + resetInputIds[i]).val('');
	}
}

function resetSelect() {
	for(var i=0; i<resetSelectIds.length; i++) {
		$("select#" + resetSelectIds[i] + " option:first").attr('selected', true);
	}
}

function resetCheckboxes() {
	for(var i=0; i<resetCheckboxNames.length; i++) {
		$("input[@name='" + resetCheckboxNames[i] + "']").each(function() {
			if($(this).attr('checked') == true) {
				$(this).attr('checked',false);
			}
			if($(this).val() == 'Any') {
				$(this).attr('checked',true);
			}
		});
	}
}

$(document).ready(function(){
	$('input[@name="domain[]"]').click(function(){
		valSelected = $(this).val();
		if(valSelected == 'Any') {
			$("input[@name='domain[]']").each(function(){
				if($(this).val() != 'Any') {
					$(this).attr('checked', false);
				} else {
					$(this).attr('checked', true);
				}
			});
		} else {
			var isAnythingChecked = false;
			$("input[@name='domain[]']").each(function(){
				if($(this).attr('checked') == true) {
					isAnythingChecked = true;
				}
				if($(this).val() == 'Any') {
					$(this).attr('checked', false);
				}
			});
			if(isAnythingChecked == false) {
				$("input[@name='domain[]']").each(function(){
					if($(this).val() == 'Any') {
						$(this).attr('checked', true);
					}
				});
			}
		}
	});
	
});

$(document).ready(function(){
	$('input[@name="stage[]"]').click(function(){
		valSelected = $(this).val();
		if(valSelected == 'Any') {
			$("input[@name='stage[]']").each(function(){
				if($(this).val() != 'Any') {
					$(this).attr('checked', false);
				} else {
					$(this).attr('checked', true);
				}
			});
		} else {
			var isAnythingChecked = false;
			$("input[@name='stage[]']").each(function(){
				if($(this).attr('checked') == true) {
					isAnythingChecked = true;
				}
				if($(this).val() == 'Any') {
					$(this).attr('checked', false);
				}
			});
			if(isAnythingChecked == false) {
				$("input[@name='stage[]']").each(function(){
					if($(this).val() == 'Any') {
						$(this).attr('checked', true);
					}
				});
			}
		}
	});
	
});