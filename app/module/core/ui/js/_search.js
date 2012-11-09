
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
window.addEvent('domready', function(){
	if(param_.length == 0){
		addLine($('param'));
	}else{
		afficher($('param'), param_);
	}
});

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function afficher(parent, param){
	param.each(function(e){
		li = addLine(parent, e.searchField, e.searchMode, e.searchValue);
		
		if($type(e.searchParam) == 'array'){
			nc = newCond(li, e.searchChain);
			afficher(nc, e.searchParam);
		}
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function addLine(parent, searchField, searchMode, searchValue){

	li 		= new Element('li',   {'class': 'line'}).inject(parent, 'bottom');
	trigger	= new Element('span', {'class': 'trigger'}).inject(li, 'top');
	action	= new Element('span', {'class': 'action'}).inject(li, 'bottom');

	rem	= new Element('a', {
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
	}).inject(trigger);

	add	= new Element('a', {
		'href': '#',
		'html': '<img src="ressource/img/add.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				nl = addLine(this.getParent().getParent().getParent());
				changeField(nl.getElement('select'));
			}
		}
	}).inject(trigger);

	cond = new Element('a', {
		'html': '<img src="ressource/img/cond.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				nc = newCond(this.getParent().getParent());
				addLine(nc);
			}
		}
	}).inject(trigger);		

	select = new Element('select', {
		'class' : 'is-field',
		'events' : {
			'change' : function(){
				changeField(this);
			}
		}
	}).inject(trigger);
		field_.each(function(f,i){
			select.options[i] = new Option(f.fieldName, f.id_field);
			if($chk(searchField)){
				if(searchField == f.id_field) select.selectedIndex = i;
			}
		});

//	console.log("------- NOUVELLE LI :"+Math.random(), li);

	changeField(select, searchMode, searchValue);
	
	// Main CHAIN
	resetMainChain();
	
	return li;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function resetMainChain(){
	if($('param').getChildren().length > 1){
		$('mainChain').setStyle('visibility', 'visible');
	}else{
		$('mainChain').setStyle('visibility', 'hidden');
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function save(){
	saveBLD($('param'), 0, 'searchParam');
	$('f').submit();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function saveBLD(parent, n, name){

	if($chk(parent)){
	
		li = [];
		parent.getElements('li').each(function(m){
			if(m.getParent() == parent) li.push(m);
		});

		if(li.length == 0) return true;
					
		name_ = name;
		
		li.each(function(m,i){
			name_ 		= name+"["+i+"][searchParam]";

			field_		= m.getElement('.is-field');
			mode_		= m.getElement('.is-mode');
			value_		= m.getElement('.is-value');

			if(field_)	field_.name = name+"["+i+"][searchField]";
			if(mode_)	mode_.name	= name+"["+i+"][searchMode]";

			if(value_){
				if(value_.type == 'checkbox'){
					value_.getParent().getElements('input').each(function(me){
						me.name = name+"["+i+"][searchValue][]";
					});
				}else{
					value_.name = name+"["+i+"][searchValue]";
				}
			}

			nextParent 	= m.getElement('ul');
			if(nextParent){
				// Renommer le champs CHAIN pour le LI courant
				cond = nextParent.getPrevious().getElement('select');
				if($chk(cond)) cond.name = name+"["+i+"][searchChain]";
				
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
	
	join = new Element('div', {
		'class' : 'before-cond'
	}).inject(line, 'bottom');

	rem  = new Element('a', {
		'html': '<img src="ressource/img/remove.png" align="absmiddle" height="20" width="20" /> ',
		'events' : {
			'click' : function(){
				this.getParent().getNext().destroy();
				this.getParent().destroy();
				resetMainChain();
			}
		}
	}).inject(join, 'top');

	menu = new Element('select').inject(join);
		menu.options[0] = new Option('Toutes les', 'AND');
		menu.options[1] = new Option('N\'importe laquelle des', 'OR');
	
		menu.selectedIndex = (chain == 'AND') ? 0 : 1;
	
	sp = new Element('span', {'html' : ' règles suivantes '}).inject(menu, 'after');
	
	cond = new Element('ul', {'class' : 'is-cond'}).inject(line, 'bottom');

	return cond;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function changeField(f, searchMode, searchValue){

	id_field = f.options[f.selectedIndex].value;
	if(!$chk(id_field)) return true;

	line = f.getParent().getParent();

	json = new Request.JSON({
		url : 'ressource/lib/search.json.php?id_field='+id_field,
		method : 'get',
		noCache: true,
		async: false,
		onComplete : function(r){
		//	console.log(["JSON COMPLETE", r]);
			buildAction(line, r, searchMode, searchValue);
		}
	}).get();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function buildAction(line, r, searchMode, searchValue){
	//console.log([line, r, searchMode, searchValue]);

	// Nettoyage ...
	//
	e = line.getElement('.action');
	if(e) e.empty();

	// On s'occupe du MODE
	//
	if($chk(r.mode)){
		mode = new Element('select', {'class' : 'is-mode'});
		for(i=0; i<r.mode.length; i++){
			mode.options[i] = new Option(r.mode[i], r.mode[i]);
			if($chk(searchMode) && r.mode[i] == searchMode) mode.selectedIndex = i;
		}
		mode.inject(e);
	}
	
	// On s'occupe du VALUE
	if($chk(r.choice)){
		if(r.field.fieldType == 'multichoice'){
			for(i=0; i<r.choice.length; i++){
				chk 	= new Element('input', {type: 'checkbox', 'class' : 'is-value', 'value' : r.choice[i].choiceValue}).inject(e);
				label	= new Element('span', {html: r.choice[i].choiceValue}).inject(e);

				if($chk(searchValue)){
					for(j=0; j<searchValue.length; j++){
						if(searchValue[j] == r.choice[i].choiceValue) chk.checked = true;
					}
				}
			}
		}else
		if(r.field.fieldType == 'onechoice'){
			mode = new Element('select', {'class' : 'is-value'});
			for(i=0; i<r.choice.length; i++){
				mode.options[i] = new Option(r.choice[i].choiceValue, r.choice[i].choiceValue);
				if($chk(searchValue) && searchValue == r.choice[i].choiceValue) mode.selectedIndex = i;
			}
			mode.inject(e);
		}

	}else{
		val = new Element('input', {'class' : 'is-value'});
		if($chk(searchValue)) val.value = searchValue;
		val.inject(e);
		
	}
}
