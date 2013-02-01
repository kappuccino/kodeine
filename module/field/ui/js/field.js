$(function() {

    datatbl = $('.sortable').dataTable({
		"bInfo": false,
		"bPaginate" : false,
		"oLanguage": {
			"sSearch": "Filtrer les résultats",
			"sInfo": ""
		},
		"aoColumns": aoCol($('.sortable'))
    });
    
    $('.dataTables_filter').css('display', 'none');
    
    if ($('.sortable ').length > 0) {

		//$('th').last().append($('.upper'));		
    	//$('th').last().append('<input type="text" class="input-small nomargin" placeholder="filtrer..." id="filter" style="float:right"/>');
		
		$('#filter').bind('keypress keydown keyup', function() {
			// On a un filter que si une table, pos 0
			datatbl.fnFilter($('#filter').val());
    	});	
    }
    
   changeType();
   doSort();
});

function apply(){
	if(confirm("SUPPRIMER ?")){
		$('#listing').submit();
	}
}

function doSort(){
	
	$('#choices').sortable({
		 handle: '.move',
		 stop: function() {
		 	
		 }
	});
	
	/*
	var mySortables = new Sortables('choices', {
		handle: '.move',
	    constrain: false,
	    revert: true,
	    onComplete: function(e){
	    }
	});*/
}

function changeType(){
	type = $('#fieldType option:selected').val();

	$('.line-type').each(function(i, me){
		$me = $(me);
		
		if($me.hasClass('line-'+type)){
			
			$me.removeClass('line-off');
			$me.find('input').prop('disabled', '');
			$me.find('select').prop('disabled', '');
			$me.find('textatea').prop('disabled', '');
		}else{
			
			$me.addClass('line-off');
			$me.find('input').prop('disabled', 'disabled');
			$me.find('select').prop('disabled', 'disabled');
			$me.find('textatea').prop('disabled', 'disabled');
		}
	});		
}

function addChoice(){
	li = $('<li />').appendTo('#choices');
	sp = $('<span class="move" />').appendTo(li);
	cb = $('<input type="checkbox" disabled="disabled" />').appendTo(li);
	ta = $('<textarea name="choice[new][]" rows="2" cols="40" />').appendTo(li);
	
	doSort();
}

function aoCol(item) {
	var aoCol = [];
	
	$.each(item.find('tr:eq(1) td'), function(k,v) {
		var attr = $(v).attr("data-raw");

		if (typeof attr !== 'undefined') {
			aoCol.push({ "sSortDataType": "data-raw", "sType": "numeric" });
		} else {
			aoCol.push(null);
		}
		
	});	
	return aoCol;
}
