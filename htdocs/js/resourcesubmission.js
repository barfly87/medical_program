var RESOURCE_SUBMISSION_DEBUG = true;
/* ************************** FANCY BOX ***************************** */

$(document).ready(function(){
	createFancyBox('a.resource_submission');
	if(typeof(resourcesToDisplay) !== 'undefined') {
		displayResources(resourcesToDisplay);
	}
});

function createFancyBox(elem){
	$(elem).fancybox({
		'easingIn': 3000, 
		'easingOut': 3000,
		'overlayShow' : true,
		'overlayOpacity' : 0.7,
		'hideOnContentClick':false,
		'frameWidth' : 800,
		'frameHeight' : 400
	});
	
}

function createEditFancyBox(){
	createFancyBox('a.resourceButtonHref');
	createFancyBox('a.resource_view');
}

/* *************** Mids & ResourceCount functionality *********** */

loResourceCount = 0;
taResourceCount = 0;
loMids = {};
taMids = {};

function setMids(type, div, mid){
	if(type == 'lo') {
		loMids[div] = mid;
	} else {
		taMids[div] = mid;
	}
}

function getMids(type){
	if(type == 'lo') {
		return loMids;
	} else {
		return taMids;
	}
}

function getResourceCount(type){
	if(type == 'lo') {
		return loResourceCount;
	} else {
		return taResourceCount;
	}
}

function setResourceCount(type, count){
	if(type == 'lo') {
		loResourceCount = count;
	} else {
		taResourceCount = count;
	}
}

/* ************************** ADD ***************************** */
function addNewResource(type,mid,resourceTitle,editable,resourcetypeid){
	var count 				= getResourceCount(type);
	var divCount 			= ++count;
	setMids(type, divCount, mid);
	var id 				= type + '_resourceContainer_' + divCount;
	var imageSrc 		= getImageSrc(mid);
	var resourceHtml 	= getResourceHtml(id, mid, type, divCount, imageSrc, resourceTitle,editable,resourcetypeid);
	
    setResourceCount(type,divCount);
    $('div.' + type + '_resourceUploaded').append(resourceHtml);
    createEditFancyBox();
}

function getImageSrc(mid){
	return BASE_URL + '/resource/image?size=128&mid=' + mid;	
}

function getResourceHtml(id, mid, type, divCount, imageSrc, resourceTitle,editable,resourcetypeid){
	var editHtml = '';
	if(editable == 'yes') {
		var editHref = getEditHref(divCount,type,mid,resourcetypeid);
		editHtml = getEditHtml(editHref);                   
	}
	var viewHref = getViewHref(mid,type,divCount);
	var viewHtml = getViewHtml(viewHref, imageSrc, resourceTitle);
	var removeHref = getRemoveHref(id,divCount,type) ;
	var removeHtml = getRemoveHtml(removeHref);
	var inputMids = getInputMids(type,mid,resourcetypeid);
	var resourceHtml = createResourceHtml(id, viewHtml, editHtml, removeHtml, inputMids);
	return resourceHtml;
}

function createResourceHtml(id, viewHtml,editHtml,removeHtml,inputMids){
	var resourceHtml = 
		'<div style="height: auto;width:350px;" id="'+ id +'">' +
		'	<div style="background-color: rgb(255, 249, 239);" class="resourceElem">' +                        
				viewHtml +
        '		<div class="resourceActionContainer">' +
        		editHtml +
        		removeHtml +
        '		</div>' +
        '	</div>' +
        	inputMids +                        
        '</div>';
	return resourceHtml;
}

//Input Mids Html
function getInputMids(type,mid,resourcetypeid) {
	try {	
		var inputMids = '<input type="hidden" name="'+ type +'_mids[]" value="' + mid + '|' + resourcetypeid + '" />';
		return inputMids;
	} catch(err) {resoureSubmissionDebug(err); return '';}	
}

//Remove Html
function getRemoveHref(id,divCount,type){
	try {	
		var removeHref = 'javascript:removeResourceHtml(\''+ id +'\','+ divCount + ',\''+ type +'\');';
		return removeHref;
	} catch(err) {resoureSubmissionDebug(err); return '';}	
}

function getRemoveHtml(removeHref){
	try {	
		var removeHtml =         
			'<div class="resourceButtonContainer">' +
		    '	<a class="resourceRemoveButtonHref" href="' + removeHref + '">Remove</a>' +
		    '</div>';
		return removeHtml;
	} catch(err) {resoureSubmissionDebug(err); return '<div class="error">Error</div>';}		
}

//View Html
function getViewHref(mid,type,divCount){
	try {	
		var viewHref = BASE_URL + '/resource/view/type/' + type + '/div/'+divCount+'/id/new?mid='+ mid;
		return viewHref;
	} catch(err) {resoureSubmissionDebug(err); return '';}	
}

function getViewHtml(viewHref, imageSrc, resourceTitle){
	try {	
		var viewHtml =
		    '<div class="resourceImgContainer">' +
		    '	<a class="resource_view iframe" href="'+ viewHref +'"><img src="' + imageSrc +'" alt=""></a>' +
		    '	<p class="resourceTitleContainer"><a class="resource_view iframe" href="'+ viewHref +'">' + resourceTitle + '</a></p>' +                        
		    '</div>';
		return viewHtml;
	} catch(err) {resoureSubmissionDebug(err); return '';}	
}

//Edit Html
function getEditHref(divCount,type,mid,resourcetypeid){
	try {	
		var editHref = BASE_URL + '/resource/edit/id/new/div/' + divCount + '/type/' + type + '/resourcetypeid/'+resourcetypeid+'?mid='+mid;
		return editHref;
	} catch(err) {resoureSubmissionDebug(err); return '';}		
}

function getEditHtml(editHref) {
	try {	
		var editHtml =                        
			'<div class="resourceButtonContainer">' +
	        '	<a class="resourceButtonHref iframe" href="'+editHref+'">Edit</a>' +
	        '</div>' ;
		return editHtml;
	} catch(err) {resoureSubmissionDebug(err); return '<div class="error">Error</div>';}	
}

/* ************************** UPDATE ***************************** */

function updateResourceHtmlForEditAction(type,div,mid,title,resourcetypeid) {
	try {	
		var hrefObj = $('#' + type + '_resourceContainer_' + div + ' a.resourceButtonHref');
		var href = hrefObj.attr('href');
		href = href.replace(/\/resourcetypeid\/[0-9]+\?/,'/resourcetypeid/'+resourcetypeid+'?');
		hrefObj.attr('href',href);
		$('#' + type + '_resourceContainer_' + div + ' .resourceTitleContainer a').html(title);
		var imageSrc = getImageSrc(mid)+'&'+ Math.random();
		$('#' + type + '_resourceContainer_' + div + ' .resourceImgContainer img').each(function(){
			$(this).attr('src',imageSrc);
		});
	} catch(err) {resoureSubmissionDebug(err);}	
}

/* ************************** REMOVE ***************************** */

function removeNewResource(type,mid){
	try {	
		existingMids = getMids(type);
		for ( var div in existingMids ) {
			if(existingMids[div] == mid) {
				id = type +'_resourceContainer_' + div;
				removeResourceHtml(id,div,type);
				break;
			}
		}
	} catch(err) {resoureSubmissionDebug(err);}	
}

function removeResourceHtml(id,div,type){
	try {	
		if(type == 'lo') {
			delete loMids[div];
		} else if (type == 'ta') {
			delete taMids[div];
		}
	    $('#'+id).remove();
	} catch(err) {resoureSubmissionDebug(err);}    
}

/* ************************** DISPLAY RESOURCES ***************************** */

function displayResources(data) {
	try {	
		var url = BASE_URL + '/resource/gettypedetail';
		$.getJSON(url, data, function(resrc){
			for(var i=0; i<resrc.length; i++) {
				addNewResource(resrc[i]['type'],resrc[i]['mid'],resrc[i]['resourceTitle'],resrc[i]['editable']);
			}
		});
	} catch(err) {resoureSubmissionDebug(err);}	
}

/* ************************* Debug ************************* */
function resoureSubmissionDebug(err) {
	if(RESOURCE_SUBMISSION_DEBUG == true) {
		try {
			alert(err);
		} catch(error) {
			
		}
	}
}

