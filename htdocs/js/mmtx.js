
// Have a function run after the page loads:
window.onload = init;
var ajax;

// Function that adds the Ajax layer:
function init() {

  // Get an XMLHttpRequest object:
  ajax = getXMLHttpRequestObject();
  
  // Attach the function call to the form submission, if supported:
  if (ajax) {
  
   
      // Add an onsubmit event handler to the form:
      document.getElementById('lo').onsubmit = function() {
		document.getElementById('pleasewaitScreen').style.pixelTop = (document.body.scrollTop + 50);

		document.getElementById('pleasewaitScreen').style.visibility="visible";
             
        // Function that handles the response:
        ajax.onreadystatechange = function() {
          // Pass it this request object:
          handleResponse(ajax);
        }

        // Call the PHP script.
		window.setTimeout("sendrequest()" ,100);
      
        return false; // So form isn't submitted.

      } // End of anonymous function.
      
    
  } // End of ajax IF.

} // End of init() function.

function sendrequest() {

    // Open the connection:
    // Use the POST method.
    ajax.open('post', 'mmtx_ajax.php');

    // Set the request headers:
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
    // Send the request along with the data:
    ajax.send('lotext=' + encodeURIComponent(document.getElementById('lotext').value));
}

// Function that handles the response from the PHP script:
function handleResponse(ajax) {

  // Check that the transaction is complete:
  if (ajax.readyState == 4) {
  
    // Check for a valid HTTP status code:
    if ((ajax.status == 200) || (ajax.status == 304) ) {
      
      // Put the received response in the DOM:
	  document.getElementById('pleasewaitScreen').style.visibility="hidden";
      var results = document.getElementById('suggestedkeywords');
      results.innerHTML = ajax.responseText;
      
    } else { // Bad status code, submit the form.
      //document.getElementById('dept_form').submit();
    }
    
  } // End of readyState IF.
  
} // End of handleResponse() function.

