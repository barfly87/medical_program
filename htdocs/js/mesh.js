window.onload = init;
function init() {
    //Add an onsubmit event handler to the form:
    document.getElementById('lo').onsubmit = function() {
        document.getElementById('pleasewaitScreen').style.pixelTop = (document.body.scrollTop + 50);
        document.getElementById('pleasewaitScreen').style.visibility = "visible";

        // Call the PHP script.
        window.setTimeout("sendrequest()" ,100);
        return false; // So form isn't submitted.
    } // End of anonymous function.
}

function sendrequest(){
    var sUrl = BASE_URL + '/mesh/crawler/format/html';
    new Ajax.Request(sUrl, {
        method: 'post',
        parameters: 'lotext=' + encodeURIComponent(document.getElementById('lotext').value),
        onSuccess: function(transport) {
            if (transport.responseText !== undefined){
                // Put the received response in the DOM:
                document.getElementById('pleasewaitScreen').style.visibility = "hidden";
                var results = document.getElementById('suggestedkeywords');
                results.innerHTML = transport.responseText;
            }
        },
        onFailure: function(transport) {
            //document.getElementById('lo').submit();
        }
    });
}
	
function pick(meshTerm) {
    window.opener.meshPick(meshTerm);
}
