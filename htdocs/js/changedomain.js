function changedomain() {
    $.post(BASE_URL + '/auth/changedomain', {domain: $('#domaindropdown').val()}, function(data) {
    	alert(data);
    });
}