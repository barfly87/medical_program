$(document).ready(function() {
	try {
	    $("#curriculumAreaIds").tableDnD({
	    	onDragClass: 'curriculumAreaDrag',
	    	onDragStart: function(table, row) {
	    		$('#sortMsg').html('Sorting in progess.');
	    	},
		    onDrop: function(table, row) {
	    		var curriculumAreaIds = $.tableDnD.serialize(); 
	    		var url = BASE_URL + '/disc/curriculumareas/sort/disc_id/'+ discId + '?' + curriculumAreaIds;
	    		$.get(url, function(data){
	    			if(data == 'successful') {
			            var order = 1;
			            $('#curriculumAreaIds tr').each(function(){
			            	if($(this).attr('id') != '') {
			            		$(this).find("td:first").html(order);
			            		order++;
			            	}
			            });
			            $('#sortMsg').html('Sorting was successful.');
	    			} else {
	    				$('#sortMsg').html('<span class="red">Error ! Sorting was not successful.</span>');
	    			}
	    		});
		    }
	    });
	} catch (err){
		debugCurriculumAreas(err);
	}
});

function curriculumAreaEdit(editId){
	try {
		$('#sortMsg').hide();
	    $('#curriculumAreaEditTitle').show();
	    $('#curriculumAreaTitle').hide();
	    $('#curriculumAreaList').hide();
	    $('#curriculumAreaHiddenEdit div').each(function(){
	        val = $(this).attr('id');
	        if(val == editId) {
	            $(this).show();
	        } else {
	            $(this).hide();
	        }
	    });  
	} catch(err) {
		debugCurriculumAreas(err);
	}
}

function debugCurriculumAreas(err) {
	var debug = true;
	if(debug) {
		alert(err);
	}
} 