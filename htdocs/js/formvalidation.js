function step2_validator() {
    $('#name_error').remove();

    var result = true;
    var errorStr = "<div id='name_error' style='color:red'><p>";
    if ($(':radio[name=lotype]:checked').val() == 'newlo') {
	    if (!($('#discipline1 :selected').text())) {
	        errorStr += "You must select a main discipline.<br/>";
		    result = false;
	    }
	    if (!($('#theme1 :selected').text())) {
	        errorStr += "You must select a theme.<br/>";
	        result = false;
	    }
	    if (!($('#system :selected').text())) {
	        errorStr += "You must select a system.<br/>";
	        result = false;
	    }
	    if ($.trim($('#lo').val()) == '') {
	        errorStr += "You must enter a learning objective.<br/>";
	        result = false;
	    }
	    if ($(':checkbox:checked[name*=assesstype]').size() == 0) {
	        errorStr += "You must select at least one assessment method.<br/>";
	        result = false;
	    }
    } else {
    	if ($.trim($('#lo_id').val()) == '') {
            errorStr += "You must enter a learning objective id.<br/>";
            result = false;
        } else if (!($.trim($('#lo_id').val()).match(/^\d+$/))) {
    		errorStr += "You must enter a valid learning objective id.<br/>";
            result = false;
    	}
    }
    errorStr += "</p></div>";
    if (!result)
        $('#step2').prepend(errorStr);
    return result;
}

function step5_validator() {
    $('#name_error').remove();

    var result = true;
    var errorStr = "<div id='name_error' style='color:red'><p>";
    if ($(':radio[name=tatype]:checked').val() == 'newta') {
	    if ($.trim($('#name').val()) == '') {
	        errorStr += "You must enter a teaching activity title.<br/>";
	        result = false;
	    }
	    if (!($('#type :selected').text())) {
	        errorStr += "You must select a type.<br/>";
	        result = false;
	    }
	    if (!($('#cohort :selected').text())) {
	        errorStr += "You must select a cohort.<br/>";
	        result = false;
	    }
	    if (!($('#stage :selected').text())) {
	        errorStr += "You must select a stage.<br/>";
	        result = false;
	    }
	    if (!($('#block :selected').text())) {
	        errorStr += "You must select a block.<br/>";
	        result = false;
	    }
    } else {
    	if ($.trim($('#ta_id').val()) == '') {
            errorStr += "You must enter a teaching activity id.<br/>";
            result = false;
        } else if (!($.trim($('#ta_id').val()).match(/^\d+$/))) {
    		errorStr += "You must enter a valid teaching activity id.<br/>";
            result = false;
    	}
    }
    errorStr += "</p></div>";
    if (!result)
        $('#step5').prepend(errorStr);
    return result;
}