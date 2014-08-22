$(document).ready(function(){
	if($(":checkbox") != null) {
	   $(":checkbox").css({background:"none", border:"none"});
	}
	if($(":radio") != null) {
	   $(":radio").css({background:"none", border:"none"});
	}
});

var unBindArrowKeysFor = new Array('#qstr');

function showPblBlockResource(mid) {
    $('#content-' + mid).toggle();
    $('#container-' + mid).toggleClass('print').toggleClass('noPrint');
}

//You can reset whole form
//$('form').clearForm()
//OR
//Reset individual type of element
//$('#someInputId').clearForm()
try {
    $.fn.resetForm = function() {
        return this.each(function() {
            var type = this.type;
            var tag = this.tagName.toLowerCase();
            if (tag == 'form') {
                return $(':input',this).resetForm();
            }
            if (type == 'text' || type == 'password' || tag == 'textarea') {
                this.value = '';
            } else if (type == 'checkbox' || type == 'radio') {
                this.checked = false;
            } else if (tag == 'select') {
                this.selectedIndex = 0;
            }
        });
    };
} catch(e) {
    
}