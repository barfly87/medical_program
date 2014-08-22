function meshPopup() {
	window.open(BASE_URL + '/mesh/index', 'meshtree', 'scrollbars=1,menubar=0,resizable=1,width=500,height=400');
}

//use mesh crawler to process text from discipline dropdown and learning objective fields
function crawlerPopup() {
	var qstring = '';
	if ($('#discipline1 option:selected').text() != '')
		qstring = $('#discipline1 option:selected').text().replace(" / ", ". ") + '. ';
	if ($('#discipline2 option:selected').text() != '')
		qstring = qstring + $('#discipline2 option:selected').text().replace(" / ", ". ") + '. ';
	if ($('#discipline3 option:selected').text() != '')
		qstring = qstring + $('#discipline3 option:selected').text().replace(" / ", ". ") + '. ';
	if ($('#usefirstline')[0].checked) {
		qstring = qstring + 'At the end of ' + $('#activity option:selected').text() + ', students should be able to ' +
			$('#ability option:selected').text() + ' ';
	}
	qstring = qstring + CKEDITOR.instances.lo.getData();
	q_str = $("<div>" + qstring + "</div>").text();
	var crawlerwin = window.open('', 'crawler', 'scrollbars=1,menubar=0,resizable=1,width=800,height=600');
	var tmp = crawlerwin.document;
	tmp.write('<html>\n');
	tmp.write('  <head>\n');
	tmp.write('    <title>Mesh Crawler</title>\n');
	tmp.write('    <script type="text/javascript">var BASE_URL = "' + BASE_URL + '"; </script>\n');
	tmp.write('    <script type="text/javascript" src="' + BASE_URL + '/js/prototype.js"></script>\n');
	tmp.write('    <script type="text/javascript" src="' + BASE_URL + '/js/jquery/jquery.js"></script>\n');
	tmp.write('    <script type="text/javascript" src="' + BASE_URL + '/js/mesh.js"></script>\n');
	tmp.write('  </head>\n');
	tmp.write('  <body>\n');
	tmp.write('    <p>The MeSH Crawler will automatically cross reference the learning objective against the mesh dictionary to suggest keywords. This may take a few minutes.</p>\n');
	tmp.write('    <form name="lo" id="lo" action="'+ BASE_URL + '/mesh/crawler/format/html" method="post">\n');
	tmp.write('      <div style="display:none">\n');
	tmp.write('        <textarea rows="5" cols="80" readonly="readonly" name="lotext" id="lotext">\n');
	tmp.write(q_str);
	tmp.write('        </textarea>\n');
	tmp.write('      </div>\n');
	tmp.write('      <p>Please <input type="submit" value="Continue"> or <input type="button" value="Cancel" onclick="javascript:window.close();"></p>\n');
	tmp.write('    </form>\n');
	tmp.write('    <div id="suggestedkeywords"></div>\n');
	tmp.write('    <div id="pleasewaitScreen" style="position:absolute;z-index:5;top:30%;left:42%;visibility:hidden"><img src="/compass/img/progressbar.gif" /></div>\n');
	tmp.write('  </body>\n');
	tmp.write('</html>');
	tmp.close();
}

function meshPick(nodeid) {
	var lastid = $('ul#keywordslist li:last-child').attr('id');
	var newid = 0;
	if (typeof(lastid) != "undefined")
		newid = parseInt(lastid.substring('keyword_'.length)) + 1;
    $('<li id="keyword_' + newid + '"><input type="hidden" name="keywords[]" value="' + nodeid+ '">' + nodeid + '<a style="padding-left:10px" href="javascript:remove(\'keyword_' + newid + '\')"><img src="/compass/img/delete.gif" border="0"></a></li>').appendTo('#keywordslist');
}

