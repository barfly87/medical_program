function removePblCoordinator(id, name) {
    var txt = 'Are you sure you want to remove user "' + name + '"?<input type="hidden" id="userid" name="userid" value="'+ id +'" />';
    $.prompt(txt,{ 
        buttons: {Delete: true, Cancel: false},
        callback: function(v,m,f){
            if (v) {
                var uid = f.userid;			
                $.post(BASE_URL + '/admin/deletepblcoordinator',{id: f.userid}, function(data) {
                    if (data == 'true') {
                        $('#userid' + uid).hide('slow', function(){ $(this).remove(); });
                    } else {
                        $.prompt('An Error Occured while removing user "' + name + '"');
                    }
                });
            } else {}
        }
    });
}	
