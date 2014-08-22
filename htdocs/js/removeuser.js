$(function(){
    $('span.userid').toggle(
        function(event){
            if (this == event.target) {
            	var current = event.target;
            	var userdetails = $(current).siblings().filter('.userdetail');
            	userdetails.html('<p><img src="'+ BASE_URL +'/img/indicator.gif"/></p>');
                $.get(BASE_URL + '/service/userdetail', {uid:$(this).html()}, function(data) {
                    userdetails.html(data);
                });
            }
        },
        function(event){
            if (this == event.target)
                $(this).siblings().filter('.userdetail').html("");
        }
    );
    $('.deleteimg').hover(
    	function(event){
            $(event.target).attr("src", BASE_URL + "/img/delete_select.gif");
        },
        function(event){
        	$(event.target).attr("src", BASE_URL + "/img/delete.gif");
        }
    );
});

function removeUser(action, id, name) {
    var txt = 'Are you sure you want to remove user "' + name + '"?<input type="hidden" id="userid" name="userid" value="'+ id +'" />';
    $.prompt(txt,{ 
        buttons: {Delete: true, Cancel: false},
        callback: function(v,m,f){
            if (v) {
                var uid = f.userid;
                $.post(BASE_URL + '/admin/' + action, {id: f.userid}, function(data) {
                	data = jQuery.trim(data);
                    if (data == 'true') {
                        $('#userid' + uid).hide('slow', function(){ $(this).remove(); });
                    } else if (action == 'deletedomainadmin' && data.indexOf("You do not have permission") != -1){
                    	$.prompt("You do not have permission to perform this action<br />Please contact Compass support");                        
                       } else {
                    		$.prompt('An error occured while removing user "' + name + '"');
                    }
                });
            } else {}
        }
    });
}	

