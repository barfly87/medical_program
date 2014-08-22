/*
$(document).ready(function(){
	// Link Activities/Objectives 
    $("#linkContent:last-child").css('margin-bottom','0px');
    changeBgColor();
    adjustHeight();
    makeResourceSortable();
});
*/
var defaultPrepend = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:115%;">';
var prependRed = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;color:red;">';
var prependSort = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;">';
var prependResourceError = '<span style="margin:10px;margin-left:0px;font-weight:bold; font-size:115%;">';
var defaultAppend = '</span>';

function removeLink(loId,taId,context,linkElement) {
	var id = '';
	
	var typeTxt = '';
	if(context == 'lo') {
		id = taId;
		typeTxt = 'linked teaching activity';
	} else {
		id = loId;
		typeTxt = 'linked learning objective';
	}
	var txt = defaultPrepend + 'Are you sure you want to delete '+ typeTxt + ' ' + id + ' ? '+ defaultAppend + '<br />' + defaultPrepend + defaultAppend;
    $.prompt(
        txt,
            { 
            buttons:{Delete:true, Cancel:false},
            callback: function(v){
            	if(v) {
            		var url = BASE_URL + '/lotalinkage/delete';
            	    $.get(url, { lo_id: loId, ta_id: taId },function(data){
            	    	result = data;
            	    	if (data == 'success') {
            	    		removeAlert('Deleted ' + typeTxt);
            	    		$("#"+linkElement).remove();
            	    		changeBgColor();
            	    	} else {
            	    		$.prompt(prependRed + data + defaultAppend);
            	    	}
            	    });
            	} 
        }
    });
    
	
}
function removeAlert(text) {	
	var txt = prependRed + text + defaultAppend;
	$.prompt(txt,{timeout : 1200,opacity:0.7,overlayspeed:'fast', buttons:{}});
}

function sortAlert() {
	var txt = prependSort + 'Please drag up or down to sort.' + defaultAppend;
	$.prompt(txt,{timeout : 2500,opacity:0.7,overlayspeed:'fast', buttons:{}});
}

function resourceError(mid) {
	var txt = prependRed + 'Resource Id :' + defaultAppend + prependResourceError + mid + defaultAppend;
	$.prompt(txt,{opacity:0.7,overlayspeed:'fast', buttons:{},persistent:false});
}

function changeBgColor(){
    $(".linkElement:even").css('background-color','#FFF9EF');
    $(".resourceElem:even").css('background-color','#FFF9EF');
    $(".linkElement:odd").css('background-color','#FFECCF');
    $(".resourceElem:odd").css('background-color','#FFECCF');
}

function makeResourceSortable() {
    $("#resourceContainer").sortable(
      {
    	  opacity: 0.3,
    	  axis: 'y',
    	  handle: 'div.resourceSortButtonContainer',
          stop:function(event,ui){
	          changeBgColor();
	          processSorting();
	      }
      }
      );
}

function processSorting() {
	var data = new Array();
	var count = 1;
	$('#resourceContainer > div').each(function(){
		var split = $(this).attr('id').split('_');
		if(split[1] != undefined) {
			data.push(split[1] + '_' + count);
			count++;
		}
	});
	$.get(BASE_URL + '/resource/sort', {'data[]': data});
}

function adjustHeight() {
	var noOfFoundResources = getNoOfFoundResources();
    switch(noOfFoundResources){
        case 0:
            $("#resourceContainer").attr('style','height: auto');
            var noOfResources = getNoOfResources();
            if(noOfResources == 0) {
            	$("#resourceContainer").append('<p class="linkNone">None Attached</p>');
            }
        break;
        case 1:
            $("#resourceContainer").attr('style','height: auto');
        break;
        case 2:
            $("#resourceContainer").attr('style','height: auto');
        break;
        case 3:
            $("#resourceContainer").attr('style','height: auto');
        break;
        default:
            $("#resourceContainer").attr('style','height: 470px;overflow:auto;');
        break;
    }
}

function getNoOfResources() {
    var len = 0;
    $("#resourceContainer div.resourceElem").each(function(){
        len++;      
    });
    return len;
}

function getNoOfFoundResources(){
    var len = 0;
    $("#resourceContainer div.resourceElem div.resourceImgContainer").each(function(){
        len++;      
    });
    return len;
}

function removeResource(type,id,mid,tdId) {
	var txt = defaultPrepend + 'Are you sure you want to remove this resource ?' + defaultAppend;

    $.prompt(
            txt,
                { 
                buttons:{Remove:true, Cancel:false},
                callback: function(v){
                	if(v) {
                		$('#resourceElem_' + tdId).hide();
                		var url = BASE_URL + '/resource/remove/id/' + id + '/type/' + type + '?mid=' + mid;
                	    $.get(url, function(data){
                	    	if(data == 'fail') {
                	    		$.prompt(defaultPrepend + 'Error occured while removing this resources.'+ defaultAppend);
                	    		$('#resourceElem_' + tdId).show();
                	    	} else {
                	    	    //$('#resourceElem_' + tdId).hide();//remove();
                	    	    //adjustHeight();
                	    	    //changeBgColor();
                	    		removeAlert('Resource removed.');
                	    	}
                	    });
                	} 
                }
                });
}

/*
 * START OF SHOW HIDE ROWS FOR TABLE
 */
function showHideRows(tableClassOrId, rowStart) {
	try {
		var showHideId  = 'showHideTableRows';
		
		//Hide table rows greater than row no 'rowStart'
		var trGt 		= $(tableClassOrId + ' > tbody > tr:gt(' + rowStart + ')');
		trGt.hide();
		
		//Create a row for show/hide link html on fly after row no 'rowStart'; 
		var trLast 		= $(tableClassOrId + ' > tbody > tr:last');
		var tds 		= countTds(trLast);
		var trEq 		= $(tableClassOrId + ' > tbody > tr:eq(' + rowStart + ')');
		createShowHideRow(tds,showHideId,trEq);
		
		//Set the onclick show/hide functionality to toggle rows
		//Since you have added show/hide row on fly you need to start toggle one row after that.
		var newRowStart = ++rowStart;
		var newTrGt 	= $(tableClassOrId + ' > tbody > tr:gt(' + newRowStart + ')');
		toggleShowHideRows(showHideId, newTrGt);
		
	} catch (err) {
		debug(err);
	}
}

function toggleShowHideRows(showHideId, newTrGt) {
	$('#' + showHideId).click(function(){
		var anchorText = $(this).html();
		if(anchorText.indexOf('View') == 0) {
			anchorText = anchorText.replace('View','Hide');
		} else {
			anchorText = anchorText.replace('Hide','View');
		}
		$(this).html(anchorText);
		newTrGt.toggle();	
	});
}

function createShowHideRow(tds, showHideId, trEq) {
	var showHideText = '<tr><td colspan="' + tds + '" style="background-color:#FFF"><a style="cursor:pointer; background-color:#A0522D; color:#FFECCF; padding:2px 10px; font-weight:bolder; text-decoration:none" href="javascript:void(0);" id="'+showHideId+'">View Details</a></td></tr>';
	trEq.after(showHideText);
}

function countTds(trLast) {
	var tds = 0; 
	trLast.children().each(function(){
		tds++;
	});
	return tds;
}
/*
 * END OF SHOW HIDE ROWS FOR TABLE
 */

function debug(err){
	//do something...
}

function unAuthorized(err) {
	$.prompt(err,{timeout : 5000,opacity:0.7,overlayspeed:'fast', buttons:{Ok:false}});
}
