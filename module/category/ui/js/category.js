$(function(){

	tab(languages[0]);
	
	$.each(languages, function(i, iso){
		
		$('#categoryName-'+iso).on({
			'keyup' : function(){
				urlCheckCategory(iso, true);
			},
			'keydown' : function(){
				urlCheckCategory(iso, true);
			}
		});
		
		$('#categoryUrl-'+iso).on({
			'keyup' : function(){
				urlCheckCategory(iso, false);
			},
			'keydown' : function(){
				urlCheckCategory(iso, false);
			}
		});

		$('#transform-'+iso).on({
			'change' : function(){
				urlCheckCategory(iso, true);
			}
		});
	});

});

function toggleCopy(iso){
	chk = ($('#copy-'+iso).prop('checked'));
	
	$('.item-'+iso).each(function(i, item){
		$(item).prop('disabled', chk);
	});
}

function tab(iso){
	$('.view').css('display', 'none');
	$('#view-'+iso).css('display', '');

	$('.is-tab').removeClass('is-selected');
	$('#tab-'+iso).addClass('is-selected');
}

function urlCheckCategory(iso, d){
	if(!$('#transform-'+iso).prop('checked')) return false;

	if(d){
		url = liveUrlTitle( $('#categoryName-'+iso).val() );
		$('#categoryUrl-'+iso).val(url); 
	}else{
		url = $('#categoryUrl-'+iso).val();
	}

	var id_category = $('#id_category').val();

	var get = $.ajax({
		url: 		'helper/url?id_category='+id_category+'&language='+iso+'&url='+url,
		dataType:	'json'
	}).done(function(d) {
		if(url != d.url){
			$('#categoryUrl-'+iso).val(d.url);
		}else{
			$('#alert-'+iso).css('display', 'none');
			$('#errorUrl-'+iso).val("");
		}
	});

}

function makeMeSortable(ul){
	
	var mySortables = ul.sortable({
		'handle': 'div.handle',
		'stop': function(e, ui) {
			serialMe(ul.attr('id'));
		}
	});
}

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
			'id_category': 	$('#'+ul).attr('id'),
			'ordered': 		all.join('-')
		}
	}).done(function(d) {
		console.log('Serialized '+data)
	});
}

function serialSave(){
	$('#order').submit();
}


function serialAll(mid){
	var tmp = [];
	li = $('#mid-'+mid).children();

	li.each(function(i, e){
		if($('#mid-'+$(e).attr('id'))){
			tmp[i] = {id:$(e).attr('id'), sub:serialAll($(e).attr('id'))};
		}else{
			tmp[i] = {id:$(e).attr('id')};
		}
	});
	
	return tmp;
}

function edit(id_category){
	document.location = './?id_category='+id_category+'&opened=' + opened.join('-');	
}

function openedMemo(){
	$('.opened-memo').val(opened.join('-'));
}

function threadMe(me, level, mid_category){
	
	if($(me).hasClass('opened')){
		$(me).removeClass('opened');
		$('#mid-'+mid_category).css('display', 'none');

		var _opened = [];		
		for(i=0; i<opened.length; i++){
			if(mid_category != opened[i]) _opened.push(opened[i]);
		}
		opened = _opened;
		openedMemo();
	}else{
		$(me).addClass('opened');

		if(mid_category > 0) opened.push(mid_category);
		
		if($('#mid-'+mid_category).css('display') == 'none'){
			$('#mid-'+mid_category).css('display', 'block');
		}else{
			thread(mid_category, (level+1));
		}
	}
}

function thread(mid_category, level){

	var get = $.ajax({
		'url' : 'helper/thread',
		'data': {'mid_category': mid_category, 'level': level}
	});
	
	get.done(function(data) {
		
		$('#mid-'+mid_category).html(data);
			
		var lis = $('#mid-'+mid_category).children('li');
		
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

		makeMeSortable($('#mid-'+mid_category));
	});
}

function removeSelection(){
	if(confirm("Really ?")){
		$('#items').submit();
	}
}














