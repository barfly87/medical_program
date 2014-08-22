(function($) {
    $.fn.emptySelect = function() {
        return this.each(function(){
            if (this.tagName=='SELECT') this.options.length = 0;
        });
    }

    $.fn.loadSelect = function(optionsDataArray) {
        return this.emptySelect().each(function(){
            if (this.tagName=='SELECT') {
                var selectElement = this;
                $.each(optionsDataArray,function(index,value){
                    var option = new Option(value, index);
                    if ($.browser.msie) {
                        selectElement.add(option);
                    } else {
                        selectElement.add(option,null);
                    }
                });
            }
        });
    }
})(jQuery);

$(document).ready(function() {
	if ($('#block_week').size() == 1) {
	    $('#stage').change(loadBlocks);
	    $('#block').change(loadWeeksAndPbls);
	    $('#block_week').change(loadPbls);
	    $('#pbl').change(loadWeeks);
	} else {
		$('#stage').change(loadYears);
		$('#year').change(loadYearBlocks);
	}
});

function loadYears() {
	var stageId = $('#stage option:selected').val();
    $.getJSON(BASE_URL + '/teachingactivity/yearsforstage/stage_id/' + stageId, function(data){
        $('#year').loadSelect(data);
        loadYearBlocks();
    })
}

function loadYearBlocks() {
	var yearId = $('#year option:selected').val();
	$.getJSON(BASE_URL + '/teachingactivity/blocksforyear/year_id/' + yearId, function(data){
	    $('#block').loadSelect(data);
	})
}

function loadBlocks() {
    var stageId = $('#stage option:selected').val();
    $.getJSON(BASE_URL + '/teachingactivity/blocksforstage/stage_id/' + stageId, function(data){
        $('#block').loadSelect(data);
        loadWeeksAndPbls();
    })
}

function loadWeeksAndPbls() {
    var stageValue = $('#stage option:selected').text();
    var stageId = $('#stage option:selected').val();
    var blockValue = $('#block option:selected').text();
    var blockId = $('#block option:selected').val();
    $.getJSON(BASE_URL + '/teachingactivity/weeksforblock/stage_id/' + stageId + '/block_id/' + blockId, function(data){
    	$('#block_week').loadSelect(data);
    })
    $.getJSON(BASE_URL + '/teachingactivity/pblsforblock/stage_id/' + stageId + '/block_id/' + blockId, function(data){
    	$('#pbl').loadSelect(data);
    });
}

function loadPbls() {
    var stageValue = $('#stage option:selected').text();
    var stageId = $('#stage option:selected').val();
    var blockValue = $('#block option:selected').text();
    var blockId = $('#block option:selected').val();
    var weekValue = $('#block_week option:selected').text();
    var weekId = $('#block_week option:selected').val();      
    if ((stageValue == '1' || stageValue == '2') && blockValue != '' && weekValue != '') {
        $.getJSON(BASE_URL + '/teachingactivity/pblforweek/stage_id/' + stageId + '/block_id/' + blockId + '/week_id/' + weekId, function(data){
        	$('#pbl').val(data);
        });
    }        
}

function loadWeeks() {
    var stageValue = $('#stage option:selected').text();
    var stageId = $('#stage option:selected').val();
    var blockValue = $('#block option:selected').text();
    var blockId = $('#block option:selected').val();
    var pblValue = $('#pbl option:selected').text();
    var pblId = $('#pbl option:selected').val();      
    if ((stageValue == '1' || stageValue == '2') && blockValue != '' && pblValue != '') {
        $.getJSON(BASE_URL + '/teachingactivity/weekforpbl/stage_id/' + stageId + '/block_id/' + blockId + '/pbl_id/' + pblId, function(data){
        	$('#block_week').val(data);
        });
    }        
}