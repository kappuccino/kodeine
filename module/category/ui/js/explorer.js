/*(function(){
	$$('.cat-explorer').each(function(xp){
		mode = xp.hasClass('multi') ? 'multi' : 'solo';
		explorer(xp, mode, name, 0, 0, '');
	});
});*/

/*	@xp : $()
 * 
 */
function explorerKeyAdd(xp, mode, name, id_category, html, parents){

	if(mode == 'multi'){
		var keywords = xp.prev('.explorer-keyword');
		var found	 = false;
	
		keywords.find('input').each(function(kw){
			if(kw.val() == id_category) found = true;
		});
		
		if(found) return true;




		var li = $('<li class="key clearfix" />').appendTo('bottom');
		//var li = new Element('li', {'class': 'key clearfix'}).inject(keywords, 'bottom');

			$('<input type="hidden" name="id_category[]" value="'+id_category+'" />').appendTo(li);
			/*new Element('input', {
				'type'	: 'hidden',
				'name'	: 'id_category[]',
				'value' : id_category
			}).inject(li);*/
	
			$('<span />').html(html).on('click', function() {
				explorerShow(mode, name, this, parents);
			}).appendTo(li);
			
			/*new Element('span',{
				'html'   : html,
				'events' : {
					'click' : function(){ explorerShow(mode, name, this, parents); }
				}
			}).inject(li);*/
			
			$('<a class="kill" />').on('click', function() {
				explorerKeyRemove(this);
			}).appendTo(li);
			
			/*new Element('a', {
				'class'  : 'kill',
				'events' : {
					'click': function(){ explorerKeyRemove(this); }
				}
			}).inject(li);*/

	}else{
		if($(name)){
			$(name).val(id_category);
		}else{
			alert('Field not found ('+name+')');
		}
	}

}

function explorerKeyRemove(a){
	a.parent().remove();
}

function explorerCleanRight(xp, wrapp, level){

	var ulIn = xp.find('ul');

	if(ulIn.length > level){
		for(var i=level; i<ulIn.length; i++){
			$(ulIn[i]).remove();
		}
	}

}

function explorerShow(mode, name, me, path){

	var keys	= me.find('.explorer-keyword');
	var xp		= keys.next();

	xp.find('.wrapp').empty();

	explorer(xp, mode, name, 0, 0, path);
}

function explorerIsGoNext(xpath, mid_category){

	if(xpath.length > 0){
		var found = false;
		for(i=0; i<xpath.length; i++){
			if(xpath[i] == mid_category) found = true;
		}
		return found;
	}else{
		return false;
	}

}

function explorer(xp, mode, name, mid_category, level, path){

	var wrapp = xp.find('.wrapp');
	
	if(!wrapp){
		/*var wrapp = new Element('div', {
			'class' : 'wrapp clearfix'
		}).inject(xp);*/
		
		var wrapp = $('<div class="wrapp clearfix" />').appendTo(xp);
	}

	var xpath = path.split(',');

	explorerCleanRight(xp, wrapp, level);

	var get = $.ajax({
		url : 'helper/explorer',
		data : {'mid_category':	mid_category}
	});
	
	get.done(function(data) {
		if(data.length >  0){
			
			var ul = $('<ul />').appendTo(wrapp);

			$.each(data, function(i, item){
				
				var li = $('<li class="clearfix" />').appendTo(ul);
				/*var li = new Element('li', {
					'class' : 'clearfix	'
				}).inject(ul);*/

				var label = $('<div class="label" />').html(item.name).on('click', function() {
					xp.find('.selection').removeClass('selection');
					li.addClass('selection');
					explorerKeyAdd(xp, mode, name, item.id_category, item.name, item.parent);
				}).appendTo(li)
	
				/*var label = new Element('div', {
					'class': 'label',
					'html' : item.name,
					'events': {
						'click': function(){
							
							xp.getElements('.selection').removeClass('selection');
							li.addClass('selection');

							explorerKeyAdd(xp, mode, name, item.id_category, item.name, item.parent);
						}
					}
				}).inject(li);*/

				if(name != null){
					if(item.id_category == $(name).val()) li.addClass('selection');
				}

				if(item.hasChildren){
					/*var act = new Element('div', {
						'class': 'action',
						'events': {
							'click' : function(){
								this.getParent().getParent().getElements('.opened').removeClass('opened');
								if(!this.getParent().hasClass('selection')) this.getParent().addClass('opened');
								explorer(xp, mode, name, item.id_category, (level+1), '');
							}
						}
					}).inject(li);*/
					
					var act = $('<div class="action" />').on('click', function() {
						this.parent().parent().find('.opened').removeClass('opened');
						if(!$(this).parent().hasClass('selection')) $(this).parent().addClass('opened');
						explorer(xp, mode, name, item.id_category, (level+1), '');
					})

					// RELOAD
					if(explorerIsGoNext(xpath, item.id_category)){
						li.parent().find('.opened').removeClass('opened');
						if(!li.hasClass('selection')) li.addClass('opened');
						explorer(xp, mode, name, item.id_category, (level+1), path);
					}
				}
			});


			// GUI
			wrappWidth = 0;
			xp.find('ul').each(function(i, ul){
				wrappWidth += $(ul).css('width') + 20;
			});
			wrapp.css('width', wrappWidth+'px');
			/*xp.scrollTo(wrappWidth*2, 0);*/
		}
	});

}
