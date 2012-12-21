var mySortablesRight = {};
var mySortables = {};


$(function() {

	// SORT BLOC RIGHT
	mySortablesRight = $('#la, #lb').sortable({
		
		connectWith: ".mylistleft",
		
		'stop': function(e, ui) {
			parent = $(ui.item[0]).parent();
	    	if(parent.attr('id') == 'lb'){
	    		$('#move').val($('#move').val() + ','+$(ui.item[0]).attr('id')); 
	    	}
		}
	});

	uls = $('#list ul');
	ser = [];
	
	
	// SORT BLOC LEFT
	if(uls.length > 0){
		
		mySortables = uls.sortable({
			'handle': 'div.handle',
			'start' : function(e, ui) {
				$(ui.item[0]).parent().find('.holder').removeClass('view-same');
			},
			'stop': function(e, ui) {
				$(ui.item[0]).parent().find('.holder').addClass('view-same');
				save($(ui.item[0]));
			}
		});
		
		$('div.handle').on('mouseenter', function() {
			console.log($(this).parent().parent().parent().children().children('div.holder'));
			$(this).parent().parent().parent().children().children('div.holder').addClass('view-same');
		});
		
		$('div.handle').on('mouseleave', function() {
			$(this).parent().parent().parent().children().children('div.holder').removeClass('view-same');
		});
	}
});

// SAVUER SORTABLES RIGHT
function sauver(){
	
	ordre = [];
	$('#la li').each(function(i, li) {
		ordre[i] = li.id;
	});
	
	ordre = ordre.join(',');
	document.location='group?sort&id_group='+id_group+'&sort=1&apply='+ordre+'&move='+$('#move').val();
}

// SAVUER SORTABLES LEFT
function save(e){
	
	var m = e.parent().children(); // La sélection et les descendants du meme <ul>
	if(m.length <= 1) return true;

	var tmp = [];
	for(i=0; i<m.length; i++){
		tmp.push($(m[i]).attr('id'));
	}
	
	var set = $.ajax({
		'url' : 'helper/group',
		'data': {'todo' : 'order', 'order' : tmp.join(',')}
	});
	
	set.done(function(data) {
		console.log(data);
	});
	
}
