/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	MEDIA
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
/*
$(window).keydown(function(event) {
    if (!(event.which == 83 && event.ctrlKey) && !(event.which == 83)) return true;
    $('#data').submit();
    event.preventDefault();
    return false;
});
*/
function mediaEnableSort(e, around){
	mediaList[e+'-sort'] = $(e+'-list').sortable({
		handle: 'span.move',
		containment : 'parent',
		tolerance: 'pointer',
		placeholder: '',
	    //constrain: true,
	    //clone: true,
	    //revert: true,
	    //opacity: 0.3,
	    stop: function(){
			mediaSerialize(e, this);
	    }
	});

	var lis = $(e+'-list li');

	if(lis.length > 1){
		$(e+'-list').find('li.noMedia').css('display', 'none');

		lis.each(function(i, li){
			if(typeof $(li).attr('id') != 'undefined') mediaView($(li).find('.media-view'), $(li).attr('id'))
		});
	}else{
		$(e+'-list').find('li.noMedia').css('display', 'block');
	}
	
	// Declencher les actions pour chaque media-list
	doMediaAction(e, around);
}

function doMediaAction(e, around){
	if(around){
		// Browser
		var button = $(e+'-list').parent().find('.media-picker-embed-choose');
		
		button.click(function(evnt){
			evnt.preventDefault();
			
			var arrow	 = $(e+'-list').parent().find('.arrow');
			var choosing = $(e+'-list').parent().find('.choosing');
			
			if(choosing.css('display') == 'none'){

				choosing.css('display', 'block');
				
				/* /!\ ATTENTION !! On passe l'id #contentMedia a ?field
				 * mais le # fout en l'air la variable. Exploser le premier
				 * char de la chaine, le remettre après sur embed.php
				 */
				
				$(e+'-choosing').attr('src', '../media/?embed&method=sort-embed&field='+e.substr(1, e.length));
				$(this).html('Fermer le navigateur de media');
				arrow.attr('src', '../core/ui/img/_img/arrow-folder-open.png');
			}else{
				
				choosing.css('display', 'none');
			//	$(e+'-choosing').attr('src', '../media/?embed&method=sort-embed&field='+e.substr(1, e.length));
				$(this).html('Choisir des media');
				arrow.attr('src', '../core/ui/img/_img/arrow-folder-close.png');
			}
		});
	}
	
	// suppresion
	$(e+'-list li .remove').bind('click', function(){
		if($(this).parent().parent()){
			$(this).parent().parent().remove();
		}
		mediaSerialize(e, mediaList[e+'-sort']);
	
		if($(e+'-list').find('li .media-view').length == 0){
			$(e+'-list').find('li.noMedia').css('display', 'block');
		}
	});

	// changer les metas
	$(e+'-list li .info').bind('click', function(){

		editing = $(e+'-list').parent().find('.editing');

		if(editing.css('display') == 'none'){
			editing.css('display', 'block');

			li		= $(this).parent().parent();
			parts	= li.attr('id').split('@@');
		
			// tuer le hash de l'id pour pas casser l'url
			reg = new RegExp('(#)', "g");
			nohash = e.replace(reg, '');
		
			$(e+'-iframe').attr('src', '../media/helper/metadata-fromcontent?list='+nohash+'&url='+parts[1]);
		}else{
			editing.css('display', 'none');
			$(e+'-iframe').attr('src', '../media/helper/metadata-fromcontent?off');
		}
	});
	
}

function mediaCloseMeta(e){
	editing = $(e+'-list').parent().find('.editing');
	editing.css('display', 'none');
}

function mediaSerialize(e){


	var js = [];

	$(e+'-list li').each(function(i, li){
		
		if(typeof $(li).attr('id') != 'undefined'){
			parts 	= $(li).attr('id').split("@@");
	
			js.push({
				'type'	: parts[0], 
				'url'	: parts[1]
			});
		}
	});

   	$(e).val(JSON.stringify(js));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
Gere l'affichage d'une vignette pour la boite de selection
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function mediaView(view, raw){

//	console.log([view, view.getParent(), raw]);
	view.parent().attr('id', raw);

	var parts = raw.split("@@")
		, type = parts[0]
		, url = parts[1]
		, cache = view.parent().attr('data-cache')
		, img;

	if(type == "folder"){
		img = {'src':'../media/ui/img/media-folder.png', 'height':128, 'width':128, 'myclass':''};
	}else
	if(type == "pdf"){
		img = {'src': '../media/ui/img/media-file_pdf.png', 'height': 128, 'width': 128, 'myclass': ''};
	}else
	if(type == "flash"){
		img = {'src': '../media/ui/img/media-file_flash.png', 'height': 128, 'width': 128, 'myclass': ''};
	}else
	if(type == "audio"){
		img = {'src': '../media/ui/img/media-file_audio.png', 'height': 128, 'width': 128, 'myclass': ''};
	}else
	if(type == "image"){
		img = (view.parent().hasClass('notFound'))
			? {'src': '../media/ui/img/media-file_file.png', 'height': 128, 'width': 128, 'myclass': ''}
			: {'src': url, 'height': '', 'width': '', 'myClass': 'shd'};
	}else{
		img = {'src': '../media/ui/img/media-file_file.png', 'height': 128, 'width': 128, 'myclass': ''};
	}

	if(img.myClass != '') view.addClass(img.myClass);

	image = new Image(); 
	image.onload = function(){
		
		var ratio	= 1;
		var raw 	= {
			src		: this.src,
			height	: this.height,
			width	: this.width
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
		
		_styles   = 'margin-top: '+Math.round((150 - img.height) / 2)+'px; margin-left: '+Math.round((150 - img.width)  / 2)+'px';
		imageView = $('<img src="'+this.src+'" height="'+img.height+'" width="'+img.width+'" style="'+_styles+'" />').on('mousedown', function() {return false;}).appendTo(view.empty());
		
	}

	if(img.myClass == 'shd'){
	//image.src = '/h:140'+img.src+'?&ttloff=45+minutes';
		image.src = cache;
	}else{
		image.src = img.src;
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	RICH TEXT EDITOR
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function setRichEditor(selector){
	// si selector est fourni, init un CKEditor sur cet item
	// plutot que le "textarea" global

	var ck_selector = textarea;

	if (!useEditor) return false
	// si on fourni un selecteur custom, ne pas utiliser "textarea"
	if (selector) ck_selector = selector;

	var splitTest = ck_selector.split(',');
	if (splitTest.length > 1) {
		$.each(splitTest, function(i, elem) {
			// recurser les textarea a CKEditoriser
			setRichEditor(elem)
		});

		return;

	} else {

		$( '#'+ck_selector ).ckeditor({
			contentsCss: '../core/helper/ckeditor',
			allowedContent: true
		});

	}

	// charger une ressource externe (plugin) et l'initialiser dans l'instance de notre ckeditor
	var editor = CKEDITOR.instances[ck_selector];
	CKEDITOR.plugins.addExternal('kodeineimg', '/admin/core/vendor/ckeditor-plugins/kodeineimg/', 'plugin.js');
	CKEDITOR.plugins.load('kodeineimg', function(plugins) {
		if(editor) plugins['kodeineimg'].init(editor)
	});

}

function removeRichEditor() {
	for (var id in CKEDITOR.instances) {
		CKEDITOR.instances[id].destroy();
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	TABS
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function checkNeedToBeFilled(){

	tabs = $('.tabset .tab li.do-view');

	$('.tabset .view-tab').each(function(i, view){
		if($(view).find('li.needToBeFilled').length > 0){
			tabs[i].addClass('needToBeFilledInside');
		}
	});
	
}

function addTab(tab){

	var nt = prompt("Nom de l'onglet ", "Nouvel onglet");
	
	if(nt != null && nt != ''){
		
		li = $('<li class="do-view is-tab" />').html('<span class="text">'+nt+'</span><span class="edit"></span><span class="remove"></span><span class="handle"></span>');
		li.insertBefore($(tab).find('.view-all'));
		
		/*li = new Element('li', {
			'html' 	: '<span class="text">'+nt+'</span><span class="edit"></span><span class="remove"></span><span class="handle"></span>',
			'class'	: 'do-view is-tab'
		}).inject(tab.getElement('.view-all'), 'before');*/

		var view	= $(tab).find('.view-tab');
		var how		= view.length;
		var last	= view[how-1];

		var view = $('<div class="view view-tab" id="view'+how+'" style="display: none;" />').insertAfter($(last));

		/*var view	= new Element('div', {
			'class'	: 'view view-tab',
			'id'	: 'view'+how,
			'styles': {
				'display':'none'
			}
		}).inject(last, 'after');*/
		$('<div class="view-label view-label-toggle" style="display:none;" />').html('<span>'+nt+'</span>').prependTo(view);	
			
			/*new Element('div', {
				'html'	: '<span>'+nt+'</span>',
				'class'	: 'view-label view-label-toggle',
				'styles': {
					'display':'none'
				}
			}).inject(view, 'top');*/
		
		$('<ul class="is-sortable field-list" />').appendTo(view);
		
			/*new Element('ul', {
				'class' : 'is-sortable field-list'
			}).inject(view);*/

		tabAction();		// Ajouter le comportement sur les LI
		formLayout(true);	// Memoriser le nouvel onglet dans le JSON

		if(doMove) enableMove();
		
		openView('ALL', 0);
	}
}

function formLayout(save){
		
	if($('#typeFormLayout').length < 1 && $('#groupFormLayout').length < 1) return false;

	var obj = {'tab':{}, 'bottom':[]};

	// Toujours visibles
	$('.field-list-bottom li.form-item').each(function(i, li){
		obj.bottom[i] = {
			'field'		: $(li).attr('id'),
			'close'		: $(li).hasClass('closed')
		};
	});

	// Les onglets
	$('.view-tab').each(function(i, view){
		field = [];
		$(view).find('li.form-item').each(function(j, li){
			field[j] = {
				'field'	: $(li).attr('id'),
				'close'	: $(li).hasClass('closed')
			};
		});
		
		var e	= {};
		
		e.label = $(view).children('.view-label').children('span').text();
		e.field = field;

		eval("obj.tab."+view.id+"=e");
	});

	if($('#typeFormLayout').length > 0){
		$('#typeFormLayout').val(JSON.stringify(obj));
	}else
	if($('#groupFormLayout').length > 0){
		$('#groupFormLayout').val(JSON.stringify(obj));
	}else{
		alert("Pas de champs pour serialiser JSON");
	}
	
	if(save) formLayoutSave();
	
}

function formLayoutSave(){
	//console.log('form layout save 1');
	//console.log('data', {id_group: $('#group-select option:selected').val(), typeFormLayout: $('#groupFormLayout').val()} );
	
		
	// Content
	if($('#id_type').length > 0){
		
		var get = $.ajax({
			url: '../content/helper/type-layout',
			type: "POST",
			data: {id_type: $('#id_type').val(), typeFormLayout: $('#typeFormLayout').val()}
		});
		
		get.done({
			// RIEN
		});
		
	}else
	
	// User
	if($('#group-select').length > 0){

		var get = $.ajax({
			url: '../user/helper/group-layout',
			type: "POST",
			data: {id_group: $('#group-select option:selected').val(), groupFormLayout: $('#groupFormLayout').val()}
		});
		
		get.done({
			// RIEN
		});

	}
}

function enableMove(){

	doMove = true;

	/*mySortables = new Sortables([], {
		handle: 'div.hand',
		constrain: false,
		clone: true,
		revert: true,
		opacity: 0.7,
		onComplete: function(){
			formLayout(true); // Memoriser l'ordre des DIV
		}
	});*/

//	mySortables.removeLists($$('.field-list'));
//	mySortables.addLists($$('.field-list'));

//	myTabSortables.removeLists($$('.do-viewer'));
//	myTabSortables.removeLists($$('.do-viewer'));
//	myTabSortables.addLists($$('.do-viewer'));

	mySortables = $('.field-list');
	myTabSortables = $('.do-viewer');

	$('.tabset').addClass('editing');

	$.each(mySortables, function(k,v) {
		
		// réactiver les sort si disabled
		if (typeof $(v).data('sortable') !== 'undefined') {
			 $(v).sortable({disabled : false});
		} else {
			// sinon les créer
			
			$(v).sortable({
				handle: 'div.hand',
				connectWith: '.field-list',
				stop: function() {
					formLayout(true);
				}
			});
		}
		
	});

	$.each(myTabSortables, function(k,v) {
		
		$(v).sortable({		
			axis : 'x',	
			handle: 'span.handle',
		    stop: function(){
		    	// Remettre les actions sur les bon tab
		    	tabAction();
	
		    	// On doit remettre les zones dans le bon ordre
				myTabsOrdered = [];
				myViewOrdered = [];
	
				$('.do-viewer li').each(function(i, item){
					myTabsOrdered[i] = $(item).find('span.text').html();
				});
	
				$.each(myTabsOrdered, function(i, name){
					$('.view-tab').each(function(ii, li){
						var named = $(li).find('.view-label span').html();
						if(name == named){
							myViewOrdered[i] = li;
						}
					});
				});
				
				var starter = $('.view-tab');
					starter = starter.eq(starter.length-1).next();
				
				for(i=0; i<myViewOrdered.length; i++){
					
					var tmp = $('<div />').html('dd').prepend(starter);
					//myViewOrdered[i].replaces(tmp);
					tmp.replaceWith(myViewOrdered[i]);
				}
	
				formLayout(true); // Memoriser l'ordre des onglets
		    }
		});
	});


	$('.hand').css('visibility', 'visible');
	$('.tab .handle, .tab .edit, .tab .remove').css('float', 'right');

	$('.is-tab.do-view span').css('display', 'block');
	$('.is-tab.do-view').css({'display' : 'block'});
	$('.is-tab.do-view .text').css('float', 'left');

	//$('.is-tab.do-view').css('width')
	
	$('#action-move-on').addClass('hide'); 		//css('display', 'none');
	$('#action-move-off').removeClass('hide'); 	//css('display', '');
	$('#action-add-tab').removeClass('hide');	//css('display', '');

	removeRichEditor();
}


function disableMove(){

	doMove = false;
	$('.tabset').removeClass('editing');

	mySortables.each(function(k,v) {
		$(v).sortable({ disabled: true });
	});

	$('.hand').css('visibility', 'hidden');
	$('.tab .handle, .tab .edit, .tab .remove').css('display', 'none');

	$('#action-move-on').removeClass('hide'); 		// css('display', '');
	$('#action-move-off').addClass('hide');			// css('display', 'none');
	$('#action-add-tab').addClass('hide'); 			// css('display', 'none');
	
	$('.is-tab.do-view').css({'display' : ''});
	$('.is-tab.do-view span').not('.text').css('display', 'none');
	$('.is-tab.do-view .text').css('float', 'none');

	setRichEditor();
}


function openView(n,p){

	$('.tabset .tab li').removeClass('is-selected');

	if(n == 'ALL'){
		$('.tabset .view-tab').each(function(i, view){
		//	$(view).css('display', '').find('.view-label').css('display', '');
			$(view).removeClass('hide').find('.view-label').css('display', 'block');
		});

		$('.tabset .tab li.is-tab').addClass('is-selected');
	}else{
		
		$('.tabset .tab li').removeClass('is-selected');
		$('.tabset .view-tab').addClass('hide'); //css('display', 'none');
		$('.tabset .view-label-toggle').css('display', 'none');

		$('.view').eq(n).removeClass('hide'); //css('display', '');

		$('.tabset .tab li').eq(p).addClass('is-selected');
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function tabAction(){

	// Ouverture de l'onglet
	$('.tabset .tab li .text').each(function(i, e){
		$(e).unbind('click').bind('click', function(){

			if($(this).parent().hasClass('view-all')){
				openView('ALL', i);
			}else
			if($(this).parent().hasClass('do-view')){
				openView(i, i);
			}

		});
	});

	// Changer le nom
	$('.tabset .tab li span.edit').each(function(i, e){
		$(e).unbind('click').bind('click', function(){
			
			var parent	= $(this).parent();
			var text	= parent.find('span.text');
			
			if(parent.hasClass('do-view')){

				var html = $(this).html();
				if(parent.find('span.text').length > 0) html = parent.find('.text').html();

				var newName = prompt("Nom de l'onglet", html);
				if(newName != null && newName != ''){

					(parent.find('span.text').length > 0)
						? parent.find('span.text').html(newName)
						: $(this).html(newName);
					
					var view	= parent.parent().parent().parent().parent().children('.view').eq(i);
				//	console.log(parent.parent().parent().parent().parent().children('.view').eq(i))
					var label	= view.find('.view-label span');

					if(label) label.html(newName);						
				}

				formLayout(true); // Memoriser le nouveau nom
			}

		})
	});

	// Supprimer l'onglet
	$('.tabset .tab li span.remove').each(function(i, e){
		$(e).unbind('click').bind('click', function(){
			
			var parent	= $(this).parent();
			
			openView('ALL', i);

			// Verifier s'il y a plus d'un onglet
			if($('.do-viewer li.do-view').length == 1){
				alert("Vous ne pouvez pas supprimer cet onglet");
				return true;
			}

			// Verifier s'il y a des champs dans la VIEW associ�s
			var views	= parent.parent().parent().parent().parent().find('.view');
			var view	= views[i];
			var items	= $(view).find('.form-item');
			
			if(confirm("Voulez-vous supprimer cet onglet ?")){
				if(items.length > 0){
					items.each(function(i, itm){
						$(itm).append(views.eq(0).find('ul'));
					});
				}
				

				$(view).remove();
				parent.remove();
				
				formLayout(true); // Memoriser la suppression de l'onglet
			}

		})
	});
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	THIS IS THE VERY FIRST START POINT !!!
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function boot(){

	// Open-Close
	$('.toggle').each(function(i, e){
		$(e).bind('click', function(){
			$(this).parent().toggleClass('closed');
			formLayout(true); // Memoriser le open-close des DIV
		});
	});

	mySortables = {};
	myTabSortables = {};

	// Deplacement des CHAMPS

	var replacelis = function(){
    	// Remettre les actions sur les bon tab
    	tabAction();

    	// On doit remettre les zones dans le bon ordre
		myTabsOrdered = [];
		myViewOrdered = [];

		$('.do-viewer li').each(function(i, item){
			myTabsOrdered[i] = item.find('span.text').html();
		});

		myTabsOrdered.each(function(i, name){
			$('.view-tab').each(function(ii, li){
				var named = $(li).find('.view-label span').html();
				if(name == named){
					myViewOrdered[i] = li;
				}
			});
		});
		
		var starter = $('.view-tab');
			starter = starter[starter.length-1].next();
		
		for(i=0; i<myViewOrdered.length; i++){
			
			var tmp = $('<div />').html('dd').prepend(starter);
			//myViewOrdered[i].replaces(tmp);
			tmp.replaceWith(myViewOrdered[i]);
		}

    }

	// Pour chaque liste de media (boite de selection)
	mediaList = [];
	$('.media-list').each(function(i, e){
		mediaList[i] = '#'+$(e).find('textarea').attr('id');
	});
	
	if(mediaList.length > 0){	
		$.each(mediaList, function(i, list){
			mediaEnableSort(list, true);
			$(list).bind('change', function(){
				mediaEnableSort($(this).attr('id'), true);
			});
		});
	}

	// Remplacer a la volee les LI dans les UL de la LAYOUT
	if(replace.length > 0){
		$.each(replace, function(i, e){
			if($(e)){
				if($('#replace-'+$(e).attr('id')).hasClass('closed')) $(e).addClass('closed');
				$('#replace-'+$(e).attr('id')).replaceWith($(e));
			}
		});
	}

	tabAction();
	setRichEditor();
	setUrlBehaviour();
	formLayout(false);
	checkNeedToBeFilled();
	tagInit();

	// Ajouter les datePicker aux champs DATE
	//
	$('.datePicker').each(function(d){

		/*if(d.value == '' || d.value == '0000-00-00') var isEmpty = true;

		var cal = new CalendarEightysix(d, {
			'startMonday'	: true,
			'alignX'		: 'middle',
			'alignY'		: 'top',
			'format'		: '%Y-%m-%d'
		});

		if(isEmpty) d.value = '';*/

	});
}

function setUrlBehaviour(){

	if($('#contentNameField').length > 0){
		$('#contentNameField').bind('keyup', function(event){
			// desactive touche tabulation
			if(event.keyCode != '9' && event.keyCode != '16') {
				if($('#autogen').length > 0){
					if($('#autogen').prop('checked')) urlCheck();
				}else{
					urlCheck();
				}
			}
		});
	}


}

function urlCheck(){

	var url = liveUrlTitle($('#contentNameField').val());
	$('#urlField').val(url); 


	var language = ($('#language')[0].tagName.toLowerCase() == 'select')
		? $('#language option:selected').val()
		: $('#language').val();

	$.ajax({
		url: '../content/helper/url',
		data: {
			id_content: $('#id_content').val(),
			url: url,
			language: language
		},
		type: 'get',
		dataType: 'json'
	}).done(function(data) {
		if($('#urlField').val() != data.url) $('#urlField').val(data.url);
	});
}	

function toggleEditor(id) {
	var textarea = $('#'+id);

	if(textarea.length){
		if(textarea.css('display') == 'none'){
			CKEDITOR.instances[id].destroy()
		}else{
			CKEDITOR.replace(id)
		}
	}

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Les fonctions qui suivent servent a la gestion d'un field CONTENT (tableau)
	ou d'un USER ou d'un DBTABLE (table externe comme source de donn�es)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
tagSearchStorage = {};
tagSortable		 = [];

function tagInit(){

	$('.keyword').each(function(){
		var tmp = $(this).sortable({
			placeholder: 'keyholder',
			opacity: 0.7,
			forcePlaceholderSize: true,
		    stop: function(){
		    //	console.log(this.serialize());
		    }
		});

		// on memorise
		tagSortable.push({'id':$(this).attr('id'), 'sort':tmp});
	});
	
//	console.log(tagSortable);
}

function tagInsert(zone, fieldName, id_field, id_type, id, contentName, method){
	
	if(id_type == 'dbtable'){
		var prompt = '#db';
	}else
	if(id_type == 'user'){
		var prompt = '#us';
	}else{
		var prompt = 'ct';
	}

	var area = $('#'+zone).find('.keyword');
	if($(prompt+'-'+id_field+'-'+id).length > 0) return true;

	if(method == 'solo') $('#'+zone).find('.keyword').empty();

	var key = $('<li class="key" id="'+prompt+'-'+id_field+'-'+id+'">'+contentName+' ('+id+')</li>').prependTo(area);
	var inp = $('<input type="hidden" name="'+fieldName+'[]" value="'+id+'" />').prependTo(key);
	var add = $('<a class="kill" />').appendTo(key).bind('click', function() {
		tagRemove(prompt, id_field, id);
	});
	var edit = $('<a class="edit" />').appendTo(key).bind('click', function() {
		tagOpen(id_type, id);
	});
	
	// Pas tres propre
	/*for(i=0; i<tagSortable.length; i++){
		if(tagSortable[i].id == 'sort'+id_field){
			tagSortable[i].sort.addItems(key);
		}
	}*/

}

function tagOpen(id_type, id){

	if(id_type == 'dbtable'){
		return false;
	}else
	if(id_type == 'user'){
		var prompt = '../user/data?id_user='+id;
	}else{
		var prompt = '../content/data?id_content='+id;
	}
	
	window.open(prompt, '', '');
}

function tagRemove(prompt, id_field, id){


//	prompt = '#'+prompt;
//	prompt = prompt;

//	if(!$(prompt+'-'+id_field+'-'+id).length > 0) return false;
	$('#'+prompt+'-'+id_field+'-'+id).remove();
}

function tagSearch(id_field, id_type, fieldName, method){
	
	var q = $('#contenttable-'+id_field).find('.field').val();
	var g = {
		'id_field'	: id_field,
		'id_type'	: id_type,
		'offset'    : 0,
		'q'			: q
	};
	
	tagSearchRequest(id_field, id_type, fieldName, true, g, method);
}


// Creation d'un nouveau contenu en base + associe le tag
function tagCreate(id_field, id_type, fieldName, method){

	var q = $('contenttable-'+id_field).getElement('.field').value;
	if(q == '') return false;

	new Request.JSON({
		'url'			: 'ressource/lib/field.tagcreate.php',
		'chain'			: 'cancel',
		'onComplete'	: function(r){
			if(r == null) return false;
			tagInsert('contenttable-'+id_field, fieldName, id_field, id_type, r.id_content, r.contentName, method);

			$('contenttable-'+id_field).getElement('.field').value = '';
		}
	}).get({
		'id_type': 	id_type,
		'name': 	q
	});

//	console.log(id_field, id_type, fieldName, method, q);

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

function tagSearchRequest(id_field, id_type, fieldName, clean, getVar, method){

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

	var q = $('#contenttable-'+id_field).find('.field').val();
	var t = $('#contenttable-'+id_field).find('table.listing tbody');

	// On supprime les lignes actuelles
	if(clean){
		t.find('tr').remove();

		// On indique qu'on va effectuer une recherche
		$('<tr><td colspan="2" class="tagListingSearch">Recherche en cours...</td></tr>').appendTo(t)
	}

	// + le lien MORE en dessous
	if($('#morexqr-'+id_field)) $('#morexqr-'+id_field).remove();

	// Est-ce que j'utiliser deja ce XQR ?
	var xqr = $.ajax({
		'url'			: '../field/helper/field-multitag',
		'dataType'		: "json",
		'data'			: getVar
	});
	
	xqr.done(function(r) {
		var a = r.result;

		// On supprime les lignes actuelles
		t.find('tr').remove();

		// le lien MORE en dessous
		if($('#morexqr-'+id_field)) $('#morexqr-'+id_field).remove();

		if(a.length > 0){
			$.each(a, function(i, e){

				var line = $('<tr />').appendTo(t);
				var push = $('<td />').css('width', 25).appendTo(line);
				var anch = $('<a style="cursor:pointer;"><img src=\"../core/ui/img/_img/picto-add.png\" /></a>').appendTo(push).bind('click', function() {
					tagInsert('contenttable-'+id_field, fieldName, id_field, id_type, eval('e.'+tagId), eval('e.'+tagView), method);
				});
				var name = e[tagView];

				if(e.path) name = name + ' ('+ e.path +')';

				$('<td>'+e[tagId]+'</td>').appendTo(line).css('width', 25);
				$('<td class="@">'+name+'</td>').appendTo(line);

				if(typeof e.more != 'undefined'){
					e.more.each(function(i, more){
						$('<td>'+more+'</td>').appendTo(line).css('width', '20%');
					});
				}
			});

			if(r.more){
				var line = $('<tr id="#morexqr-'+id_field+'" />').appendTo(t);
				var more = $('<td colspan="4" width="25" />').appendTo(line);

				more.click(function(){
					getVar_ = getVar;
					getVar_.offset = getVar.offset + 1;

					console.log(id_field, id_type, fieldName, false, getVar_, method);
					tagSearchRequest(id_field, id_type, fieldName, false, getVar_, method);
				});

				more.html('Plus');
			}
		}
		else{
			$('<tr><td colspan="2" class="tagListingSearch">Aucun r&eacute;sultat</td></tr>').appendTo(t);
		}
	});
	

	tagSearchStorageSet(id_field, 'xqr', xqr);

	//	console.log("Je reutilise ce qui existe");
		
		/*tagSearchStorageSet(id_field, 'clean',	clean);

		xqr = tagSearchStorageGet(id_field, 'xqr');
		console.log(xqr)
		xqr.get(getVar);*/
}

function modalShowUpload() {
	
	fadeWall = $('<div id="fade-wall" />').appendTo('body');
	$('#modal-upload').appendTo('body');
	
	if ($('#modal-upload #uploadembed').length == 0) {
		form = $('<form id="uploadembed" />');
		$('#modal-upload .uploadcontainer').wrap(form);
	}
	
	$('#modal-upload').fadeTo(218, 1);
	$('#fade-wall').fadeTo(218, 1);
	$('#modal-upload .uploadcontainer').fadeTo(218, 1);
	/*$('#modal-upload .uploadcontainer').css('left', (($(window).width() - 600) / 2));
	$('#modal-upload .uploadcontainer').css('top', (($(window).height() - 400) / 2));*/
	
	/* Mettre a jour les path d'upload si d�j� charg� */
	/*var uploadPath = $('#path').attr('data-url');
	if (typeof $('#modal-upload .uploadcontainer #file_upload').data('uploadifive') === 'object') 
		$('#modal-upload .uploadcontainer #file_upload').data('uploadifive').settings.uploadScript = 'helper/upload-action?f='+uploadPath;
	if (typeof $('#modal-upload .uploadcontainer #file_upload').data('uploadify') === 'object') 
		$('#modal-upload .uploadcontainer #file_upload').data('uploadify').settings.uploadScript = 'helper/upload-action?f='+uploadPath;
	*/	
	// SI ON A ACCES AU FILEREADER DU BROWSER
	if (typeof FileReader !== 'undefined') {
		
		// NE PAS LANCER PLUSIEURES INSTANCES
		if (typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadifive')) === "undefined") {
			
			// SI LA MODAL EST ACTIVEE SUR UPLOADIFY ALORS QU'ON DROP, DESTROY
			if (typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadify')) === "object") {
				$('#modal-upload .uploadcontainer #file_upload').uploadify('destroy');
			}
			
			var date = new Date();
			//	folder = folder.get.split('/'). 			
						
			$('#modal-upload .uploadcontainer #file_upload').uploadifive({
				'buttonText'   : 'Parcourir',
				'auto'         : true,
				'formData'     : {'test' : 'something'},
				'queueID'      : 'queue',
				'onSelect'     : function(event,ID,fileObj) {
				},
				'onDrop' : function(file, count) {
					
				},
				'uploadScript' : '../media/helper/upload-action?auto',
				'onUploadComplete' : function(file, data) {
					data = JSON.parse(data);
					field = $(this).parents('#modal-upload').attr('data-field');
					prompt = detectType(file.name);
					mediaInsert(field, prompt+'@@'+data.folder+'/'+file.name, 'sort');
				},
				'onQueueComplete' : function() {
					modalHideUpload();
					$('#queue').empty();
				}
			});
		}
	} else {
		console.log('Sorry, can\'t do that. Your browser sucks.');
	}
}

function modalHideUpload() {
	
	$('#fade-wall').fadeTo(218, 0, function() {
		$('#fade-wall').remove();
	});
	$('#modal-upload').fadeTo(218, 0, function() {
		$('#modal-upload').css('display', 'none');
	});
}

function detectType(file){

	ext = file.substr(file.lastIndexOf(".")+1).toLowerCase();
	arr = ['mov', 'avi', 'm4v', 'mp4', 'mpg', 'mpeg', 'wmv', 'flv'];
	
	if(ext == 'ppt'){
		return 'powerpoint';
	}else
	if(ext == 'swf'){
		return 'flash';
	}else
	if(ext == 'pdf'){
		return 'pdf';
	}else
	if(ext == 'doc' || ext == 'txt'){
		return 'word';
	}else
	if(ext == 'mp3' || ext == 'aif' || ext == 'aiff' || ext == 'wav'){
		return 'audio';
	}else
	if(ext == 'xls' || ext == 'xlm' || ext == 'xlt'){
		return 'excel';
	}else
	if( $.inArray(ext, arr) !== -1 ){
		return 'video';
	}else
	if(ext == 'htm' || ext == 'html' || ext == 'php' || ext ==  'php3' || ext == 'php4' || ext == 'php5'){
		return 'html';
	}else
	if(ext == 'png' || ext == 'gif' || ext == 'jpeg' || ext == 'jpg' || ext == 'tiff' || ext == 'tif' || ext == 'psd' || ext == 'bmp'){
		return 'image';
	}else
	if(ext == 'zip' || ext == 'tar' || ext == 'tgz' || ext == 'sit' || ext == 'rar' || ext == 'arj' || ext == 'sitx' || ext == 'sea' || ext == 'lha' || ext == 'lzh' || ext == 'bin' || ext == 'hqx' || ext == 'gz' || ext == 'tbz' || ext == 'z' || ext == 'taz'){
		return 'archive';
	}else{
		return 'unknown';
	}
}











