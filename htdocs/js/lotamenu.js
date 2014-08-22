$(document).ready(function(){
    loc = window.location.pathname;
    $("#submenu ul li a").each(function() {
    	linkLoc = $(this).attr('href');
    	if(linkLoc == loc) {
            var style = 'color: #283A50;' +
                        'background: url('+ BASE_URL +'/img/menu3.gif) 0 -32px;' +
                        'padding: 8px 0 0 30px;';
            $(this).attr('style', style);
    	}
    });
});
