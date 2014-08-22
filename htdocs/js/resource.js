var RESOURCE_DEBUG = true;
var defaultPrepend = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;">';
var defaultAppend = '</span>';
var prependRed = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;color:red;">';

/* ************************* Add ************************* */

function addResource(type,id,resourcetypeid,editable,mid,tdId){
	try {	
		if(id == 'new') {
			addNewResource(type,mid,tdId,editable,resourcetypeid);
			changeCssAddResource(tdId);
		} else {
			addUrl = BASE_URL + '/resource/add/id/' + id + '/type/' + type + '/resourcetypeid/'+resourcetypeid+'?mid=' + mid;
			var result = 'fail';
	        $.ajax({
	            url: addUrl,
	            async: false,
	            type: "GET",
	            success: function(data) {
					result = data;
					if(result != 'success') {
						showAlert('addError');
						return;
					} else {
						changeCssAddResource(tdId);
						showAlert('add');
					}
	            }
	        });     
		}
	} catch(err) {resoureDebug(err);}	
}

function showAlert(action) {
	try {	
		$('div.jqi').css('width','200px');
		var txt = '';
		var alive = 1200;
		switch(action) {
			case 'add': 	
				txt = defaultPrepend + 'Resource Added' + defaultAppend;
				break;
			case 'remove':	
				txt = prependRed + 'Resource Removed' + defaultAppend;
				break;
			case 'addError':
				txt = prependRed + 'Error !' + defaultAppend + '<span style="font-size:110%;">Resource could not be Added</span>';
				alive = 4000;
				break;
			case 'removeError':
				txt = prependRed + 'Error !' + defaultAppend + '<span style="font-size:110%;">Resource could not be Removed</span>';
				alive = 4000;
				break;
			default: 
				return;
		}
	    $.prompt(txt,{timeout : alive,opacity:0.7,overlayspeed:'fast', buttons:{}});
	} catch(err) {resoureDebug(err);}    
}

function changeCssAddResource(tdId) {
	try {	
	    arOldHref = $('#addResource_' + tdId + ' a').attr('href');
	    arNewHref = arOldHref.replace('addResource','removeResource');
		
	    $('#resource_' + tdId).css('background-color','#EFEFEF');
	    $('#addResource_' + tdId + ' a').attr('href',arNewHref);
	    $('#addResource_' + tdId + ' a').css('color','#DF6F6F');
	    $('#addResource_' + tdId + ' a').css('background-color','#3F3F3F');
	    $('#addResource_' + tdId + ' a').html('Remove');
	} catch(err) {resoureDebug(err);}    
}
function addNewResource(type,mid,tdId,editable,resourcetypeid){
	try {	
		var resourceTitle	= getResourceTitle(tdId);
		parent.addNewResource(type,mid,resourceTitle,editable,resourcetypeid);
	} catch(err) {resoureDebug(err);}
}

function getResourceTitle(tdId){
	try {	
		return $('#resource_'+tdId+' .content a:eq(1)').html();
	} catch(err) {resoureDebug(err);return '';}
}

/* ************************* Remove ************************* */

function removeResource(type,id,resourcetypeid,editable,mid,tdId){
	try {	
		if(id == 'new') {
			removeNewResource(type,mid);
			changeCssRemoveResource(tdId);
		} else {
		    removeUrl = BASE_URL + '/resource/remove/id/' + id + '/type/' + type + '?mid=' + mid;
		    var result = 'fail';
	        $.ajax({
	            url: removeUrl,
	            async: false,
	            type: "GET",
	            success: function(data) {
			        result = data;
					if(result != 'success') {
						showAlert('removeError');
						return;
					} else {
						changeCssRemoveResource(tdId);
						showAlert('remove');
					}
	        	}
	        });     
		    
		}
	} catch(err) {resoureDebug(err);} 	
}

function removeNewResource(type,mid){
	try{	
		parent.removeNewResource(type,mid);
	} catch(err) {resoureDebug(err);} 
}

function changeCssRemoveResource(tdId) {
	try {	
	    arOldHref = $('#addResource_' + tdId + ' a').attr('href');
	    arNewHref = arOldHref.replace('removeResource','addResource');
		
	    $('#resource_' + tdId).css('background-color','#FFFFFF');
	    $('#addResource_' + tdId + ' a').attr('href',arNewHref);
	    $('#addResource_' + tdId + ' a').css('background-color','#EFE3D1');
	    $('#addResource_' + tdId + ' a').css('color','#322006');
	    $('#addResource_' + tdId + ' a').html('Add');
	} catch(err) {resoureDebug(err);}     
}

/* ************************* Change UI for existing mids ************************* */

function changeUIForExistingMids(type){
	try {	
		mids = parent.getMids(type);
		len = 0;
		for(var x in mids){
			++len;
			break;
		}
		if(len > 0) {
			$('.hiddenMid').each(function() {
				mid  = $(this).html();
				for(var i in mids) {
					if(mids[i] == mid) {
						tdId = $(this).attr('id').replace('td_','');
					    changeCssAddResource(tdId);
					}
				}
			});
		}	
	} catch(err) {resoureDebug(err);}  	
}

/* ************************* Debug ************************* */
function resoureDebug(err) {
	if(RESOURCE_DEBUG == true) {
		try {
			alert(err);
		} catch(error) {
			
		}
	}
}