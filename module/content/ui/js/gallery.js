imageZone	= 160
imageSize	= imageZone - 15;
mode		= 'sort';
treeReload	= [];

$(function(){
	$('#buttonAddAlbum').bind('click',	galleryAddAlbum);
	$('#buttonEdit').bind('click', 		galleryEditAlbum);
	$('#buttonImport').bind('click', 	galleryAddItem);
});

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryTree(id, level, where){

	var album = $.ajax({
		url: 'helper/gallery-album',
		data: {'id_type': id_type, 'id_album': id},
		dataType: 'json'
	});
	
	album.done(function(data) {
		galleryTreeBuild(where, level, data);
	});

}
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryTreeBuild(where, level, data){

	if(data.length == 0) return true;
	
	where.empty();

	$.each(data, function(i, d){
		
		var li_ 	= $('<li />').appendTo(where);
		var item_	= $('<div id="'+d.id_content+'" class="item clearfix dropme"/>').appendTo(li_);
		var act_	= $('<span class="toggle" style="margin-left:'+(level * 16)+'px" />').appendTo(item_).bind('click', function() {

			if($(this).parent('li').hasClass('opened')){
				galleryTreeClose($(this));
			}else{
				galleryTreeOpen(d.id_content, (level+1), $(this));
			}
		});

		$('<a class="name" href="#'+d.id_content+'">'+d.contentName+'</a>').appendTo(item_).bind('click', function() {
			galleryAlbum(d.id_content, false);
		});
		
		$('<ul />').appendTo(li_);

		console.log(treeReload);
		//// Reload tree
		if(treeReload.length > 0){
			treeReload.some(function(item, index){
				if(item == d.id_content) act_.trigger('click');
			});
		}	
	});
}

function galleryTreeOpen(id, level, me){
	var li_ = me.parent('li');
	var ul_ = li_.find('ul');
	
	li_.addClass('opened');
	
	galleryTree(id, level, ul_);
}

function galleryTreeClose(me){
	var li_ = me.parent('li');
	var ul_ = li_.find('ul');

	li_.removeClass('opened');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryAlbum(id, loadTree){

	id_album = id;

	var get = $.ajax({
		url: 'helper/gallery-view',
		dataType: 'json',
		data: {'id_type': id_type, 'id_album': id_album, 'size': imageSize}
	});
	
	get.done(function(data) {
		
		galleryView(data.items);
		galleryPath(data.path);

		(mode == 'sort')
			? makeSort()
			: makeMove();

		if(loadTree){
			$.each(data.path, function(i, p){
				treeReload.push(p.id_content);
			});
			
			galleryTree(0, 0, $('#galleryTree'));
		}
	});
	
	console.log('galleryAlbum : DONE');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryPict(me, isPoster){
	var top		= (isPoster) ? 6 : 0;
	
	var img_	= $('<img src="'+me.preview.contentItemUrl+'" height="'+me.preview.contentItemHeight+'" width="'+me.preview.contentItemWidth+'" class="loading" />');
		img_.css({'cursor' : 'pointer', 'margin-top' : Math.round((imageZone - me.preview.contentItemHeight - top) / 2)});
		img_.onload = function() {
			this.addClass('shadow').removeClass('loading');
		}

	return img_;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryView(data){
	
	var v = (id_album == '0') ? 'none' : '';
	$('#buttonEdit').css('display',   v);
//	$('buttonImport').setStyle('display', v);

	$('#galleryView').empty();
	
	if(data.length > 0){
		$.each(data, function(i, me){
			
			li_ 	= $('<li id="'+me.id_content+'" class="gItem dragme">').appendTo('#galleryView');
			title_ 	= $('<div class="title" />').appendTo(li_);
			icone_	= $('<div class="icone" />').appendTo(li_);
			action_ = $('<div class="action"/>').appendTo(li_);
			action_ = $('<span />').appendTo(action_);
			
			title_.html(me.contentName);

			kill_	= $('<a href="javascript:;"><img src="../core/ui/img/_img/media-delete.png" /></a>').appendTo(action_);
			kill_.bind('click', function() {
				galleryActionItemRemove(me.id_content);
			});
			
			// Ceci est un ALBUM
			//
			if(me.is_album){
				console.log('album !')
				
				li_.addClass('dropme');

				open_ = $('<a href="#'+me.id_content+'" />').appendTo(icone_);
				open_.bind('click', function() {
					galleryAlbum(me.id_content, false);
				});

				// Image generique d'un dossier
				if(me.id_poster == 0){
					img_ = $('<img src="../core/ui/img/_img/gallery-folder.png" style="margin-top:16px"/>');
				}
				// Item qui represente le dossier (poster)
				else{
					img_ = galleryPict(me.poster, true);
					img_.addClass('isPoster');
				}

				img_.appendTo(open_);
				editer_ = $('<a href="gallery-album?id_content='+me.id_content+'"><img src="../core/ui/img/_img/media-edit.png" /></a>');
				editer_.appendTo(action_);

				if(me.contentAlbumSyncFolder != ''){
					sync_ = $('<a href="gallery-import?id_type='+me.id_type+'&id_album='+me.id_content+'&sync='+me.contentAlbumSyncFolder+'" />');
					sync_.html('<img src="../core/ui/img/_img/media-sync.png" />');	
					sync_.appendTo(action_);
				}

			}
			// Ceci est un ITEM de gallery
			//
			else{
				
				me.contentItemHeight = parseInt(me.contentItemHeight);
				me.contentItemWidth  = parseInt(me.contentItemWidth);
				
				// Image
				//
				if(me.contentItemType == 'image'){
					
					var img = galleryPict(me, false);

					var wra = $('<a href="gallery-item?id_content='+me.id_content+'&id_type='+me.id_type+'" />');
						wra.appendTo(icone_);
					
					img.appendTo(wra).bind('click', function(e) {
						if(!e.meta){
							ev.preventDefault();
							galleryEditItem(me.id_content);
						}
					});
					
					eye_ = $('<a class="toggPoster '+((me.is_poster) ? 'is_poster' : 'isnot_poster')+'"><img src="../core/ui/img/_img/media-star.png" /></a>');
					eye_.appendTo(action_).bind('click', function() {
						var r = (me.is_poster) ? false : true;
						galleryActionTogglePoster(me.id_content, r);
					});
					
				}else

				// Video
				//
				if(me.contentItemType == 'video'){
					
					img_ = $('<img src="../core/ui/img/_img/media-file_quicktime.png" height="128" width="128" style="cursor:pointer;margin-top:'+Math.round((imageZone - 128) / 2)+'" />');
					img_.appendTo(icone_).bind('click', function() {
						galleryEditItem(me.id_content);
					});

				}else

				// Audio
				//
				if(me.contentItemType == 'audio'){
					
					img_ = $('<img src="../core/ui/img/_img/media-file_audio.png" height="128" width="128" style="cursor:pointer;margin-top:'+Math.round((imageZone - 128) / 2)+'" />');
					img_.appendTo(icone_).bind('click', function() {
						galleryEditItem(me.id_content);
					});

				}else

				// Audio
				//
				if(me.contentItemType == 'application' && me.contentItemMime == 'pdf'){
					
					img_ = $('<img src="../core/ui/img/_img/media-file_pdf.png" height="128" width="128" style="cursor:pointer;margin-top:'+Math.round((imageZone - 128) / 2)+'" />');
					img_.appendTo(icone_).bind('click', function() {
						galleryEditItem(me.id_content);
					});

				}
				
				// Generique
				//
				else{
					
					img_ = $('<img src="../core/ui/img/_img/media-file_file.png" height="128" width="128" style="cursor:pointer;margin-top:'+Math.round((imageZone - 128) / 2)+'" />');
					img_.appendTo(icone_).bind('click', function() {
						galleryEditItem(me.id_content);
					});

				}

				editer_ = $('<a href="gallery-item?id_content='+me.id_content+'&id_type='+id_type+'"><img src="../core/ui/img/_img/media-edit.png" /></a>');
				editer_.appendTo(action_);
			}

			eye_ = $('<a class="toggView '+((me.contentSee == '1') ? 'view' : 'notview')+'"><img src="../core/ui/img/_img/media-eye.png" /></a>');
			eye_.appendTo(action_).bind('click', function() {
				galleryActionToggleView(me.id_content, this.hasClass('view'));
			});

		});

	}else{
		nothing = $('<li class="nothing">Il n\'y a aucun element a afficher</li>').appendTo('#galleryView');
	}
}

console.log('galleryView : DONE');


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryPath(path){

	$('#galleyPathAlbum').empty();

	li_ = $('<li />').appendTo('#galleyPathAlbum');
	a_  = $('<a href="#0" id="0" class="dropme">Racine</a>').appendTo(li_).bind('click', function() {
		galleryAlbum(0, false);
	});

	if(path.length > 0){
		
		$.each(path, function(i, me){
			
			li_ = $('<li>&raquo;</li>').appendTo('#galleyPathAlbum');
			li_ = $('<li></li>').appendTo('#galleyPathAlbum');

			$('<a id="'+me.id_content+'" class="dropme">'+me.contentName+'</a>').appendTo(li_).bind('click', function() {
				galleryAlbum(me.id_content, false);
			});
			
		});
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryEditItem(id_content){
	document.location = 'content.gallery.item.php?id_content='+id_content+'&id_type='+id_type;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryAddAlbum(){
	document.location = 'content.gallery.album.php?id_type='+id_type+'&id_album='+id_album;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryEditAlbum(){
	if(id_album == '0'){
		alert('Cet album ne peut pas etre modifi�');
	}else{
		document.location='content.gallery.album.php?id_type='+id_type+'&id_content='+id_album;
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryAddItem(){
	document.location = 'content.gallery.import.php?id_type='+id_type+'&id_album='+id_album;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryActionItemRemove(id_content){

	if(confirm("Voulez vous supprimer cet element ?")){
		new Request.JSON({
			url: 'ressource/lib/gallery.action.php',
			onComplete: function(data){
				if(data.message == 'OK'){
					$(id_content).destroy();
					resetPosition();
				}else{
					alert(data.message);
				}
			}
		}).get({
			'action'		: 'remove',
			'id_type'		: id_type,
			'id_album'		: id_album,
			'id_content'	: id_content
		});
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryActionAlbumMove(src, dst){

	var remote = new Request.JSON({
		url: 'ressource/lib/gallery.action.php',
		onComplete:function(r){
			$(src).destroy();
			resetPosition();
		}
	}).get({
		'action'	: 'moveAlbum',
		'id_album'	: id_album,
		'me'		: src,
		'goto'		: dst
	});

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryActionItemMove(src, dst){

	var remote = new Request.JSON({
		url: 'ressource/lib/gallery.action.php',
		onComplete:function(r){
			$(src).destroy();
			resetPosition();
		}
	}).get({
		'action'	: 'moveItem',
		'id_album'	: id_album,
		'me'		: src,
		'goto'		: dst
	});

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryActionToggleView(id_content, view){

	var remote = new Request.JSON({
		url: 'ressource/lib/gallery.action.php',
		onComplete:function(r){

			var ico = $('galleryView').getElement('li[id='+r.id_content+'] .toggView');

			ico.removeClass('notview').removeClass('view');
			
			var d = (r.newContentSee == '1') ? 'view' : 'notview';
			ico.addClass(d);
		}
	}).get({
		'action'	: 'toggleView',
		'id_content': id_content,
		'contentSee': ((view) ? false : true)
	});

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryActionTogglePoster(id_content, is_poster){

	var remote = new Request.JSON({
		url: 'ressource/lib/gallery.action.php',
		onComplete: function(r){

			// all
			$$('.toggPoster').removeClass('is_poster').addClass('isnot_poster');
			
			// me
			var ico = $(r.id_content).getElement('.toggPoster');
			ico.removeClass('is_poster').removeClass('isnot_poster');
			
			var d = (r.is_poster) ? 'is_poster' : 'isnot_poster';
			ico.addClass(d);

		}
	}).get({
		'action'		: 'togglePoster',
		'id_album'		: id_album,
		'id_content'	: id_content,
		'is_poster'		: is_poster
	});

}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function getHash(){
	var href = top.location.href;
	var pos = href.indexOf('#') + 1;
	return (pos) ? href.substr(pos) : '';
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function makeSort(){

	mode = 'sort';

//	if($$('#galleryView li').length <= 1) return false;

	$('.dragme').css({
		'left' : '',
		'top' : ''
	});

	$('#galleryView').sortable({
		handle: '.icone',
		stop: function(e, u) {
			items = $(this);
			var f = [];
			var d = [];
			
			for(i=0; i<items.length; i++){
				($(items).eq(i).hasClass('dropme')) ? d.push($(items).eq(i).attr('id')) : f.push($(items).eq(i).attr('id'));
			}
			
			var get = $.ajax({
				url: 'helper/gallery-action.php',
				data: {	'action': 'positions',
						'id_album': id_album,
						'items': f.join(','),
						'albums': d.join(',')}
			});
			
			get.done(function(){
				console.log('je suis un ajax tout seul');
			});
		}		
	});
	
	mySortables = {};
	
	/*mySortables = new Sortables('', {
		constrain: false,
		clone: true,
		revert: false,
		onComplete: function(e){
			items = this.serialize();
			var f = [];
			var d = [];
			
			
			for(i=0; i<items.length; i++){
				($(items[i]).hasClass('dropme')) ? d.push($(items[i]).id) : f.push($(items[i]).id);
			}
			
			var remote = new Request.JSON({
				url: 'ressource/lib/gallery.action.php'
			}).get({
				'action'	: 'positions',
				'id_album'	: id_album,
				'items'		: f.join(','),
				'albums'	: d.join(',')
			});
		}
	});*/
	
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function makeMove(){

	mode = 'move';
	mySortables.detach();

	$$('.dragme').each(function(e){
		
		if(e.dd != undefined) e.dd.detach().stop();

		e.dd = new Drag.Move(e, {
			chain: 'cancel',
			handle: e.getElements('.icone'),
			droppables: $$('.dropme'),
			
		    onDrop: function(element, droppable){

				if(droppable){
			    	if(element.id != droppable.id && element.id != null && droppable.id != null){
						if(element.hasClass('dropme')){
					 		galleryActionAlbumMove(element.id, droppable.id);
						}else{
						//	if(droppable.id > 0){
					 			galleryActionItemMove(element.id, droppable.id);
					 	//	}else{
					 	//		alert("Vous ne pouvez pas mettre des images a la racines");
					 	//	}
						}
					}
				}
			},

			onStart: function() {
				this.elementOrg	= this.element;
				this.element 	= this.element.clone().addClass('dragging').setStyles({
					'position' 	: 'absolute',
					'opacity'	: 0.6
				}).set('id', this.element.id).injectInside(document.body);

				/*this.element.clone().addClass('dragging').setStyles({
					'position' : 'absolute'
				}).set('id', this.element.id).injectInside(document.body);*/
			},

			onComplete: function() {
				$$('.dragging').destroy();

				$$('.dragme').setStyles({
					'position' : 'static'
				});

				resetPosition();

				this.element.destroy();
				this.element 	= this.elementOrg;
				this.elementOrg = null;
			}
		});
		
		
		// Super important !
		e.setStyles({'position': ''});

	});
}


function resetPosition(){

	if(mode == 'move'){
		$$('.dragme').each(function(d){
			var cor = d.getCoordinates();
			d.setStyles({
				'position'	: '',
				'left'		: cor.left+'px',
				'top'		: cor.top+'px'
			});
		});
	}

}



