function serialMe(ul){

	var all = [];
	var lis = $('#'+ul).children('li');

	lis.each(function(i, li){
		all.push($(li).attr('id'));
	});

	var get = $.ajax({
		'url' : 	'helper/order',
		'dataType': 'json',
		'data' : {
			'id_socialforum': 	$('#'+ul).attr('id'),
			'ordered': 		all.join('-')
		}
	}).done(function(d) {
		console.log('Serialized '+data)
	});
}


function serialSave(){
	$('#order').submit();
}

function makeMeSortable(ul){
	var mySortables = ul.sortable({
		'handle': 'div.handle',
		'stop': function(e, ui) {
			serialMe(ul.attr('id'));
		}
	});
}

function removeSelection(){
	if(confirm("Really ?")){
		$('#items').submit();
	}
}

function edit(id_socialforum){
	document.location = './forum?id_socialforum='+id_socialforum+'&opened=' + opened.join('-');
}

function openedMemo(){
	$('.opened-memo').val(opened.join('-'));
}

function threadMe(me, level, mid_socialforum){

	if($(me).hasClass('opened')){
		$(me).removeClass('opened');
		$('#mid-'+mid_socialforum).css('display', 'none');

		var _opened = [];
		for(i=0; i<opened.length; i++){
			if(mid_socialforum != opened[i]) _opened.push(opened[i]);
		}
		opened = _opened;
		openedMemo();
	}else{
		$(me).addClass('opened');

		if(mid_socialforum > 0) opened.push(mid_socialforum);

		if($('#mid-'+mid_socialforum).css('display') == 'none'){
			$('#mid-'+mid_socialforum).css('display', 'block');
		}else{
			thread(mid_socialforum, (level+1));
		}
	}
}

function thread(mid_socialforum, level){

	var get = $.ajax({
		'url' : 'helper/forum-thread',
		'url' : 'helper/forum-thread',
		'data': {'mid_socialforum':mid_socialforum, 'level':level}
	});

	get.done(function(data) {

		$('#mid-'+mid_socialforum).html(data);

		var lis = $('#mid-'+mid_socialforum).children('li');

		if(lis.length > 0){
			lis.each(function(key, li){

				var found = -1;
				for(i=0; i<opened.length; i++){
					if(found < 0 && $(li).attr('id') == opened[i]) found = $(li).attr('id')
				}

				if(found > 0){
					$(li).find('.toggle').addClass('opened');
					thread(found, level+1);
				}
			});
		}

		makeMeSortable($('#mid-'+mid_socialforum));
	});
}













