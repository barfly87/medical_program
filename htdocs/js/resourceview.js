$(document).ready(function(){
	$('#linkContentLo .linkElement:even').attr('style','background-color: #FFF9EF;');
	$('#linkContentTa .linkElement:even').attr('style','background-color: #FFF9EF;');
});

function addResource(type,typeName, id,mid,resourceTypeId){
	addUrl = BASE_URL + '/resource/add/id/' + id + '/type/' + type + '/resourcetypeid/' + resourceTypeId +'?mid=' + mid;
	
    $.ajax({
        url: addUrl,
        async: false,
        type: "GET",
        success: function(data) {
			result = data;
			if(result != 'success') {
				showAlert('addError');
			} else {
				window.location = BASE_URL + '/'+typeName+'/view/id/'+id;				
			}
        }
    });     
	
	
}

var defaultPrepend = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;">';
var defaultAppend = '</span>';
var prependRed = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;color:red;">';


function showAlert(action) {
	try {	
		$('div.jqi').css('width','200px');
		var txt = '';
		var alive = 1200;
		switch(action) {
			case 'addError':
				txt = prependRed + 'Error !' + defaultAppend + '<span style="font-size:110%;">Resource could not be Added</span>';
				alive = 4000;
				break;
			default: 
				return;
		}
	    $.prompt(txt,{timeout : alive,opacity:0.7,overlayspeed:'fast', buttons:{}});
	} catch(err) {resoureDebug(err);}    
}
