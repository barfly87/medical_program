function handleSuccess(transport) {
    if(transport.responseText !== undefined){
        document.getElementById('info').innerHTML = transport.responseText;
    }
}

function handleFailure(transport) {
    document.getElementById('info').innerHTML = "";
}

function getinfo(action, id) {
    var sUrl = BASE_URL + '/lotalinkage/' + action + '/format/html/id/' + id;
    new Ajax.Request(sUrl, {
        method: 'get',
        onSuccess: handleSuccess,
        onFailure: handleFailure 
    });
}

function displayInfo(action,id){
	var sUrl = 	BASE_URL + '/lotalinkage/' + action + '/format/html/id/' + id;
	$.get(sUrl, function(data){
		if(data != undefined) {
			if(action == 'loinfo') {
				$('#loinfo').html(data);
			} else if(action == 'tainfo') {
				$('#tainfo').html(data);
			}
		}
	});
}