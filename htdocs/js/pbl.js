var PBL_DEBUG = true;

//var TR_resrcTypeTr ='#resrcTypeTr';
var TR_resrcSubType = '#resrcSubTypeTr';
var TR_resrcTitles = '#resrcTitlesTr';
var TR_resrcTitleText = '#resrcTitleTextTr';
var TR_resrcText = '#resrcTextTr';
var TR_resrcOr = '#resrcOrTr';
var TR_resrcImage = '#resrcImgTr';
var TR_resrcFile = '#resrcFileTr';
var TR_resrcSubmit = '#resrcSubmitTr';

var TD_resrcImg = '#resrcImgTd';
var TD_resrcSubType = '#resrcSubTypeTd';

var SELECT_resrcType = '#resrcType';
var SELECT_resrcSubType = '#resrcSubType';
var SELECT_resrcTitles = '#resrcTitles';
var TEXTAREA_resrcText = '#resrcText';
var INPUT_HIDDEN_singleOrMulti = '#resrcTypeSingleOrMulti';
var INPUT_HIDDEN_resrcAction = '#resrcAction';
var INPUT_HIDDEN_resrcData = '#resrcData';
var INPUT_resrcTitle = '#resrcTitle';
var INPUT_resrcFile = '#resrcFile';

var DIV_error = '#showHideError';
var DIV_exception = '#exception';

//var CLASS_clicked = '.clicked';

var TXT_showError = 'Show Error';
var TXT_hideError = 'Hide Error';
var TXT_single = 'single'; 
var TXT_multi = 'multi'; 
var TXT_none = 'none'; 
//var TXT_clicked = 'clicked';
var TXT_resrcActionEdit = 'edit';
var TXT_resrcActionAdd = 'add';
var TXT_resrcDataText ='text';
var TXT_resrcDataImage ='image';

var URL_resourceJson = BASE_URL + '/resource/json/mid/';
var URL_subresourcetypeoptions = BASE_URL + '/pbl/ajax/get/subresourcetypeoptions/elemid/' + SELECT_resrcSubType.replace('#','') + '/elemclass/textWidth/id/';

var SPAN_pblresourceFormError = '#pblresourceFormError';
var SPAN_deleteContainer = '#deleteContainer';

var ID_resourceId = '#resourceId';
var ID_typeId = '#typeId';
var ID_submit = '#pblresourceForm #submit';

var DIV_js_error = '#jsErrors ul';

var errors = {
			'textAndFileEmptyAddAction' 	: 'Please type some text OR upload a file.',
			'textAndFileEmptyEditAction' 	: 'Please type some text OR upload a file to make changes to this resource.',
			'resrcSubType'					: 'Please select Pbl Resource Sub Type.',
			'resrcTitle'					: 'Title field is empty.'
			}
try{
	$(document).ready(function(){
		hideAllResourceTr();
		$(SELECT_resrcType).change(function(){
		    resrcTypeChanged();
	    });
		$(SELECT_resrcTitles).change(function() {
		    resrcTitlesChanged();
		});
		$(DIV_error).click(function(){
			$(DIV_exception).toggle("fast");
			val = $(DIV_error).html();
			if(val == TXT_showError) {
				$(DIV_error).html(TXT_hideError);
			} else {
				$(DIV_error).html(TXT_showError);
			}
		});
	});
} catch(err) {pblDebug(err);}  

//SHOW/HIDE FORM ELEMENTS - FUNCTIONALITY : START
function hideAllResourceTr() {
	$(TR_resrcSubType).hide();
	$(TR_resrcTitles).hide();
	$(TR_resrcTitleText).hide();
	$(TR_resrcText).hide();
	$(TR_resrcOr).hide();
	$(TR_resrcImage).hide();
	$(TR_resrcFile).hide();
	$(TR_resrcSubmit).hide();
}
function showResrcTypeSingleFormElems() {
	$(TR_resrcSubType).hide();
	$(TR_resrcTitles).hide();
	$(TR_resrcTitleText).hide();
	$(TR_resrcText).show();
	$(TR_resrcOr).show();
	$(TR_resrcImage).show();
	$(TR_resrcFile).show();
	$(TR_resrcSubmit).show();
}
function showResrcSubTypeFormElems() {
	$(TR_resrcSubType).show();
	$(TR_resrcTitles).hide();
	$(TR_resrcTitleText).hide();
	$(TR_resrcText).hide();
	$(TR_resrcOr).hide();
	$(TR_resrcImage).hide();
	$(TR_resrcFile).hide();
	$(TR_resrcSubmit).hide();
}
function showResrcTypeMultiFormElems() {
	$(TR_resrcSubType).show();
	$(TR_resrcTitles).show();
	$(TR_resrcTitleText).show();
	$(TR_resrcText).show();
	$(TR_resrcOr).show();
	$(TR_resrcImage).show();
	$(TR_resrcFile).show();
	$(TR_resrcSubmit).show();
}
//Resource Type Changed 
function resrcTypeChanged() {
    try {
    	$(SPAN_pblresourceFormError).html('');
        var resrcTypeId =  $(SELECT_resrcType).val() ;
        var resrcType = TXT_none;
        if(resrcTypeId != 0) {
            resrcType = pblResourceType[resrcTypeId];
        }
        if(resrcType == TXT_none) {
        	processResrcTypeNone();
        } else {
            if(resrcType == TXT_single) {
            	processResrcTypeSingle(resrcTypeId);
            } else if(resrcType == TXT_multi) {
            	processResrcTypeMulti(resrcTypeId);
            	
            }
        }
    } catch(err) {pblDebug(err);}
} 

function resrcTitlesChanged() {
	try{
	    var selected = $(SELECT_resrcTitles).val();
	    var resrcSubType = $(SELECT_resrcSubType).val();
	    if(selected != 0) {
	        if(typeof(resrcTypeMulti[resrcSubType]) !== 'undefined' && typeof(resrcTypeMulti[resrcSubType][selected]) !== 'undefined') {
	            var mid = resrcTypeMulti[resrcSubType][selected].mid;
	            $.getJSON(URL_resourceJson + mid, function(data){
	            	setResrcId(selected);
	                populateResrc(data);
	                setResrcAction(TXT_resrcActionEdit);
	            });
	        }
	    } else {
	        setResrcId('');
	        setResrcTitle('');
	        setResrcText('');
	        setResrcImg('');
	        setResrcAction(TXT_resrcActionAdd);
	    }
	} catch(err) {pblDebug(err);}  
} 
//DEBUG : START
function pblDebug(err) {
	if(PBL_DEBUG == true) {
		try {
			var errStr = "<a href='" +err.fileName+ "'>" + err.fileName + '</a><br />' + "'<b>" +err.name + "</b>' on line no <b>"+ err.lineNumber + "</b> : <b><i>"+  err.message + "</b></i><br />";
			$(DIV_js_error).append('<li>'+errStr+'</li>');
		} catch(error) {
			
		}
	}
}
//DEBUG : END

function processResrcTypeNone() {
	try{	
		hideAllResourceTr();
	    setResrcTitle('');
	    setResrcText('');
	    setResrcSingleOrMulti('');
	    setResrcImg('');
	} catch(err) {pblDebug(err);}      
}
function setResrcTitle(data){
    try { 
    	$(INPUT_resrcTitle).val(data);
    } catch(err) {pblDebug(err);}        	
}
function setResrcText (data){
    try {   
    	$(TEXTAREA_resrcText).val(data);
    	if(typeof(CKEDITOR.instances["resrcText"]) != 'undefined') {
    		CKEDITOR.instances["resrcText"].setData(data);
    	}
    } catch(err) {pblDebug(err);}  
}
function setResrcSingleOrMulti (fileType) {
	try {
		$(INPUT_HIDDEN_singleOrMulti).val(fileType);
    } catch(err) {pblDebug(err);} 	
}
function setResrcImg(src){
    try {	
    	if(src.length == '') {
    	    $(TD_resrcImg).html('');
        } else {
        	var onErrorUrl = "'"+ BASE_URL + "/img/noimage/noimage_150x100.gif'";  
            var img = '<img onerror="this.src=' + onErrorUrl + ';" src="' + src + '"/>';
            $(TD_resrcImg).html(img);
        }
    } catch(err) {pblDebug(err);}    
}
function setResrcData(data){
    try { 	
    	$(INPUT_HIDDEN_resrcData).val(data);
    } catch(err) {pblDebug(err);}  
}
function setResrcAction(action) {
    try { 	
    	$(INPUT_HIDDEN_resrcAction).val(action);
    	if(action == TXT_resrcActionEdit) {
    		setSubmit('Edit');
    		showDeleteButton();
    	} else {
    		setSubmit('Submit');
    		hideDeleteButton();
    	}
    } catch(err) {pblDebug(err);}  
}
function setResrcId(id) {
	$(ID_resourceId).val(id);
}
function showDeleteButton() {
	$(SPAN_deleteContainer).show();
}
function hideDeleteButton() {
	$(SPAN_deleteContainer).hide();
}
function setSubmit(text) {
	$(ID_submit).val(text);
}

function processResrcTypeSingle(resrcTypeId) {
	try{	
		setResrcSingleOrMulti(TXT_single);
	    setResrcImg('');
	    setResrcText('loading ... ');
	    if(typeof(resrcTypeSingle[resrcTypeId]) !== 'undefined') {
            var mid = resrcTypeSingle[resrcTypeId].mid;
            $.getJSON(URL_resourceJson + mid, function(data){
                populateResrc(data);
                setResrcAction(TXT_resrcActionEdit);
                setResrcId(resrcTypeId);
            });
	    } else {
	    	setResrcId('');
	    	setResrcText('');
	    	setResrcAction(TXT_resrcActionAdd);
	    }
	    showResrcTypeSingleFormElems();
	} catch(err) {pblDebug(err);}      
}
function populateResrc(data) {
	try{	
	    if(typeof(data['html']) !== 'undefined' && typeof(data['html']['val']) !== 'undefined') {
	        setResrcImg('');
	        setResrcText(data['html']['val']);
	        setResrcData(TXT_resrcDataText);
	    } else {
	        setResrcText('');
	        if(typeof(data['image']) !== 'undefined' && typeof(data['image']['src']) !== 'undefined') {
	            var image = data['image']['src'];
	            image = image.replace('\/','/').replace('height=500','height=250').replace('width=500','width=250');
	            setResrcImg(image);
	            setResrcData(TXT_resrcDataImage);
	        }
	    }
	    if(typeof(data['data']) !== 'undefined' && typeof(data['data']['title']) !== 'undefined') {
	        setResrcTitle(data['data']['title']);
	    } else {
	        setResrcTitle('');
	    }
	} catch(err) {pblDebug(err);}  
}

function processResrcTypeMulti(resrcTypeId) {
	try{
		setResrcAction('');
		var url = URL_subresourcetypeoptions + resrcTypeId;
		//ajax call to get options for resource sub type
		$.get(url,function(select) {
			select = $.trim(select);
			if(select != '' && select != "false") {
				$(TD_resrcSubType).html(select);
			} else {
				$(TD_resrcSubType).html("<span class='error'>Error !</span> Could not find any resource subtype.");
			}
			showResrcSubTypeFormElems();
		    $(SELECT_resrcSubType).change(function(){
		    	var resrcSubTypeId = $(this).val();
		    	resrcSubTypeChanged(resrcSubTypeId);
		    });
		});
	} catch(err) {pblDebug(err);}   	
}
function resrcSubTypeChanged(resrcSubTypeId) {
	try{
		setResrcTitle('');
	    setResrcText('');
	    setResrcSingleOrMulti(TXT_multi);
	    setResrcAction(TXT_resrcActionAdd);
	    setResrcImg('');
	    if(resrcSubTypeId != 0) {
		    createOptionsForResrcTitles(resrcSubTypeId);
		    showResrcTypeMultiFormElems();
	    } else {
	    	showResrcSubTypeFormElems();
	    }
	} catch(err) {pblDebug(err);}      
}
function createOptionsForResrcTitles(resrcSubTypeId) {
	try{
		var options = '<option value="0">** ADD NEW RESOURCE **</option>';
	    if(typeof(resrcTypeMulti[resrcSubTypeId]) !== 'undefined') {
	        for(var resourceId in resrcTypeMulti[resrcSubTypeId]) {
	               options += '<option value="' + resourceId + '">'+ resrcTypeMulti[resrcSubTypeId][resourceId].title +'</option>';
	        }
	   }
	   $(SELECT_resrcTitles).html(options);
	} catch(err) {pblDebug(err);}      
}

//FORM VALIDATION - FUNCTIONALITY : START
function validatePblResrcForm() {
	try {
		var formSubmit = false;
		var params = {};
		params['resrcText'] = getResrcTextFiltered();
		params['resrcFile'] = getResrcFile();
		var resrcType = $(SELECT_resrcType).val();
		
		if(resrcType != '0' && typeof(pblResourceType[resrcType]) !== 'undefined') {
			if(pblResourceType[resrcType] == TXT_single) {
				params['resrcAction'] = (typeof(resrcTypeSingle[resrcType]) !== 'undefined')  ? 'edit' : 'add' ;
				formSubmit = validateFormResrcTypeSingle(params);
			} else if (pblResourceType[resrcType] == TXT_multi) {
				params['resrcSubType'] = getResrcSubType();
				params['resrcTitles'] = getResrcTitles();
				params['resrcTitle'] = getResrcTitle();
				params['resrcData'] = getResrcData();
				params['resrcAction'] = (typeof(resrcTypeMulti[params['resrcSubType']]) !== 'undefined' && typeof(resrcTypeMulti[params['resrcSubType']][params['resrcTitles']]) !== 'undefined')  ? 'edit' : 'add' ;
				formSubmit = validateFormResrcTypeMulti(params);
			}
		}
		return formSubmit;
	} catch(err) {
		pblDebug(err);
		return false;
	}
}
function getResrcTextFiltered() {
	try {
		var resrcTxt = CKEDITOR.instances["resrcText"].getData();
		resrcTxt = jQuery.trim(resrcTxt.replace( /<[^<|>]+?>/gi,'').replace(/&nbsp;/g,''));
		return resrcTxt;
	} catch(err) { pblDebug(err);return '';}  	
}
function getResrcFile() {
	try {
		return jQuery.trim($(INPUT_resrcFile).val());
	} catch(err) { pblDebug(err); return '';}  	
}
function getResrcSubType() {
    try { 
    	return jQuery.trim($(SELECT_resrcSubType).val());
    } catch(err) {pblDebug(err); return '';}        	
}
function getResrcTitles() {
    try { 
    	return jQuery.trim($(SELECT_resrcTitles).val());
    } catch(err) {pblDebug(err); return '';}        	
}
function getResrcTitle() {
    try { 
    	return jQuery.trim($(INPUT_resrcTitle).val());
    } catch(err) {pblDebug(err); return '';}        	
}
function getResrcData() {
    try {	
    	return jQuery.trim($(INPUT_HIDDEN_resrcData).val());
    } catch(err) {pblDebug(err); return'';}    	
}
function validateFormResrcTypeSingle(params) {
	try {
		var error = {};
		error['errorFound'] = false;
		if(params['resrcText'].length == 0 && params['resrcFile'].length == 0) {
			error['errorFound'] = true;
			if(params['resrcAction'] == 'add') {
				error['textAndFileEmptyAddAction'] = true;	
			} else if (params['resrcAction'] == 'edit') {
				error['textAndFileEmptyEditAction'] = true;
			}
		}
		if(error['errorFound'] == true) {
			displayErrors(error);
			return false;
		}
		return true;
	} catch(err) {
		pblDebug(err);
		return false;
	}
}

function validateFormResrcTypeMulti(params) {
	try {
		var error = {};
		error['errorFound'] = false;
		if(params['resrcSubType'] <= 0) {
			error['resrcSubType'] = true;
			error['errorFound'] = true;
		}
		if(params['resrcTitle'].length == 0) {
			error['resrcTitle'] = true;
			error['errorFound'] = true;
		}
		var textFileError = (params['resrcText'].length == 0 && params['resrcFile'].length == 0);
		if(params['resrcAction'] == 'add') {
			if(textFileError) {
				error['textAndFileEmptyAddAction'] = true;
				error['errorFound'] = true;
			}
		} else if (params['resrcAction'] == 'edit') {
			if(params['resrcData'] == 'text' && textFileError) {
				error['textAndFileEmptyEditAction'] = true;
				error['errorFound'] = true;
			} 
		}
		if(error['errorFound'] == true) {
			displayErrors(error);
			return false;
		}
		return true;
	} catch (err) {
		pblDebug(err);
		return false;
	}
}

function displayErrors(error) {
	try {
		var errorStr = '<ul>';
		for(var err in error) {
			if(typeof(errors[err]) !== 'undefined') {
				errorStr += '<li class="red">' + errors[err] + '</li>';
			}
		}
		errorStr += '</ul>'; 
		$(SPAN_pblresourceFormError).html(errorStr);
	} catch(err) { pblDebug(err); }
}
//FORM VALIDATION - FUNCTIONALITY : END
