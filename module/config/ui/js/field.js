
var mySort = $('#la, #lb').sortable({

	connectWith: ".myList",
	stop: function(e,ui) {
	
		parent = $(this);
		item = $(ui.item.context);
		
    	if(parent.attr('id') == 'la'){
    		$('#move').val($('#move').val()+','+item.attr('id'));
    	}
    	
	}
});

function sauver(){
	var ordre = mySort.sortable('toArray');

	if (ordre.length > 0) {
		ordre = ordre.join(',');
	} else {
		ordre = ',';
	}
	
	document.location='field?apply=' + ordre + '&move=' + $('#move').val();
}
