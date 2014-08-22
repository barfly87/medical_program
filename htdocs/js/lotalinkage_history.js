$(document).ready(function() {
    $('#sortTable tbody tr').each(function() {
        $(this).find("td").eq(0).attr('class','center');
        $(this).find("td").eq(1).attr('class','center');
        $(this).find("td").eq(2).attr('class','center');    
        
        var action = $(this).find("td").eq(2).html();
        if(action == 'deleted') {
            $(this).find("td").eq(2).attr('style','color: #DF1B1B;');
        }
    });
});