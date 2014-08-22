function addremoveaudience(type, type_id, domain_id, domain_name) {
	var useraction, txt;
	var checked = $('#domaindamincheckbox').attr('checked');
	if (checked) {
		useraction = 'add';
		txt = 'Are you sure you want to add "' + domain_name + '" to the audience?';
	} else {
		useraction = 'remove';
		txt = 'Are you sure you want to remove "' + domain_name + '" from the audience?';
	}
    $.prompt(txt,{ 
        buttons: {Yes: true, No: false},
        callback: function(v,m,f){
            if (v) {
                $.post(BASE_URL + '/admin/audience', {useraction: useraction, type: type, type_id: type_id, domain_id: domain_id}, function(data) {
                	$.prompt(data, {timeout: 1200, opacity: 0.7, overlayspeed: 'fast', buttons: {}});
                });
            } else {
            	$('#domaindamincheckbox').attr('checked', !checked);
            }
        }
    });
}