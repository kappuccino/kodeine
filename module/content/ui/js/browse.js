/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function first(hash){
	newPanel(null, 0, 0);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function newPanel(caller, index, id){
	cleanPanelAfter(index);

	if($(caller).length > 0){
		if(caller.tagName == 'A'){
			caller = $(caller).parent().parent();
		}

		cleanSelected(caller);
		caller.addClass('me');
	}

	var get = $.ajax({
		url: 'helper/browse',
		dataType: 'json',
		data: {	'id_type': id_type, 'id_category': id}
	});
	
	get.done(function(d) {
		ul = $('<ul class="panel" id="cat-'+id+'" style="display:none" />').appendTo('#holder');
		
		if(d.category.length > 0){
			$.each(d.category, function(i, d){
				
				li = $('<li id="'+d.id_category+'" />').appendTo(ul);
				sp = $('<span />').appendTo(li);
				an = $('<a href="#'+d.categoryParent+','+d.id_category+'">'+d.categoryName+'</a>').appendTo(sp).bind('click', function(e) {
					$('#form_hash').val(d.categoryParent+','+d.id_category);
					newPanel(this, (index+1), d.id_category);
				});

				if(d.hasSub){
					if(d.hasContent){
						li.addClass('hasSubAndContent');
					}else{
						li.addClass('hasSub');
					}
				}else{
					if(d.hasContent) li.addClass('hasJusContent');
				}
			});
		}
		
		$('#hasData').css('display', 'none');
		$('#noData').css('display',  'none');
		$('#view').find('thead').css('display',  'none');
		$('#view').find('tfoot').css('display', 'none');
		
		if(d.content.length > 0){
			$('#hasData').css('display', '');
			$('#view').find('thead').css('display', '');
			$('#view').find('tfoot').css('display', '');
			
			$('#hasData tr').remove();

			$.each(d.content, function(i, c){
				
				tr = $('<tr/>').appendTo('#hasData')
				$('<input type="checkbox" name="remove[]" class="cb" value="'+c.id_content+'" />').appendTo($('<td />').appendTo(tr));
				
				td = $('<td />').appendTo(tr);
				$('<input type="hidden" name="see['+c.id_content+']" value="0" />').appendTo(td);
				$('<input type="checkbox" name="see['+c.id_content+']" value="1" class="cs" checked="'+(c.contentSee == '1')+'" />').appendTo(td);
				// pardon...
				$('<a href="javascript:duplicate('+c.id_content+','+id_type+',\''+getHash()+'\')"><i class="icon-tags"></i></a>').appendTo($('<td />').appendTo(tr));
				tdLan = $('<td/>').appendTo(tr);
				
				if(c.lan.length > 0){
					
					$.each(c.lan, function(i, lan){
						$('<a class="button button-blue" href="../content/language">'+lan.toUpperCase()+'</a>').appendTo(tdLan);
					});
				}

				$('<a href="content.data.php?id_content='+c.id_content+'">'+c.id_content+'</a>').appendTo($('<td/>').appendTo(tr));
				$('<a>'+c.contentDateCreation+'</a>').appendTo($('<td/>').appendTo(tr));
				$('<a>'+c.contentDateUpdate+'</a>').appendTo($('<td />').appendTo(tr));
				$('<a href="content.data.php?id_content='+c.id_content+'">'+c.contentName+'</a>').appendTo($('<td />').appendTo(tr));
			});
		}else{
			$('#noData').css('display', '');
		}
		
		ul.css('display', 'block');

		$('#holder').css('width', ((($('#browse ul').length) * 200))+'px');
		
		pos	  	= $('#browse').position();
		visible = pos.left+$('#browse').width();
//		visible = $('#browse').getCoordinates().right;

		need	= $('#holder').css('width');
		delta	= (need > visible) ? ((need - visible) * -1) : 0;
		$('#browse').css('padding-left', delta+'px');

		// Loop auto populate
		if(todo.length > 0 && todoC < todo.length){	
			newPanel($(todo[todoC]), (index+1), todo[todoC]);
			todoC += 1;
		}
	});

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function cleanPanelAfter(index){
	$('#browse').find('ul').each(function(i, ul){
		if(i+1 > index) $(ul).remove();
	});
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function cleanSelected(caller){
	caller.parent().find('li').removeClass('me');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function getHash() {
	var href = top.location.href;
	var pos = href.indexOf('#') + 1;
	return (pos) ? href.substr(pos) : '';
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function duplicate(id, type, hash){
	if(confirm("DUPLIQUER ?")){
		document.location='browse?id_type='+type+'&duplicate='+id+'&hash='+hash;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function remove(){
	if(confirm("Confirmez-vous les changements sur la selection ?")){
		$('#listing').submit();
	}
}
