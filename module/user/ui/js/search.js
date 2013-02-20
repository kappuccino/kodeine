
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
$(function() {

	if (typeof param_ !== 'undefined') {
		
		if(param_.length == 0){
			addLine($('#param'));
		}else{
			afficher($('#param'), param_);
		}
	}
});

// @flemmard, mootools emulation :p
var $chk = function(obj){
    return !!(obj || obj === 0);
};

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function afficher(parent, param){
	
	$.each(param, function(i, e){
		li = addLine(parent, e.searchField, e.searchMode, e.searchValue);
		
		if(typeof e.searchParam == 'array'){
			nc = newCond(li, e.searchChain);
			afficher(nc, e.searchParam);
		}
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function addLine(parent, searchField, searchMode, searchValue){
	
//	li 		= new Element('li',   {'class': 'line'}).inject(parent, 'bottom');
//	trigger	= new Element('span', {'class': 'trigger'}).inject(li, 'top');
//	action	= new Element('span', {'class': 'action'}).inject(li, 'bottom');

	li = $('<li class="line" />').appendTo(parent);
	
	trigger = $('<span class="trigger" />').prependTo(li);
	action = $('<span class="action" />').appendTo(li);

	rem = $('<a href="#"><i class="icon-remove-sign"></i></a>').on('click', function() {
		if(parent.children('li').length > 1){
			$(this).parent().parent().remove();
		}
		resetMainChain();
	}).appendTo(trigger);
	
	add = $('<a href="#"><i class="icon-plus-sign"></i></a>').on('click', function() {
		nl = addLine($(this).parent().parent().parent());
		changeField(nl.children('select'));
	}).appendTo(trigger);
	
	cond = $('<a href="#"><i class="icon-wrench"></i></a>').on('click', function() {
		nc = newCond($(this).parent().parent());
		addLine(nc);
	}).appendTo(trigger);
	
	select = $('<select class="is-field" style="margin-left: 10px;" />').on('change', function() {
		changeField($(this));
	}).appendTo(trigger);
	
	$.each(field_, function(i, f) {
		opt = $('<option value="'+f.id_field+'">'+f.fieldName+'</option>').appendTo(select)
		if($chk(searchField)){
			if(searchField == f.id_field) select.prop('selectedIndex', i);
		}
	});
	
	/*cond = new Element('a', {
		'html': '<img src="ressource/img/cond.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				nc = newCond(this.getParent().getParent());
				addLine(nc);
			}
		}
	}).inject(trigger);	*/
	
	/*rem	= new Element('a', {
		'href': '#',
		'html': '<img src="ressource/img/remove.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				if(parent.getElements('li').length > 1){
					this.getParent().getParent().destroy();
				}

				resetMainChain();
			}
		}
	}).inject(trigger);*/

	/*add	= new Element('a', {
		'href': '#',
		'html': '<img src="ressource/img/add.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				nl = addLine(this.getParent().getParent().getParent());
				changeField(nl.getElement('select'));
			}
		}
	}).inject(trigger);*/

	/*select = new Element('select', {
		'class' : 'is-field',
		'events' : {
			'change' : function(){
				changeField(this);
			}
		}
	}).inject(trigger); */
	
	/*field_.each(function(f,i){
		select.options[i] = new Option(f.fieldName, f.id_field);
		if($chk(searchField)){
			if(searchField == f.id_field) select.selectedIndex = i;
		}
	});*/

//	console.log("------- NOUVELLE LI :"+Math.random(), li);

	changeField(select, searchMode, searchValue);
	
	// Main CHAIN
	resetMainChain();
	
	return li;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function resetMainChain(){
	if($('#param').children().length > 1){
		$('#mainChain').css('visibility', 'visible');
	}else{
		$('#mainChain').css('visibility', 'hidden');
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function save(){
	saveBLD($('#param'), 0, 'searchParam');
	$('#f').submit();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function saveBLD(parent, n, name){

	if($chk(parent)){
	
		/*li = [];
		parent.children('li').each(function(i, m){
			
			//if($(m).parent() == parent) {
				li.push($(m));
			//}
		});
		if(li.length == 0) return true;*/
					
		name_ = name;
		
		parent.children('li').each(function(i, m){
			
			name_ 		= name+"["+i+"][searchParam]";

			field_		= $(m).find('span .is-field');
			mode_		= $(m).find('span .is-mode');
			value_		= $(m).find('span .is-value');

			if(field_)	field_.attr('name', name+"["+i+"][searchField]");
			if(mode_)	mode_.attr('name', name+"["+i+"][searchMode]");

			if(value_.length > 0){
				if(value_.is(":checkbox")){
					value_.parent().children('input').each(function(i, me){
						$(me).attr('name', name+"["+i+"][searchValue][]");
					});
				}else{
					value_.attr('name', name+"["+i+"][searchValue]");
				}
			}

			nextParent 	= $(m).children('ul');

			if(nextParent.length > 0){
				// Renommer le champs CHAIN pour le LI courant
				cond = nextParent.prev().children('select');
				if($chk(cond)) cond.attr('name', name+"["+i+"][searchChain]");
				
				// La suite
				saveBLD(nextParent, (n+1), name_);
			}

		});
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function newCond(line, chain){
	
	join = $('<div class="before-cond" />').appendTo(line);
	
	rem = $('<a href="#"><i class="icon-remove-sign"></i></a>').on('click', function() {
		$(this).parent().next().remove();
		$(this).parent().remove();
		resetMainChain();
	}).prependTo(join);
	
	menu = $('<select class="foo-sel" />').appendTo(join);
	opt1 = $('<option value="AND">Toutes les</option>').appendTo(menu);
	opt2 = $('<option value="OR">N\'importe laquelle des</option>').appendTo(menu);
	
	sel = (chain == 'AND') ? 0 : 1;
	menu.prop('selectedIndex', sel);

	sp = $('<span>régles suivantes</span>').insertAfter(menu);
	cond = $('<ul class="is-cond" style="padding-left: 20px;"/>').appendTo(line)	

	return cond;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function changeField(f, searchMode, searchValue){
	
	id_field = f.val();
	
	if(!$chk(id_field)) return true;

	line = f.parent().parent();

	json = $.ajax({
		url : '../core/helper/search-json?id_field='+id_field,
		dataType : 'json'
	});

	json.done(function(r) {
		buildAction(line, r, searchMode, searchValue);
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function buildAction(line, r, searchMode, searchValue){
	//console.log([line, r, searchMode, searchValue]);
	// Nettoyage ...
	//
	e = line.children('.action');
	
	if(e.length > 0) e.empty();

	// On s'occupe du MODE
	//
	if($chk(r.mode)){
		mode = $('<select class="is-mode nomargin" />');
		for(i=0; i<r.mode.length; i++){
			opt = $('<option value="'+r.mode[i]+'">'+r.mode[i]+'</option>').appendTo(mode);
			if($chk(searchMode) && r.mode[i] == searchMode) mode.prop('selectedIndex', i);
		}
		mode.prependTo(e);
	}
	
	// On s'occupe du VALUE
	if($chk(r.choice)){
		if(r.field.fieldType == 'multichoice'){
			for(i=0; i<r.choice.length; i++){
				chk = $('<input type="checkbox" class="is-value" value="'+r.choice[i].choiceValue+'" />').appendTo(e);
				label = $('<span>'+r.choice[i].choiceValue+'</span>').appendTo(e);
				
				//chk 	= new Element('input', {type: 'checkbox', 'class' : 'is-value', 'value' : r.choice[i].choiceValue}).inject(e);
				//label	= new Element('span', {html: r.choice[i].choiceValue}).inject(e);

				if($chk(searchValue)){
					for(j=0; j<searchValue.length; j++){
						if(searchValue[j] == r.choice[i].choiceValue) chk.prop('checked', true);
					}
				}
			}
		}else
		if(r.field.fieldType == 'onechoice'){
			mode = $('<select class="is-value" />');
			for(i=0; i<r.choice.length; i++){
				opt = $('<option value="'+r.choice[i].choiceValue+'">'+r.choice[i].choiceValue+'</option>').appendTo(mode);
				if($chk(searchValue) && searchValue == r.choice[i].choiceValue) mode.prop('selectedIndex', i);
			}
			mode.appendTo(e);
		}

	}else{
		val = $('<input class="is-value" style="margin-left: 10px;" />');
		if($chk(searchValue)) val.attr('value', searchValue);
		val.appendTo(e);
	}
}
