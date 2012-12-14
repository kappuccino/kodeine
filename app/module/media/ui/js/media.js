viewMode		= 'icon';		// Mode icon|list determine la maniere dont les images sont affiches
factor			= 0.65;			// Facteur entre minH et miniW
minH			= 200;			// La hauteur minimum autorisee
minW			= minH*factor;	// La largeur minimum autorisee
folder			= ''; 			// Dossier courant
file			= ''; 			// Fichier courant
collection		= ''; 			// La dernier resultat de la rechercher de dossier
parentFolder	= '';			// Determine (si non vide) le nom du dossier parent
panelOpened		= false;		// Status de iframe panel
panelView		= '';			// Ce qui est dans le panel
sliderMove		= false;
myPath			= '/admin/';
myMedia			= '/admin/media/ui/img';
isDrag			= false;
hasHistory 		= false;
scrollWidth		= '';

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function init(){

	if($('#button-upload').length > 0)		$('#button-upload').bind('click', modalShowUpload);
	if($('#button-folder').length > 0)		$('#button-folder').bind('click', folderView);
	if($('#button-newdir').length > 0)		$('#button-newdir').bind('click', modalPromptDir);
	if($('#button-pref').length > 0)		$('#button-pref').bind('click', modalPref);
//	if($('button-maintenance')) $('button-maintenance').addEvent('click', panelMaintenance);
	if($('#button-hidepanel').length > 0)	$('#button-hidepanel').bind('click', panelHide).css('display', 'none');
	if($('#viewModeIcon').length > 0)		$('#viewModeIcon').bind('click', function(){ modeSet('icon'); });
	if($('#viewModeList').length > 0)		$('#viewModeList').bind('click', function(){ modeSet('list'); });
	
	var body = $('body')[0];
	
	$('#fade-wall').on('click', function() {
		modalHideUpload();
	});
	
	$(document).keyup(function (e) {
		//console.log(e);
		if (e.keyCode == 27 && $('#modal-upload').css('display') == 'block') {
			modalHideUpload();
		}
	});
	
	document.addEventListener('dragleave', function(e) {
		// Stop FireFox from opening the dropped file(s)
		// console.log('leave');
		
		if (e.pageX === 0) {
			modalHideUpload();
			isDrag = false;
		}		

		e.preventDefault();
		e.stopPropagation();
	}, false);

	document.addEventListener('dragenter', function(e) {
		// Stop FireFox from opening the dropped file(s)
		if (isDrag) return;		
		isDrag = true;
		
		modalShowUpload();
		e.preventDefault();
		e.stopPropagation();
	}, false);

	document.addEventListener('dragover', function(e) {
		// Stop FireFox from opening the dropped file(s)
		e.preventDefault();
		e.stopPropagation();
	}, false);

	scrollWidth = getScrollbarWidth();
	$(window).resize(function() {
		spreadGrid();
	});

	modeSet(viewMode);
}

function getScrollbarWidth() 
{
    var div = $('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div></div>'); 
    $('body').append(div); 
    var w1 = $('div', div).innerWidth(); 
    
    div.css('overflow-y', 'auto'); 
    var w2 = $('div', div).innerWidth(); 
    $(div).remove(); 
    return (w1 - w2);
}

function spreadGrid() {
	
	if (typeof($('.dragme .cnt').attr('style')) == 'undefined') {
		$('.dragme .cnt').css('width', '208px');
	}
	
	mainWidth = $('#main').width() - scrollWidth;
	rowNB = ( mainWidth / ($('.dragme .cnt').outerWidth() + 10) );
	rowNB = rowNB.toString().split('.')[0]; // nb d'elements par row
	
	percent = 100/rowNB;

	// si pas assez d'éléments dans la ligne
	if (rowNB > $('.dragme').length) {
		rowNB = $('.dragme').length;
		if (percent > 25) percent = 25;
	}
	
	$('.dragme').width(percent+'%');
}

function sliderInit(){
	mySlider = $('#slider').slider({
		step: 1,
		min: minH,
		max: (minH+300),
		stop: function(e, ui) {
			
			if(sliderMove) folderElementResizeReload();

			folderElementSize(ui.value, (ui.value*factor));
			spreadGrid();
			
			if(!sliderMove) sliderMove = true;
		}
	});
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function modeSet(m,s){

	viewMode = m;
	$('#viewMode a').removeClass('me');
	$('#main').removeClass('mode-icon').removeClass('mode-list');

	if(viewMode == 'icon'){
		$('#viewModeIcon').addClass('me');
		$('#main').addClass('mode-icon');
		minH = 200;
		
		$('.iconeaction').css({'display':'none', 'opacity':0});
	}else
	if(viewMode == 'list'){
		$('#viewModeList').addClass('me');
		$('#main').addClass('mode-list');
		minH = 40;
	}

	minW = minH*factor;
	
	sliderInit();
	
	if(collection.length > 0){
		folderDisplay();
		folderElementResizeReload();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderNav(url){
	setFolder(url);
	folderView(url);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderNavFromPosition(n){
	setFolder(collection[n].url);
	folderView();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderView(hash){

	$('#path').html('Chargement en cours');

	var remote = $.ajax({
		url: '/admin/media/helper/folder',
		dataType: 'json',
		data: {
			'factor': factor,
			'folder': folder
		}
	}).done(function(data) {
		if(data != null){

			collection 		=  data.files;
			parentFolder	= (data.parent != null) ? data.parent : null; 

			folderDisplay();
			viewUrl();
			makeDragAndDrop();
			spreadGrid();
			//window.History.pushState({state:1}, "State 1", "");
		}
	});
	
	if(hash != false) document.location='#'+folder;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderDisplay(){

	$('#main').empty();

	if(collection.length > 0){

		$.each(collection, function(position, el){
			folderlElement(el, position).appendTo('#main');
		});

		makeDragAndDrop();
		maskByClass();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderlElement(el, position){
	
	// Largeur desiree !!
	var width 		= mySlider.slider("value");
	var height		= width * factor;
	
	// Objet a insert (image + fonction)
	var	insert		= {'src':'','class':''};

	// Tous les objects utilises dans e
	var container = $('<div id="'+el.name+'" class="e dragme clearfix imgResize '+el.tag+' '+viewMode+'"/>');
		container.bind({
			'mouseenter' : function(e) {$(this).addClass('colored')},
			'mouseleave' : function(e) {$(this).removeClass('colored')}
		});
	
	var content = $('<div class="cnt" />').appendTo(container);
	var top = $('<div class="top clearfix">&nbsp;</div>').prependTo(content);
	var icone = $('<div class="icone loader"/>').appendTo(content).bind({
		'mouseenter' : function(e) {
			if(typeof iconeaction != 'undefined' && viewMode == 'icon')	$(this).find('.iconeaction').css('opacity', 1);
		},
		'mouseleave' : function(e) {
			if(typeof iconeaction != 'undefined' && viewMode == 'icon') $(this).find('.iconeaction').css('opacity', 0);
		}
	});
	
	var tools = $('<div class="tools" />').appendTo(content);
	var name  = $('<div class="name" />').insertAfter(icone);
	var nameField = $('<input type="text" class="field" value="'+el.name+'" readonly="readonly" />').appendTo(name).bind({
		'click' : function() {actionRename(position);},
		'focus' : function(e) {$(this).addClass('fieldFocus');}
	});

	if(viewMode == 'icon' && el.type == 'file'){
		
		var iconeaction = $('<div class="iconeaction" style="display:none;opacity:0" />').appendTo(icone).fadeTo()
		/*var iconeaction = new Element('div', {
			'class'		: 'iconeaction',
			'styles'	: {
				'display' : 'none',
				'opacity' : '0'
			}
		}).set('morph', {duration:150}).inject(icone);*/
		
	}else
	if(viewMode == 'list'){
		var iconeaction = $('<div class="iconeaction" />').prependTo(name);
	}
	
	var remove = $('<img src="'+myMedia+'/media-delete.png" class="remove" title="supprimer" />').appendTo(tools).bind({
		'click' : function() {actionDelete(position)}
	})

	if(el.type == 'dir'){

		if(el.locked){
			container.addClass('isLocked');
			var lockSrc = myMedia+'/media-locked.png';
		}else{
			container.removeClass('isLocked');
			var lockSrc = myMedia+'/media-unlocked.png';
		}

		var locked = $('<img src="'+lockSrc+'" class="lock" title="Protection contre la suppression" />').appendTo(tools).bind({
			'click' : function() {actionLock(position);}
		});
	}

	// Fixer a la main la largeur et hauteur de CONTAINER
	icone.css({'width':width, 'height':height});
	
	if(viewMode == 'icon'){
		container.css({'width':(width+8)+'px'});
	}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	// Si l'element est un dossier
	if(el.type == 'dir'){
		container.addClass('dropme');
		
		if(viewMode == 'icon'){
			top.css('background', 'url('+myMedia+'/media-nano-folder.png) no-repeat 4px center');
		}

		if(field != '' && method != ''){
			
			var _select = $('<img src="'+myMedia+'/media-select.png" />').appendTo(tools).bind({
				'click' : function() {selectFile(el.url, 'folder');}
			});
		}
		
		var insert = $('<img src="'+myMedia+'/media-folder.png" height="32" width="32" />');
		
	}else

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	if(el.type == 'file'){

		if(viewMode == 'icon'){
			top.css('background', 'url('+myMedia+'/media-nano-file.png) no-repeat 4px center');
		}

		// Ajouter l'action en ajout 'ajouter'
		$('<a>Insérer</a>').bind('click', function() {
			selectFile(el.url);
		}).appendTo(top);

		// Legende
		var _meta = $('<img src="'+myMedia+'/media-t.png" title="Légende" />').appendTo(iconeaction).bind('click', function() {
			actionMetadata(el.url);
		});

		// Url
		var _link = $('<img src="'+myMedia+'/media-copy.png" title="Afficher le chemin d\'accès" />').appendTo(iconeaction).bind('click', function() {
			actionClipBoard(el.url);
		});

		var _dup = $('<img src="'+myMedia+'/media-duplicate.png" title="Dupliquer l\'image" />').appendTo(iconeaction).bind('click', function() {
			actionDuplicate(el.url);
		});

		/******************************/
		// PDF
		if(el.kind == 'pdf'){

			if(viewMode == 'icon'){
				top.css('background', 'url('+myMedia+'/media-nano-pdf.png) no-repeat 4px center');
			}

			// Full Size Pop Up
			var _full = $('<img src="'+myMedia+'/media-fullsize.png" title="Afficher le fichier" />').appendTo(iconeaction).bind('click', function() {
				window.open(el.url);
			});
			
			var _play = $('<img src="'+myMedia+'/media-flip.png" />').appendTo(iconeaction).bind('click', function() {
				actionPdfToImage(el.url);
			});
		}else

		/******************************/
		// AUDIO
		if(el.kind == 'audio'){

			if(viewMode == 'icon'){
				top.css('background', 'url('+myMedia+'/media-nano-audio.png) no-repeat 4px center');
			}

			var _play = $('<img src="'+myMedia+'/media-play.png" />').appendTo(iconeaction).bind('click', function() {
				actionViewAudio(el.url);
			});

		}else

		/******************************/
		// VIDEO
		if(el.kind == 'video'){

			if(viewMode == 'icon'){
				top.css('background', 'url('+myMedia+'/media-nano-video.png) no-repeat 4px center');
			}

			var _play = $('<img src="'+myMedia+'/media-play.png" title="Lire la vidéo" />').appendTo(iconeaction).bind('click', function() {
				modal.open({ type : 'video', url : el.url });
			});

			var _nfo = $('<img src="'+myMedia+'/media-nano-info.png" title="Voir les infos techniques" />').appendTo(iconeaction).bind('click', function() {
				window.open('/admin/media/helper/player-video?url='+el.url, '', '');
			});

			var _poster = $('<img src="'+myMedia+'/media-flip.png" title="Gérer le poster de la video" />').appendTo(iconeaction).bind('click', function() {
				var url = el.url;
				window.open('helper/video-poster?url='+url, '', '');
			});

		}else

		/******************************/
		// PICTURE
		if(el.kind == 'picture'){
			
			if(viewMode == 'icon'){
				top.css('background', 'url('+myMedia+'/media-nano-image.png) no-repeat 4px center');
			}
			
			container.addClass('isPicture');
		//	if(el.height > minH || el.width > minW) container.addClass('imgResize');
			if(el.thumbnail.exists){
				var insert = $('<img src="'+decodeURIComponent(el.thumbnail.url)+'" height="'+el.thumbnail.height+'" width="'+el.thumbnail.width+'" />'); 
			}else{
				parentHeight = icone.height();
				//var insert = $('<img src="'+el.url+'" height="'+el.height+'" width="'+el.width+'" />');
				var insert = $('<img src="'+el.url+'" height="'+parentHeight+'" />');
			}
			var _class = ((el.height > el.width) ? 'portrait' : 'landscape');
			insert.attr('class', _class);

			// Full Size Pop Up
			var _full = $('<img src="'+myMedia+'/media-fullsize.png" title="Afficher en grand" />').appendTo(iconeaction).bind('click', function() {
				window.open(el.url);
			});

			// Size
			var _size = $('<img src="'+myMedia+'/media-size.png" title="Manipuler la taille" />').appendTo(iconeaction).bind('click', function() {
				var url = el.url;
				window.open('helper/crop?url='+url, '', '');
			});
		} 


		/******************************/

		// GENERIQUE
		if(insert.src == ''){

			var insert = $('<img src="'+myMedia+'/media-file_'+el.kind+'.png" height="128" width="128" />');

		}
	}


	//
	// Insertion de l'image de l'element (generique, dossier ou autre)
	//
	if(insert.attr('src') != ''){

		var _image = $('<img />');
		var _image = $('<img />');

		_image.attr('src', insert.attr('src'));
		
		_image.css('display', 'none');
		_image.addClass('img');
		
		_image.load(function(){
			// Pffff (hack ie?)
			//	this.alt = insert.src;

			var ratio = (insert.width() > insert.height())
				? (insert.width()  < 140) ? 1 : (140 / insert.width())
				: (insert.height() < 140) ? 1 : (140 / insert.height());

			// Ajouter les evenement relies a cette image
			//if(typeof insert.events == 'object') $(this).click(folderNavFromPosition(position));
			if(insert.length > 0 && el.type == 'dir') $(this).click(function(){folderNavFromPosition(position)});

			// Injection de l'image
			if (typeof iconeaction != 'undefined') {
				$(this).appendTo(icone, 'bottom')
			} else {
				$(this).appendTo(icone);
			}
			// Remettre la bonne taille
			//console.log(this);
			$(this).css('display', '');
			folderElementSize(mySlider.slider("value"), (mySlider.slider("value") * factor), container);
			$(this).parent('div.e').find('.loader').removeClass('loader');

			// Ajouter le class
			if(typeof insert.attr('class') != 'undefined') $(this).addClass(insert.attr('class'));
		});
	}

	return container;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
/*function folderElementResizeReload(){
	//console.log("resize !")
	return true;

	$$('.imgResize img.img').each(function(img){
	//	img.getParent('.icone').addClass('loader');

		if(img.src.test('/w:([0-9]*)/')){
			var wi = new RegExp('/w:([0-9]*)/');
			var wi = wi.exec(img.src);
		
			if(wi[1] < mySlider.slider("value")){
				img.src = img.src.replace(wi[0], '/w:'+Math.round(mySlider.slider("value"))+'/');
			//	img.alt = img.src;
			}
		}else
		if(img.src.test('/h:([0-9]*)/')){
			var he = new RegExp('/h:([0-9]*)/');
			var he = he.exec(img.src);

			if(he[1] < (mySlider.slider("value") * factor)){
				img.src = img.src.replace(he[0], '/h:'+Math.round(mySlider.slider("value") * factor)+'/');
			//	img.alt = img.src;
			}
		}

	});
}*/

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function folderElementSize(myWidth, myHeight, e){

	// Soit TOUS soit JUSTE MOI
	job = (e == null) ? $('#main .e') : [e];
	if(job.length == 0) return false;

	for(var i=0; i<job.length; i++){
		e = $(job[i]);

		// Pointeur sur les sous element de "e"
		var ico = e.find('.icone');
		var iac = e.find('.iconeaction');
		var img	= e.find('.img');
		var nam	= e.find('.name');
		var too	= e.find('.tools');
		
		ico.css({
			'width'		: myWidth  + 'px',
			'height'	: myHeight + 'px'
		});

		if(e.hasClass('imgResize') ){
			var ratioH  = myHeight / img.height();
			var ratioW  = myWidth  / img.width()
			var ratio	= (ratioH > ratioW) ? ratioW : ratioH;

			img.css({
				'height'	: Math.floor((img.height() * ratio) * 0.95)+'px',
				'width'		: Math.floor((img.width() * ratio) * 0.95)+'px'
			});
			
			img.height = img.height();
			img.width  = img.width();
		}
		
		// Centrer l'image verticalement
		mt = myHeight - img.height;
		mt = (mt > 0) ? mt / 2 : 0;
		img.css({'margin-top' : mt+'px'});

		// Mise a jour de la largeur du container
		if(viewMode == 'icon'){
			e.children('.cnt').css({'width': myWidth+8+'px'});
			nam.css({'width': myWidth + 'px'});
		}else{
			e.children('.cnt').css({'width' : 'auto'});
			nam.css({'width': ''});
		}
		
		if(iac.length > 0 && viewMode == 'icon'){
			iac.css({
			//	'width' 	: '180px',
				'width' 	: (myWidth - 10),
			//	'height'	: myHeight,
				'display'	: 'block',
				'visibility': 'hidden'
			});
			
			var tmpCoo = iac.position();
			
			iac.css({
				'margin-top'	: Math.round(Math.abs(myHeight - iac.height()) / 2) +'px',
				'margin-left'	: Math.round(Math.abs(myWidth  - iac.width())  / 2) +'px',
				'visibility'	: 'visible'
			});
		}

	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function makeDragAndDrop(){

	$('.dragme .icone').each(function(i, e){
		
		if ( $(e).hasClass('ui-draggable') ) {
			return true;
		}

		$(e).draggable({
			handle: $(e).find('.icone'),
			zIndex: 9999,
			//helper: 'clone',
			stack : '.icon',
			revert: true,
			cursorAt: { left: 20, top: 20},
			distance: 5,
			containment : 'window',
			appendTo : '#media',
			
			start: function(e, u) {
				$(e.currentTarget).fadeTo(218, 0.5);
			},
			
			stop: function(e, u) {
				$(e.target).fadeTo(218, 1);
			}
			
		});

	});	
	
	$('.dropme').droppable({
		hoverClass: 'orange',
		tolerance : 'pointer',
		drop : function(e, ui) {
			el = $(ui.draggable);
			actionMove(el.parents('.dragme').attr('id'), $(this));				
		},
		over : function(e, ui) {
			//console.log("OVER !");
		}
		
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function viewUrl(){

	var before	= '';
	var parts 	= folder.split("\/");
	var str		= [];

	$('#path').empty();

	$('<span>/</span>').appendTo('#path');

	for(i=1; i<parts.length; i++){
		
		var a = $('<a class="parent" id="'+before+'/'+parts[i]+'">'+parts[i]+'</a>').appendTo('#path').bind('click', function() {
			folderNav($(this).attr('id'));
		});
		
		if(i < parts.length-1){

			a.addClass('dropme');
			$('<span>/</span>').appendTo('#path');		

		}

		before 	= before+'/'+parts[i];
	}
	
	$('#path').attr('data-url', before);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function setFolder(url){
	folder = url;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function setFile(url){
	file = url;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function selectFile(file, prompt){

	prompt = (prompt == null) ? detectType(file) : prompt;
	
	switch(method){
		case 'mce'			: parent.opener.insertRichEditor(field, '<img src="'+file+'" />');	break;
		case 'sort'			: parent.opener.mediaInsert(field, prompt+'@@'+file, 'sort'); 		break;
		case 'sort-embed'	: parent.mediaInsert(field, prompt+'@@'+file, 'sort'); 		break;

	//	case 'fck'			: parent.opener.insertContent('<img src=\"'+file+'\" border=\"0\" alt=\"\" />', field); break;
	//	case 'multiline'	: parent.opener.document.getElementById(field).value += prompt + file+"\n"; break;

		case 'line'			: fld = parent.opener.document.getElementById(field);
							  fld.value  = file;
							  fld.fireEvent('change', fld);
							  break;

		case 'editable'		: parent.opener.editable.imageBack(file);

	//	default				: log_('Insert : '+ file);
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
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

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionDelete(p){
	src = collection[p].name;
	
	if($('#'+src).hasClass('isLocked')){
		alert("Ce dossier est protégé contre la suppression");
		return false;
	}

	message = $('#'+src).hasClass('isDir')
		? "Voulez vous supprimer ce dosssier et TOUT son contenu ?"
		: "Voulez vous supprimer ce fichier ?";

	if(!confirm(message)){
		//log_("REMOVE CANCELED BY USER");
		return false;
	}
	
	var get = $.ajax({
		url: 'helper/action',
		dataType: 'json',
		data: {'action':'remove', 'src':collection[p].url}
	});
	
	get.done(function(r) {
//		if(r.callBack != null) eval(r.callBack);
		if(r.success == 'true'){
			$('div[id="'+src+'"]').fadeTo(218,0, function() {
				$(this).remove();
			});
			makeDragAndDrop();
		}
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionClipBoard(url){
	prompt("URL du media : ", url);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionMove(src, dstElement){
	
	// Si parent alors URL = ID si non URL = DOSSIER + ID
	dst = dstElement.hasClass('parent') ? dstElement.attr('id') : folder+'/'+dstElement.attr('id');

	var get = $.ajax({
		url: 'helper/action',
		data: {'action':'move', 'src':folder+'/'+src, 'dst':dst},
		dataType: 'json'
	});
	
	get.done(function(r) {
	//	if(r.message  != null) console.log(r.message);
	//	if(r.callBack != null) eval(r.callBack);
		if(r.success == 'true'){
			$('#'+src).remove();
			makeDragAndDrop();
			setTimeout(function() {
				folderView(true);
			}, 100)
		} else {
			$('#folderWay').append('<div class="no-move">Impossible de déplacer ce fichier ici.</span>');
			setTimeout(function() {
				$('#folderWay .no-move').fadeTo(218, 0, function() {
					$(this).remove();
				});
			}, 3000)		
		}
	});

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionRename(position){
	
	var e		= collection[position].name;
	
	$e = $('div[id="'+e+'"]');
	var field	= $e.find('div.name input.field');
	var old		= field.val();
	var changed	= false;

	field.removeAttr('readonly');
	
	field.unbind('keydown');
	field.unbind('blur');

	field.bind({
		'keydown' : function(e){
			if(e.keyCode == '13'){ // ENTER
				
				var get = $.ajax({
					url: 'helper/action',
					data: {'action':'rename', 'src':folder+'/'+old, 'dst':folder+'/'+$(this).val()},
					dataType: 'json'
				});
				
				get.done(function(r) {
					
					changed = true;
					//	if(r.message  != null) log_(r.message);
					//	if(r.callBack != null) eval(r.callBack);
						if(r.success == 'true'){
							field.parent().parent().attr('id', field.val()); 
							field.prop('readonly', 'readonly');
							collection[position].url = collection[position].url.replace(/\\/g,'/').replace(/\/[^\/]*\/?$/, '') + '/' + field.val();
						}
				});
			}else
			if(e.key == 'esc'){
				field.val(old);
				field.removeClass('fieldFocus');
				//log_('RENAME ANNULE PAR L\'UTILISATEUR (ESCAPE)');
			}
		},
		'blur' : function(e){
			if(!changed) field.val(old);
			field.removeClass('fieldFocus');
			//log_('RENAME ANNULE PAR L\'UTILISATEUR (PERTE DE FOCUS)');
		}
	});	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionViewVideo(url){
	window.open('/admin/media/helper/player-video?'+url, 'audio', '');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionViewAudio(url){
	window.open('/admin/media/helper/player-audio?'+url, 'video', '');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionVignette(url){
	
	var remote = $.ajax({
		url: '/admin/media/helper/action',
		dataType : 'json',
		data : {'action':'vignette', 'src':url}
	}).done(function(r) {
		//if(r.callBack != null) eval(r.callBack);
	});
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionNewDirectory(newDir){

//	var newDir = prompt("Nom du nouveau dossier : ");
	if(newDir == null){
	//	log_("NEWDIR CANCELED BY USER");
		return false;
	}

	var get = $.ajax({
		url: 'helper/action',
		data: {'action':'newdir', 'src':folder+'/'+newDir},
		dataType: 'json'
	});
	
	get.done(function(r){
		
		if(r.success == 'true'){

			collection.push({
				'name'	: newDir,
				'tags'	: 'isDir',
				'type'	: 'dir',
				'url'	: folder+'/'+newDir
			});
			
			last = collection[collection.length - 1];
			nd  = folderlElement(last, (collection.length-1));
			gtp = $('#main div.parent');

			if(gtp.length > 0){
				nd.appendTo(gtp.eq(0));
			}else{
				nd.prependTo('#main');
			}
			spreadGrid();
			makeDragAndDrop();
			folderView(true);
		}
	});

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
function actionWide(icn, url){

	var icone 	= $(icn).getElement('.img').setStyle('display', 'none');
	var image	= new Image();

	image.onload = function(){
		icone.src = url;
		icone.setStyles('display', '');

		$(icn).addClass('imgResize');
		folderElementSize(mySlider.step, (mySlider.step * factor), $(icn));

	//	icone.alt = icone.src;
	}

	image.src = url;
}
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionMetadata(url){
	modalMetaData(url);
	//panelMetaData(url);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionLock(p){
	
	src = collection[p].name;
	e = $('#'+src);
	
	icoLock = e.find('.lock')[0];

	var remote = $.ajax({
		url : '/admin/media/helper/action',
		data : {'action':'lock', 'src':collection[p].url},
		dataType : 'json'
	}).done(function(data) {
		if(data.message == 'LOCK'){
			e.addClass('isLocked');
			icoLock.src = '/admin/media/ui/img/media-locked.png';
		}else
		if(data.message == 'UNLOCK'){
			e.removeClass('isLocked');
			icoLock.src = '/admin/media/ui/img/media-unlocked.png';
		}
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionDuplicate(url){
	if(confirm("Voulez vous dupliquer ce fichier ?")){
		
		var ajax = $.ajax({
			url : 'helper/action',
			data : {'action':'duplicate', 'src':url},
			dataType :'json'	
		}).done(function() {
			folderView(true);
		})
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelShow(to, name){

	panelView = name;
	$('#panel, #action').css({'height' : to+'px', 'display' : 'block', 'opacity' : '0'});
	
	$('#main_').animate({
		'top' : (paneltop + to)
	}, 218, function() {
		$('#panel, #action').fadeTo(150, 1);
	});

	$('#button-hidepanel').css('display', '');
	
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelHide(){
	panelView = '';
	$('#panel').fadeTo(218, 0);
	$('#main_').animate({
		'top': panelTop
	}, 218);
	
	$('#button-hidepanel').css('display', 'none');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelUpload(){
	
	if ($('#modal-upload').css('display') == 'none') {
		modalShowUpload();
	} else {
		modalHideUpload();
	}
	/*if(panelView != 'upload'){
		$('#action .controls').css('display', 'none');
		panelShow(150, 'upload');
		$('#panelFrame').attr('src', 'helper/upload?f='+folder);
	}else{
		$('#action .controls').css('display', 'block');
		panelHide();
	}*/
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function modalPromptDir() {
	$('#modal-newdir').fadeTo(218, 1, function() {
		$('#modal-newdir').children('input').focus();
	});
	$('#fade-wall').fadeTo(218, 1);
	/*$('#modal-newdir').css('left', (($(window).width() - 600) / 2));
	$('#modal-newdir').css('top', (($(window).height() - 400) / 2));*/
	
	$('#modal-newdir input').on('keydown', function(e) {
		if (e.keyCode == 13) {
			modalHideUpload();
			actionNewDirectory($('#modal-newdir input').val());
		}
	});
		
	$('#modal-newdir #newdir').on('click', function(e) {
		modalHideUpload();
		actionNewDirectory($('#modal-newdir input').val());
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function modalPref() {
	$('#modal-pref').fadeTo(218, 1);
	$('#fade-wall').fadeTo(218, 1);
	$('#modal-pref iframe').remove();
	var frame = $('<iframe src="/admin/media/pref-embed" />').appendTo('#modal-pref').css({
		'width' : '100%',
		'height' : '100%'
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function modalShowUpload() {
	
/*	$('#modal-upload').fadeTo(218, 1);
	$('#fade-wall').fadeTo(218, 1);
	$('#modal-upload .uploadcontainer').fadeTo(218, 1);
*/

	$('#modal-upload, #fade-wall, #modal-upload .uploadcontainer').fadeTo(218, 1);

	// Ban safari 5
	isSafari		= (/safari/.test(navigator.userAgent.toLowerCase())) ? true : false;
	isSafariFive	= (isSafari && /version\/5/.test(navigator.userAgent.toLowerCase())) ? true : false;
	
	/* Mettre a jour les path d'upload si déjà chargé */
	var uploadPath	= $('#path').attr('data-url');

	// SI ON A ACCES AU FILEREADER DU BROWSER
	if(typeof FileReader !== 'undefined' && !isSafariFive) {
		
		// NE PAS LANCER PLUSIEURES INSTANCES
		if (typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadifive')) === "undefined") {
			
			// SI LA MODAL EST ACTIVEE SUR UPLOADIFY ALORS QU'ON DROP, DESTROY
			if(typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadify')) === "object") {
				$('#modal-upload .uploadcontainer #file_upload').uploadify('destroy');
			}
			
			$('#modal-upload .uploadcontainer #file_upload').uploadifive({
				'buttonText'   : 'Parcourir',
				'auto'         : true,
				'formData'     : {'test' : 'something'},
				'queueID'      : 'queue',
				'uploadScript' : 'helper/upload-action?f='+uploadPath,
				'onSelect'     : function(event,ID,fileObj) {
				},
				'onDrop' : function(file, count) {
				},
				'onUploadComplete' : function(file, data) {
				},
				'onQueueComplete' : function() {					
					modalHideUpload();
					folderView(true);
					isDrag = false;
					$('#queue').empty();
				}
			});
		}
	}else
	if(!isSafariFive) {
		// SUCKERS
		// NE PAS LANCER PLUSIEURES INSTANCES
		if (typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadify')) === "undefined") {
		
			// SI LA MODAL EST ACTIVEE SUR UPLOADIFIVE ALORS QU'ON DROP, DESTROY
			if (typeof($('#modal-upload .uploadcontainer #file_upload').data('uploadifive')) === "object") {
				$('#modal-upload .uploadcontainer #file_upload').uploadifive('destroy');
			}
					
	        $('#file_upload').uploadify({
				'swf'      : '/admin/media/ui/_uploadify/uploadify.swf?phpsessid='+phpsid,
				'auto'     : true,
				'formData' : {'test' : 'something'},
	            'uploader' : 'helper/upload-action?f='+$('#path').attr('data-url'),
	            'width'    : 100,
	            'buttonText' : 'Parcourir',
				'onUploadComplete' : function(file) {
				},
				'onQueueComplete' : function() {
					modalHideUpload();
					folderView(true);
					isDrag = false;
					$('#queue').empty();
				},
				'onUploadSuccess' : function(file, data, response) {
				},
				'onInit' : function(instance) {
					// déplacer la queue dans le container
					$('#'+instance.settings.queueID).appendTo("#queue");
				},
				'onUploadError' : function(file, errorCode, errorMsg, errorString) {
				}
	        });
		}

	}else{
		alert('En raison d\'un bug inhérent à la version de votre navigateur, l\'upload de fichiers est indisponible. Merci de mettre à jour votre navigateur.');
		modalHideUpload();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function modalHideUpload() {
	
	//console.log('hello !')
	
	$('#fade-wall').fadeTo(218, 0, function() {
		$('#fade-wall').css('display', 'none');
	});
	$('#modal-upload').fadeTo(218, 0, function() {
		$('#modal-upload').css('display', 'none');
	});
	$('#modal-meta').fadeTo(218, 0, function() {
		$('#modal-meta').empty();
		$('#modal-meta').css('display', 'none');
	});
	
	$('#modal-newdir').fadeTo(218, 0, function() {
		$('#modal-newdir').css('display', 'none');
	});
	
	$('#modal-pref').fadeTo(218, 0, function() {
		$('#modal-pref').css('display', 'none');
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelMaintenance(){
	if(panelView != 'maintenance'){
		panelShow(40, 'maintenance');
		$('panelFrame').src = myPath+'ressource/lib/media.maintenance.php';
	}else{
		panelHide();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelPref(){
	if(panelView != 'pref'){
		panelShow(80, 'pref');
		$('panelFrame').src = myPath+'ressource/lib/media.pref.php';
	}else{
		panelHide();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelAnalyse(url){
	if(panelView != 'analyse'){
		panelShow(80, 'analyse');
		$('panelFrame').src = myPath+'ressource/lib/media.analyse.php?folder='+url;
	}else{
		panelHide();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelMetaData(url){
	panelView = 'metadata';
	panelShow(220, 'meta');
	$('panelFrame').src = myPath+'ressource/lib/media.metadata.php?url='+url;
}

function modalMetaData(url) {

	$('#modal-meta').fadeTo(218, 1);
	$('#fade-wall').fadeTo(218, 1);
	/*$('#modal-meta').css('left', (($(window).width() - 600) / 2));
	$('#modal-meta').css('top', (($(window).height() - 400) / 2));*/
	
	var frame = $('<iframe src="/admin/media/helper/metadata?url='+url+'" />');
	frame.appendTo('#modal-meta');
	frame.css({
		'border' : 'none',
		'height' : $('#modal-meta').innerHeight()+'px',
		'width' : $('#modal-meta').innerWidth()+'px'
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function panelMetaDataClose(){
	panelView = '';
	panelShow({'height' : '10px'});
	$('panelFrame').src='index.php?n';
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function maskByClass(){

	$('#action .filter').each(function(i, chk){	
		v = ($(chk).prop('checked')) ? 'none' : '';
		$('.'+chk.val()).css('display', v);
	});
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
function sizeInit(url){

	overlay = new Element('div', {
		'id':'mbOverlay',
		'styles':{ 'opacity': 0.75 },
		'events':{ 'click'  : sizeKill }
	}).inject($(document.body));

	pp = new Element('div', {
		'id' : 'overSize',
		'class' : 'overlayPanel'
	}).inject($(document.body));

	ii = new Element('iframe', {
		'src'			: myPath+'ressource/lib/media.size.php?src='+url,
		'height'		: '100%',
		'width'			: '100%',
		'frameborder'	: '0'
	}).inject(pp);

}
	function sizeKill(){
		$('overSize').destroy();
		$('mbOverlay').destroy();
	}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function actionPdfToImage(url){

	var remote = new Request.JSON({
		url: myPath+'ressource/lib/media.action.php',
		onComplete:function(r){
		//	if(r.message  != null) log_(r.message);
		//	if(r.callBack != null) eval(r.callBack);

			if(r.success == true){
				folderNav(folder);
			}
		}
	}).get({
		'action'	: 'pdfToImage',
		'src'		: url
	});

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function distantDownload(){

	if($('#distantUpload').hasClass('doing')) return false;	
	$('#distantUpload').addClass('doing');

	var distant = $.ajax({
		url : '/admin/media/helper/action',
		type: 'post',
		dataType : 'json',
		data : {
			'action': 	'download',
			'data':		$('#distantUpload').val(),
			'folder': 	folder
		}
	}).done(function(data){

		$('#distantUpload').val('').removeClass('doing');
		modalHideUpload();
		folderView(true);

	}).fail(function(data){
		$('#distantUpload').removeClass('doing');
		alert("Fichier impossible a récupérer");
	});

}

/* Modal generator
* modal.open({ type : 'video', url : el.url });
* */
var modal = {

	open : function(data) {
		if (data.type == 'video') {
			this.frame = this.buildFrame(data);
			this.buildBody(data);
		}
	},

	buildFrame : function(data) {
		var _class = 'genericmodal';
		if (typeof data.class !== 'undefined') {
			_class = data.class;
		}

		return $('<div id="modal-'+data.type+'" class="'+_class+'" style="display:none;" />');
	},

	buildBody : function(data) {

		if (data.type == 'video') {
			this.ext = this.getExt(data.url);

			// build flowplayer 3.x
			if (this.ext == 'flv') {
				$.getScript("/admin/core/ui/_flowplayer/flowplayer-3.2.11.min.js")
					.done($.proxy(function(script, status) {
						this.body = $('<a href="'+data.url+'" id="player" />').appendTo(this.frame);
						this.show(data);
					}, this ));


			// build flowplayer 5.x
			} else {
				$.getScript("/admin/core/ui/_flowplayer5/flowplayer.min.js")
					.done($.proxy(function(script, status) {
						this.body  = $('<div id="player" />').appendTo(this.frame);
						this.video = $('<video src="'+data.url+'" />').appendTo(this.body);
						this.show(data);
					}, this ));
			}
		}


	},

	show : function(data) {

		$('#fade-wall').fadeTo(218, 1);
		this.frame.appendTo('body').css('display', 'block');

		$(document).on('keydown', $.proxy(function(e) {
			if (e.keyCode == 27) {
				this.hide(data);
			}
		}, this ));
		$('#fade-wall').on('click', $.proxy(function() {
			this.hide(data);
		}, this ));

		if (data.type == 'video') {

			if (this.ext == 'flv') {
				flowplayer("player", "/admin/core/ui/_flowplayer/flowplayer-3.2.12.swf", {
					clip : {
						autoPlay: true
					}
				});
			} else {
				this.player = $('#player').flowplayer({ swf: "/admin/core/ui/_flowplayer5/flowplayer.swf" });
				this.frame.css('height', 'auto');
			}
		}
	},

	hide : function(data) {
		if (data.type == 'video') {
			if (this.ext == 'flv') {
				$f().stop();
				$f().unload();
			} else {
				this.player.data('flowplayer').stop();
				this.player.data('flowplayer').unload();
			}
		}

		this.frame.remove();
		$('#fade-wall').hide();
	},

	getExt : function(filename) {
		return filename.split('.').pop();
	}

}








