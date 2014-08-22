$(function(){
	$('span.username').click(function() {
		
		//var userid 		= $(this).html();
		var parent 		= $(this).parent();
		var userid 		= parent.children('.userid').html();
		var userdetails = parent.children('div.userdetails');
		var userExist 	= true;
		
		if(userdetails.children('img').attr('src') != undefined) {
			userExist = false;
		}
		
		if(userExist == false) {
			userdetails.children('img').removeClass('hide');
			var url 	= BASE_URL + '/user/details';
			var q 		= parent.children('span.cryptuid').html();
			$.getJSON(url, { uid: userid , view: 'basic', q: q },function(data){
				var userdetailsHtml = processUserdetails(data);
				userdetails.html(userdetailsHtml);
			});
		} else {
			userdetails.toggle('slow');
		}
	});
});

function processUserdetails(data) {
	var html = '';
	if(data.error != undefined && data.error.length > 0) {
		html += '<span class="red">Error !<br />Unable to retrieve user details.</span>';
		return html;
	} else {
		html += '<table><tr><td valign="top" style="padding-left: 0px;">';
		if(data.image != undefined && data.image.length > 0) {
			html += '<img class="uidimages" src="' + data.image + '" onerror="this.src=\''+ BASE_URL+ '/img/noimage/empty1x1.gif\'; this.width=\'1\';" width="36"/></td><td valign="top">';
		}
		if(data.title != undefined && data.title.length > 0) {
			html += data.title;
		}
		if(data.faculty != undefined && data.faculty.length > 0 && data.faculty != 'null') {
			if(data.title != undefined && data.title.length > 0) {
				html += ', ';
			}
			html += data.faculty + '<br />';
		} else if(data.title != undefined && data.title.length > 0) {
			html += '<br />';	
		}
		
		if(data.phone != undefined && data.phone.length > 0) {
			html += data.phone + '<br />';
		}
		if(data.email != undefined && data.email.length > 0) {
			html += '<a href="mailto:'+data.email+'">' + data.email + '</a>&nbsp;&nbsp;&nbsp;';
		}
		if(data.profile != undefined && data.profile.length > 0) {
			html += '<a href="'+data.profile+'" >profile</a>&nbsp;&nbsp;&nbsp;';
		}
		if(data.account_tool != undefined && data.account_tool.length > 0) {
			html += '<a href="'+data.account_tool+'" >account tool</a>&nbsp;&nbsp;';
		}
		html += '</td></tr></table>';
		return html;
	}
}

