/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	MEDIA
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaEnableSort(e, around){

	mediaList[e+'-sort'] = new Sortables($(e+'-list'), {
		handle: 'span.move',
	    constrain: true,
	    clone: true,
	    revert: true,
	    opacity: 0.3,
	    onComplete: function(){
			mediaSerialize(e, this);
	    }
	});

	var lis = $$('#'+e+'-list li');

	if(lis.length > 1){
		$(e+'-list').getElement('li.noMedia').setStyle('display', 'none');

		lis.each(function(li){
			if(li.id != '') mediaView(li.getElement('.media-view'), li.id)
		});
	}else{
		$(e+'-list').getElement('li.noMedia').setStyle('display', 'block');
	}
	
	// Declencher les actions pour chaque media-list
	doMediaAction(e, around);
}

function doMediaAction(e, around){

	if(around){
		// Browser
		var button = $(e+'-list').getParent().getElement('.media-picker-embed-choose');
		button.addEvent('click', function(){
			var arrow	 = $(e+'-list').getParent().getElement('.arrow');
			var choosing = $(e+'-list').getParent().getElement('.choosing');
			
		//	console.log(choosing.getStyle('display'));
	
			if(choosing.getStyle('display') == 'none'){
				choosing.setStyle('display', 'block');
				$(e+'-choosing').src = 'media.embed.php?field='+e;
				this.set('html', 'Fermer le navigateur de media');
				arrow.set('src', 'ressource/img/arrow-folder-open.png');
			}else{
				choosing.setStyle('display', 'none');
				$(e+'-choosing').src = 'media.embed.php?n=1';
				this.set('html', 'Choisir des media');
				arrow.set('src', 'ressource/img/arrow-folder-close.png');
			}
		});
	}
	
	// suppresion
	$$('#'+e+'-list li .remove').addEvent('click', function(){
		if($chk(this.getParent().getParent())){
			this.getParent().getParent().destroy();
		}
		mediaSerialize(e, mediaList[e+'-sort']);
	
		if($(e+'-list').getElements('li .media-view').length == 0){
			$(e+'-list').getElement('li.noMedia').setStyle('display', 'block');
		}
	});

	// changer les metas
	$$('#'+e+'-list li .info').addEvent('click', function(){

		editing = $(e+'-list').getParent().getElement('.editing');

		if(editing.getStyle('display') == 'none'){
			editing.setStyle('display', 'block');

			li		= this.getParent().getParent();
			parts	= li.id.split('@@');
		
			$(e+'-iframe').src = 'ressource/lib/media.metadata.fromcontent.php?list='+e+'&url='+parts[1];
		}else{
			editing.setStyle('display', 'none');
			$(e+'-iframe').src = 'ressource/lib/media.metadata.fromcontent.php?off';
		}
	});
}

function mediaCloseMeta(e){
	editing = $(e+'-list').getParent().getElement('.editing');
	editing.setStyle('display', 'none');
}

function mediaSerialize(e){

	var js = [];

	$$('#'+e+'-list li').each(function(li){
		if(li.id != ''){
			parts 	= li.id.split("@@");
	
			js.push({
				'type'	: parts[0], 
				'url'	: parts[1]
			});
		}
	});

   	$(e).value = JSON.encode(js);
}

// Gere l'affichage d'une vignette pour la boite de selection
function mediaView(view, raw){

//	console.log([view, view.getParent(), raw]);

	view.getParent().id = raw;
	
	var parts 	 = raw.split("@@");
		var type = parts[0];
		var url	 = parts[1];

//	console.log([type, url]);

	if(type == "folder"){
		var img = {'src':'ressource/img/media-folder.png', 		'height':128, 'width':128, 'myclass':''};
	}else
	if(type == "pdf"){
		var img = {'src':'ressource/img/media-file_pdf.png', 	'height':128, 'width':128, 'myclass':''};
	}else
	if(type == "flash"){
		var img = {'src':'ressource/img/media-file_flash.png', 	'height':128, 'width':128, 'myclass':''};
	}else
	if(type == "audio"){
		var img = {'src':'ressource/img/media-file_audio.png', 	'height':128, 'width':128, 'myclass':''};
	}else
	if(type == "image"){
		var img = (view.getParent().hasClass('notFound'))
			? {'src':'ressource/img/media-file_file.png', 		'height':128, 'width':128, 'myclass':''}
			: {'src':url, 										'height':'',  'width':'',  'myClass':'shd'};
	}else{
		var img = {'src':'ressource/img/media-file_file.png', 	'height':128, 'width':128, 'myclass':''};
	}

	if(img.myClass != '') view.addClass(img.myClass);

	image = new Image(); 
	image.onload = function(){

		var ratio	= 1;
		var raw 	= {
			src		: this.src,
			height	: this.height,
			width	: this.width,
		};

		// Portrait
		if(raw.height >= raw.width){
			if(raw.height  >= 140){
				img.height	= 140;
				var ratio 	= (img.height / raw.height);
			}else{
				img.height	= raw.height;
			}

			img.width	= Math.round(raw.width * ratio);

		// Paysage
		}else{

			if(raw.width   >= 140){
				img.width	= 140;
				var ratio	= (img.width / raw.width);
			}else{
				img.width	= raw.width;
			}

			img.height	= Math.round(raw.height * ratio);
		}

		imageView = new Element('img', {
			'src' 		: this.src,
			'height'	: img.height,
			'width'		: img.width,
			'styles'	: {
				'margin-top' : Math.round((150 - img.height) / 2)+'px',
				'margin-left': Math.round((150 - img.width)  / 2)+'px'
			}
		}).inject(view.empty());
	}

	if(img.myClass == 'shd'){
		image.src = '/h:140'+img.src+'?&ttloff=45+minutes';
	}else{
		image.src = img.src;
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	RICH TEXT EDITOR
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function setRichEditor(){

	if(!useEditor) return false

	tinyMCE.init({
		mode		: 'exact',
		elements	: textarea,
		theme		: 'advanced',
		plugins		: 'safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',

        remove_script_host		: true,
        convert_urls 			: false,
		theme_advanced_buttons1 : 'mybutton,code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
		theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,|,insertdate,inserttime,preview,|,forecolor,backcolor',
		theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen',
		theme_advanced_buttons4 : 'styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak',
	
		theme_advanced_toolbar_location		: 'top',
		theme_advanced_toolbar_align		: 'left',
		theme_advanced_statusbar_location	: 'bottom',
		theme_advanced_resizing				: false,
	
		// Example content CSS (should be your site CSS)
		content_css		: '/app/helper/tinymce.php',

		// Custom FORMAT
		style_formats 	: MceStyleFormats,

		// Drop lists for link/image/media/template dialogs
	//	template_external_list_url	: "js/template_list.js",
	//	external_link_list_url 		: "js/link_list.js",
	//	external_image_list_url 	: "js/image_list.js",
	//	media_external_list_url 	: "js/media_list.js",
		
		setup : function(ed) {
		    ed.addButton('mybutton', {
		        title : 'Insérer des images',
		        image : 'ressource/img/myb.gif',
		        onclick : function() {
					mediaPicker(ed.id, 'mce');
		        }
		    });
		}
	
	});
}


function removeRichEditor(){
	$$('textarea').each(function(t){
		tinyMCE.execCommand('mceRemoveControl', false, t.id);
	});
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	TABS
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function checkNeedToBeFilled(){

	tabs = $$('.tabset .tab li.do-view');

	$$('.tabset .view-tab').each(function(view, i){
		how = view.getElements('li.needToBeFilled').length;
		if(how > 0){
			tabs[i].addClass('needToBeFilledInside');
		}
	});

}

function addTab(tab){

	var nt = prompt("Nom de l'onglet ", "Nouvel onglet");

	if(nt != null && nt != ''){
		li = new Element('li', {
			'html' 	: nt,
			'class'	: 'do-view is-tab'
		}).inject(tab.getElement('.view-all'), 'before');
		
		var view	= tab.getElements('.view-tab');
		var how		= view.length;
		var last	= view[how-1];

		var view	= new Element('div', {
			'class'	: 'view view-tab',
			'id'	: 'view'+how,
			'styles': {
				'display':'none'
			}
		}).inject(last, 'after');
		
		var label	= new Element('div', {
			'class'	: 'view-label view-label-toggle',
			'styles': {
				'display':'none'
			}
		}).inject(view, 'before');
		
		var label_	= new Element('span', {
			'html'	: nt
		}).inject(label);
		
		var ul		= new Element('ul', {
			'class' : 'is-sortable field-list'
		}).inject(view);

		boot();
		formLayout();

		if(doMove) enableMove();
	}
}


function formLayout(){
	obj = {'tab':{}, 'bottom':[]};

	// Toujours visible
	$$('.field-list-bottom li.form-item').each(function(li, i){
		obj.bottom[i] = {
			'field'		: li.id,
			'close'		: li.hasClass('closed')
		};
	});

	$$('.view-tab').each(function(view, i){
		field = [];
		view.getElements('li.form-item').each(function(li, j){
			field[j] = {
				'field'	: li.id,
				'close'	: li.hasClass('closed')
			};
		});

		e		= {};
		e.label = view.getPrevious().getElement('span').get('text');
		e.field = field;

		eval("obj.tab."+view.id+"=e");
	});

	if($('typeFormLayout')){
		$('typeFormLayout').value 	= JSON.encode(obj);
	}else
	if($('groupFormLayout')){
		$('groupFormLayout').value	= JSON.encode(obj);
	}else{
		alert("Pas de champs pour serialiser JSON");
	}

}

/*function formLayoutSave(url, opt){

	new Request.JSON({
		url: 'ressource/lib/'+url,
		onComplete: function(obj){
		}
	}).post(opt);

}*/

function enableMove(){

	mySortables.removeLists($$('.field-list'));
	mySortables.addLists($$('.field-list'));
	doMove = true;

	$$('.hand').setStyle('visibility', 'visible');
	
	 $('action-move-on').setStyle('display', 'none');
	$('action-move-off').setStyle('display', '');


	removeRichEditor()
}


function disableMove(){
	mySortables.removeLists($$('.field-list'));
	$$('.hand').setStyle('visibility', 'hidden');
	doMove = false;

	 $('action-move-on').setStyle('display', '');
	$('action-move-off').setStyle('display', 'none');

	if($('typeFormLayout')){
		/*formLayoutSave('content.type.layout.php', {
			'id_type' 		 : $('id_type').value,
			'typeFormLayout' : $('typeFormLayout').value
		});*/
	}

	setRichEditor();
}


function openView(id,p){

	$$('.tabset .tab li').removeClass('is-selected');

	if(id == 'ALL'){
		$$('.tabset .view-tab').each(function(view){
			view.setStyle('display', '');
			label = view.getPrevious();
			
			label.setStyle('display', '');
		});

		$$('.tabset .tab li.is-tab').addClass('is-selected');

	}else{
		$$('.tabset .view-tab').setStyle('display', 'none');
		$$('.tabset .view-label-toggle').setStyle('display', 'none');
		$$('.tabset .tab li').removeClass('is-selected');

		$('view'+id).setStyle('display', '');

		$$('.tabset .tab li')[p].addClass('is-selected');
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function tabAction(){
	
	$$('.tabset .tab li').each(function(e, i){

		e.removeEvents('click').removeEvents('dblclick').addEvents({

			'click' : function(){
				if(e.hasClass('view-all')){
					openView('ALL', i);
				}else
				if(e.hasClass('do-view')){
					openView(i, i);
				}
			},

			'dblclick' : function(){
				if(e.hasClass('do-view')){
					nn = prompt("Nom de l'onglet", this.get('html'));
					if(nn != null && nn != ''){
						this.set('html', nn);

						this.getParent().getElements('li').each(function(li, i){
							if(li == e){
								view 	= $('view'+i);
								label	= view.getPrevious().getElement('span');
								if(label) label.set('html', nn);
							}
						});
						
						formLayout();
					}
				}
			}

		});
	});

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	THIS IS THE VERY FIRST START POINT !!!
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function boot(){

	tabAction();

	// Open-Close
	$$('.toggle').each(function(e, i){
		e.addEvent('click', function(){
			this.getParent().toggleClass('closed');
			formLayout();
		});
	});

	// Deplacement des CHAMPS
	mySortables = new Sortables([], {
		handle: 'div.hand',
	    constrain: false,
	    clone: true,
	    revert: true,
	    opacity: 0.7,
	    onComplete: function(){
		    formLayout();
	    }
	});

	/*if($('action-move-on')) 	$('action-move-on').setStyle('display', '');
	if($('action-move-off'))	$('action-move-off').setStyle('display', 'none');*/

	// Pour chaque liste de media (boite de selection)
	mediaList = [];
	$$('.media-list').each(function(e,i){
		mediaList[i] = e.getElement('textarea').id;
	});

	if(mediaList.length > 0){	
		mediaList.each(function(list){
			mediaEnableSort(list, true);
		
			$(list).addEvent('change', function(){
				mediaEnableSort(this.id, true);
			});
		});
	}

	// Remplacer a la volee les LI dans les UL de la LAYOUT
	if(replace.length > 0){
		replace.each(function(e){
			if($(e)){
				$(e).replaces($('replace-'+e));
			}
		});
	}

	setRichEditor();

	if($('contentNameField')){
		$('contentNameField').addEvent('keyup', function(){
			if($('autogen')){
				if($('autogen').checked) urlCheck();
			}else{
				urlCheck();
			}
		});
	}

	// Ajouter les datePicker aux champs DATE
	//
	$$('.datePicker').each(function(d){

		if(d.value == '' || d.value == '0000-00-00') var isEmpty = true;

		var cal = new CalendarEightysix(d, {
			'startMonday'	: true,
			'alignX'		: 'middle',
			'alignY'		: 'top',
			'format'		: '%Y-%m-%d',
		});

		if(isEmpty) d.value = '';

	});
}

function urlCheck(){

	url = liveUrlTitle($('contentNameField').value);
	$('urlField').value = url

	language = ($('language').tagName.toLowerCase() == 'select')
		? $('language').options[$('language').selectedIndex].value
		: $('language').value;

	new Request.JSON({
		url: 'ressource/lib/content.url.php?id_content='+$('id_content').value+'&url='+url+'&language='+language,
		onComplete: function(obj){
			if($('urlField').value != obj.url) $('urlField').value = obj.url;
		}
	}).get();
}

function toggleEditor(id) {
	if (!tinyMCE.getInstanceById(id)){
		tinyMCE.execCommand('mceAddControl', false, id);
	}else{
		tinyMCE.execCommand('mceRemoveControl', false, id);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Les fonctions qui suivent servent a la gestion d'un field CONTENT (tableau)
	ou d'un USER ou d'un DBTABLE (table externe comme source de données)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
tagSearchStorage = {};

function tagInsert(zone, fieldName, id_field, id_type, id, contentName){

	if(id_type == 'dbtable'){
		var prompt = 'db';
	}else
	if(id_type == 'user'){
		var prompt = 'us';
	}else{
		var prompt = 'ct';
	}

	var area = $(zone).getElement('.keyword');
	if($(prompt+'-'+id_field+'-'+id)) return true;

	var key = new Element('div', {
		'class' : 'key',
		'id'	: prompt+'-'+id_field+'-'+id,
		'html'	: contentName
	}).inject(area, 'top');

	var inp = new Element('input', {
		'type'	: 'hidden', 
		'name'	: fieldName+'[]',
		'value'	: id
	}).inject(key, 'top');

	var add = new Element('a', {
		'class'	: 'kill',
		'events': {
			'click' : function(){
				tagRemove(prompt, id_field, id);
			}
		}
	}).inject(key, 'bottom');

}

function tagRemove(prompt, id_field, id){
	if(!$(prompt+'-'+id_field+'-'+id)) return false;
	$(prompt+'-'+id_field+'-'+id).destroy();
}

function tagSearch(id_field, id_type, fieldName){

	var q = $('contenttable-'+id_field).getElement('.field').value;
	var g = {
		'id_field'	: id_field,
		'id_type'	: id_type,
		'q'			: q
	};

	tagSearchRequest(id_field, id_type, fieldName, true, g);
}

function tagSearchStorageGet(id_field, k){

	if(eval('tagSearchStorage.itm'+id_field) == undefined){
		eval('tagSearchStorage.itm'+id_field+'= {clean:true}');
		return '';
	}

	return eval('tagSearchStorage.itm'+id_field+'.'+k);
}

function tagSearchStorageSet(id_field, k, v){
	if(eval('tagSearchStorage.itm'+id_field) == undefined) return '';
	
	eval('tagSearchStorage.itm'+id_field+'.'+k+'=v');
	
	return true;
}

function tagSearchRequest(id_field, id_type, fieldName, clean, getVar){

	if(id_type == 'dbtable'){
		tagId 		= 'dbtable_id';
		tagView		= 'dbtable_view';
	}else
	if(id_type == 'user'){
		tagId 		= 'id_user';
		tagView		= 'userMail';
	}else{
		tagId 		= 'id_content';
		tagView		= 'contentName';
	}

	var q = $('contenttable-'+id_field).getElement('.field').value;
	var t = $('contenttable-'+id_field).getElement('table.listing tbody');

	// On supprime les lignes actuelles
	if(clean){
		t.getElements('tr').destroy();

		// On indique qu'on va effectuer une recherche
		new Element('td', {
			'colspan'	: 2,
			'class'		: 'tagListingSearch',
			'html'		: 'recherche en cours ...'
		}).inject(new Element('tr').inject(t));
	}

	// + le lien MORE en dessous
	if($('morexqr-'+id_field)) $('morexqr-'+id_field).destroy();

	// Est-ce que j'utiliser deja ce XQR ?
	
	if(tagSearchStorageGet(id_field) == ''){
	//	console.log("Creation du storage");

		// On lance la recherche
		var xqr = new Request.JSON({
			'url'			: 'ressource/lib/field.multitag.php',
			'chain'			: 'cancel',
			'onComplete'	: function(r){

				var a = r.result;
	
				// On supprime les lignes actuelles
				if(tagSearchStorageGet(id_field, 'clean')){
				//	console.log('clean');
					t.getElements('tr').destroy();
				}

				// le lien MORE en dessous
				if($('morexqr-'+id_field)) $('morexqr-'+id_field).destroy();
	
				if(a.length > 0){
					a.each(function(e){
						var line = new Element('tr').inject(t);

						var push = new Element('td').set('width', 25).inject(line);
						var anch = new Element('a', {
							html 	: "<img src=\"ressource/img/picto-add.png\" />",
							events	: {
								click : function(){
									tagInsert('contenttable-'+id_field, fieldName, id_field, id_type, eval('e.'+tagId), eval('e.'+tagView));
								}
							}
						}).inject(push);

						var name = new Element('td', {
							'html' : eval('e.'+tagView)
						}).inject(line);
					});

					if(r.more){
						new Element('a', {
							class	: 'more',
							id		: 'morexqr-'+id_field,
							html	: 'Plus de r&eacute;sultats',
							events	: {
								click : function(){
									tagSearchRequest(id_field, id_type, name, false, {
										'id_field' 	: id_field,
										'id_type' 	: id_type,
										'offset'	: r.next,
										'q'			: q
									});
								}
							}
						}).inject(t.getParent().getParent().getParent().getElement('.head .search'), 'bottom');
					}

				}else{
					new Element('td', {
						'colspan'	: 2,
						'class'		: 'tagListingSearch',
						'html'		: 'Aucun r&eacute;sultat'
					}).inject(new Element('tr').inject(t, 'bottom'));
	
				}
	
			}
		}).get(getVar);

		tagSearchStorageSet(id_field, 'xqr', xqr);

	}else{
	//	console.log("Je reutilise ce qui existe");

		tagSearchStorageSet(id_field, 'clean',	clean);

		xqr = tagSearchStorageGet(id_field, 'xqr');
		xqr.get(getVar);
	}
}
















