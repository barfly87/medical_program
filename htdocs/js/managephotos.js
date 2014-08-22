var RESOURCE_DEBUG = true;
var defaultPrepend = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:90%;">';
var defaultAppend = '</span>';
var prependRed = '<span style="margin:10px;text-align:center;font-weight:bold; font-size:130%;color:red;">';

function setDefaultPhoto(mid) {
	setUrl = BASE_URL + '/people/setdefaultphoto/mid/'+mid;
	$.ajax({
		url: setUrl,
		async: false,
		type: "GET",
		success: function(data) {
			result = data;
			if(result != 'success') {
				if($('#messagebox').is(':animated')) {
					$('#messagebox').stop();
				}
				$('#messagebox').html(prependRed+'Error setting default photo'+defaultAppend);
				$('#messagebox').animate({opacity: 100}, 200);
				$('#messagebox').animate({opacity: 0}, 2000);
				return;
			} else {
				if($('#messagebox').is(':animated')) {
					$('#messagebox').stop();
				}
				$('#messagebox').html(defaultPrepend+'Default photo changed'+defaultAppend);
				$('#messagebox').animate({opacity: 100}, 200);
				$('#messagebox').animate({opacity: 0}, 2000);
			}
	}
	});     
}

function setOfficialPhoto(mid, uid) {
	setUrl = BASE_URL + '/people/setofficialphoto/mid/'+mid + '/uid/' + uid;
	$.ajax({
		url: setUrl,
		async: false,
		type: "GET",
		success: function(data) {
			result = data;
			if(result != 'success') {
				if($('#messagebox').is(':animated')) {
					$('#messagebox').stop();
				}
				$('#messagebox').html(prependRed+'Error setting official photo'+defaultAppend);
				$('#messagebox').animate({opacity: 100}, 200);
				$('#messagebox').animate({opacity: 0}, 2000);
				return;
			} else {
				if($('#messagebox').is(':animated')) {
					$('#messagebox').stop();
				}
				$('#messagebox').html(defaultPrepend+'Official photo changed'+defaultAppend);
				$('#messagebox').animate({opacity: 100}, 200);
				$('#messagebox').animate({opacity: 0}, 2000); 
			}
	}
	});
	location.reload();
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