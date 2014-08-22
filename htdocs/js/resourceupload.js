$(document).ready(function(){
	$('#showHideError').click(function(){
		$('#exception').toggle("slow");
		val = $('#showHideError').html();
		if(val == 'Show Error') {
			$('#showHideError').html('Hide Error');
		} else {
			$('#showHideError').html('Show Error');
		}
	});
});

function createResourceHtmlForAddAction(type, title, mid, editable,resourcetypeid) {
	parent.addNewResource(type,mid,title,editable,resourcetypeid);
}

function updateResourceHtmlForEditAction(type,div,mid,title,resourcetypeid) {
	parent.updateResourceHtmlForEditAction(type,div,mid,title,resourcetypeid);
}
