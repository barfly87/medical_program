function addLoId(id) {
    var txt = 'Add learning objective id : ' +id;
    $.prompt(
        txt,
            { 
            buttons:{Add:true, Cancel:false},
            callback: function(add){
                if(add){
			        if (window.opener && !window.opener.closed) {
			             window.opener.document.getElementById('lo_id').value = id;
			             window.opener.displayInfo('loinfo',id);
			             window.close();
			         }
                }
        }
    });
}

function addTaId(id) {
    var txt = 'Add teaching activity id : ' +id;
    $.prompt(
        txt,
            { 
            buttons:{Add:true, Cancel:false},
            callback: function(add){
                if(add){
                    if (window.opener && !window.opener.closed) {
                         window.opener.document.getElementById('ta_id').value = id;
                         window.opener.displayInfo('tainfo',id);
                         window.close();
                     }
                }
        }
    });
}
