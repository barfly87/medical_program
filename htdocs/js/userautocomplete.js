$(document).ready(function(){
    $("#principal_teacher").autocomplete(BASE_URL + "/service/allusers", {
	    width: 240,
	    delay: 200,
	    multiple: true,
	    matchContains: true,
	    mustMatch: true,
	    selectFirst: true,
	    formatItem: function(data, i, n, value) {
	        var tmp = value.split(" ");
	        var uid = tmp.splice(tmp.length-1,1);
	        return tmp.join(" ") + " [" + uid +"]";
	    },
        formatResult: function(data, value) {
	        var result = (data + "").split(" ");
	        return result[result.length-1];
        }
    });
    $("#current_teacher").autocomplete(BASE_URL + "/service/allusers", {
	    width: 240,
	    delay: 200,
	    multiple: true,
	    matchContains: true,
	    mustMatch: true,
	    selectFirst: true,
	    formatItem: function(data, i, n, value) {
	        var tmp = value.split(" ");
	        var uid = tmp.splice(tmp.length-1,1);
	        return tmp.join(" ") + " [" + uid +"]";
	    },
        formatResult: function(data, value) {
	        var result = (data + "").split(" ");
	        return result[result.length-1];
        }
    });   
});