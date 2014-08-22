
function listOfDisciplines(arrayOfIds) {
	
	if(arrayOfIds instanceof Array) {
		
        space = '&#160;&#160;';
        
		for(var i=0; i < arrayOfIds.length; i++) {
			
	        $("select[@id='"+arrayOfIds[i]+"'] option").each(function(i){        
	            var option = $(this).text();
	            if(option.length != 0) {
	                if(option.indexOf('/') == -1) {
	                    $(this).attr('class', 'headings');
	                } else if(option.length != 0) {
	                    var splitOption = option.split("/");
	                    count = splitOption.length -1 ;
	                    for(var x=0; x<count; x++) {
	                        option = space + option;
	                    }      
	                    $(this).html(option);
	                    $(this).attr('class', 'subHeadings');
	                }
	            }
	        });  
	        
        } // end of for
        
	} else {
		return false;
	}// end of if
	
} // end 

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