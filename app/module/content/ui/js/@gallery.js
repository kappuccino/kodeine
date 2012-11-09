imageZone	= 160
imageSize	= imageZone - 15;
mode		= 'sort';
treeReload	= [];

document.addEvent('domready', function(){
	$('buttonAddAlbum').addEvent('click',	galleryAddAlbum);
	$('buttonEdit').addEvent('click', 		galleryEditAlbum);
	$('buttonImport').addEvent('click', 	galleryAddItem);
});

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryTree(id, level, where){

	new Request.JSON({
		url: 'ressource/lib/gallery.album.php',
		onComplete:function(data){
			galleryTreeBuild(where, level, data);
		}
	}).get({
		'id_type'	: id_type,
		'id_album'	: id
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryTreeBuild(where, level, data){

	if(data.length == 0) return true;
	
	where.empty();

	data.each(function(d){
		var li_ = new Element('li', {
		}).inject(where);

		var item_ = new Element('div', {
			'id': d.id_content,
			'class' : 'item clearfix dropme',
		}).inject(li_);

		var act_ = new Element('span', {
			'class'  : 'toggle',
			'styles': {
				'margin-left': (level * 16)+'px'
			},
			'events' : {
				'click' : function(){

					var li_ = this.getParent('li');

					if(li_.hasClass('opened')){
						galleryTreeClose(this);
					}else{
						galleryTreeOpen(d.id_content, (level+1), this);
					}
				}
			}
		}).inject(item_);

		new Element('a', {
			'class': 'name',
			'href' : '#'+d.id_content,
			'html' : d.contentName,
			'events': {
				'click': function(){
					galleryAlbum(d.id_content, false);
				}
			}
		}).inject(item_);

		new Element('ul').inject(li_);

		//// Reload tree
		if(treeReload.length > 0){
			treeReload.some(function(item, index){
				if(item == d.id_content) act_.fireEvent('click');
			});
		}	
	});
}

function galleryTreeOpen(id, level, me){

	var li_ = me.getParent('li');
	var ul_ = li_.getElement('ul');
	
	li_.addClass('opened');
	
	galleryTree(id, level, ul_);
}

function galleryTreeClose(me){
	var li_ = me.getParent('li');
	var ul_ = li_.getElement('ul');

	li_.removeClass('opened');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryAlbum(id, loadTree){

	id_album = id;

	new Request.JSON({
		url: 'ressource/lib/gallery.view.php',
  		headers: {'contentType': 'text/html'},
		evalScripts: true,
		onComplete:function(data){

			galleryView(data.items);
			galleryPath(data.path);

			(mode == 'sort')
				? makeSort()
				: makeMove();

			if(loadTree){
				data.path.each(function(p){
					treeReload.push(p.id_content);
				});
				
				galleryTree(0, 0, $('galleryTree'));
			}
		}
	}).get({
		'id_type'	: id_type,
		'id_album'	: id_album,
		'size'		: imageSize
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryPict(me, isPoster){
/*
	var imageSize_ = (isPoster) ? imageSize-10 : imageSize-2; // Border
	var imageZone_ = (isPoster) ? imageZone-10 : imageZone-2; // Border

	if(me.contentItemHeight > me.contentItemWidth){
		ratio	= imageSize_ / me.contentItemHeight;
		prompt	= '/w:' + imageSize;
	}else{
		ratio	= imageSize_ / me.contentItemWidth;
		prompt	= '/h:' + imageSize;
	}	
	
	width	= Math.round(me.contentItemWidth  * ratio);
	height	= Math.round(me.contentItemHeight * ratio);
*/
//	console.log(me.preview);
	var top		= (isPoster) ? 6 : 0;
	var img_	= new Element('img', {
		'src'		: me.preview.contentItemUrl,
		'height'	: me.preview.contentItemHeight, 
		'width'		: me.preview.contentItemWidth,
		'class'		: 'loading',
		'styles'	: {
			'cursor'		: 'pointer',
			'margin-top'	: Math.round((imageZone - me.preview.contentItemHeight - top) / 2)
		}
	});

	img_.onload = function(){
		this.addClass('shadow').removeClass('loading');
	}

	return img_;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryView(data){
	
	var v = (id_album == '0') ? 'none' : '';
	$('buttonEdit').setStyle('display',   v);
//	$('buttonImport').setStyle('display', v);

	$('galleryView').empty();

	if(data.length > 0){
		data.each(function(me){

			li_		= new Element('li').set('id', me.id_content).addClass('gItem dragme').inject('galleryView');
			title_	= new Element('div').inject(li_).addClass('title').inject(li_);
			icone_	= new Element('div').inject(li_).addClass('icone').inject(li_);
			action_	= new Element('div').addClass('action').inject(li_);
			action_ = new Element('span').inject(action_);

			title_.set('html', me.contentName);

			kill_ = new Element('a', {
				'html' : '<img src="ressource/img/media-delete.png" />',
				'href' : 'javascript:;',
				'events' : {
					'click' : function(){
						galleryActionItemRemove(me.id_content);
					}
				}
			}).inject(action_);


			//
			// Ceci est un ALBUM
			//
			if(me.is_album){
				li_.addClass('dropme');

				open_ = new Element('a', {
					'href' : '#'+me.id_content,
					'events' : {
						'click' : function(){
							galleryAlbum(me.id_content, false);
						}
					}
				}).inject(icone_);

				// Image generique d'un dossier
				if(me.id_poster == 0){
					img_ = new Element('img', {
						'src' 	 : 'ressource/img/gallery-folder.png',
						'styles' : {
							'margin-top' : '16px'
						}
					});
				}
				// Item qui represente le dossier (poster)
				else{
					img_ = galleryPict(me.poster, true);
					img_.addClass('isPoster');
				}

				img_.inject(open_);

				editer_ = new Element('a', {
					'href' : 'content.gallery.album.php?id_content='+me.id_content
				}).set('html', '<img src="ressource/img/media-edit.png" />').inject(action_);
				
				if(me.contentAlbumSyncFolder != ''){
					sync_ = new Element('a', {
						'href' : 'content.gallery.import.php?id_type='+me.id_type+'&id_album='+me.id_content+'&sync='+me.contentAlbumSyncFolder
					}).set('html', '<img src="ressource/img/media-sync.png" />').inject(action_);
				}

			}

			//
			// Ceci est un ITEM de gallery
			//
			else{
				me.contentItemHeight = parseInt(me.contentItemHeight);
				me.contentItemWidth  = parseInt(me.contentItemWidth);
				
				// Image
				//
				if(me.contentItemType == 'image'){
					var img = galleryPict(me, false);

					var wra = new Element('a', {
						'href' : 'content.gallery.item.php?id_content='+me.id_content+'&id_type='+me.id_type
					}).inject(icone_);

					img.inject(wra).addEvent('click', function(event){
						if(!event.meta){
							event.preventDefault();
							galleryEditItem(me.id_content);
						}
					});

					eye_ = new Element('a', {
						'class'		: 'toggPoster ' + ((me.is_poster) ? 'is_poster' : 'isnot_poster'),
						'html'		: '<img src="ressource/img/media-star.png" />',
						'events'	: {
							'click' : function(){
								var r = (me.is_poster) ? false : true;
								galleryActionTogglePoster(me.id_content, r);
							}
						}
					}).inject(action_);

				}else

				// Video
				//
				if(me.contentItemType == 'video'){
					img_ = new Element('img', {
						'src'		: 'ressource/img/media-file_quicktime.png',
						'height'	: 128, 
						'width'		: 128,
						'styles'	: {
							'cursor'		: 'pointer',
							'margin-top'	: Math.round((imageZone - 128) / 2)
						},
						'events' : {
							'click' : function(){
								galleryEditItem(me.id_content);
							}
						}
					}).inject(icone_);

				}else

				// Audio
				//
				if(me.contentItemType == 'audio'){
					img_ = new Element('img', {
						'src'		: 'ressource/img/media-file_audio.png',
						'height'	: 128, 
						'width'		: 128,
						'styles'	: {
							'cursor'		: 'pointer',
							'margin-top'	: Math.round((imageZone - 128) / 2)
						},
						'events' : {
							'click' : function(){
								galleryEditItem(me.id_content);
							}
						}
					}).inject(icone_);

				}else

				// Audio
				//
				if(me.contentItemType == 'application' && me.contentItemMime == 'pdf'){
					img_ = new Element('img', {
						'src'		: 'ressource/img/media-file_pdf.png',
						'height'	: 128, 
						'width'		: 128,
						'styles'	: {
							'cursor'		: 'pointer',
							'margin-top'	: Math.round((imageZone - 128) / 2)
						},
						'events' : {
							'click' : function(){
								galleryEditItem(me.id_content);
							}
						}
					}).inject(icone_);

				}
				
				// Generique
				//
				else{

					img_ = new Element('img', {
						'src'		: 'ressource/img/media-file_file.png',
						'height'	: 128, 
						'width'		: 128,
						'styles'	: {
							'cursor'		: 'pointer',
							'margin-top'	: Math.round((imageZone - 128) / 2)
						},
						'events' : {
							'click' : function(){
								galleryEditItem(me.id_content);
							}
						}
					}).inject(icone_);
				}


				editer_ = new Element('a', {
					'href' : 'content.gallery.item.php?id_content='+me.id_content+'&id_type='+id_type
				}).set('html', '<img src="ressource/img/media-edit.png" />').inject(action_);
			}

			eye_ = new Element('a', {
				'class'		: 'toggView '+((me.contentSee == '1') ? 'view' : 'notview'),
				'html'		: '<img src="ressource/img/media-eye.png" />',
				'events'	: {
					'click' : function(){
						galleryActionToggleView(me.id_content, this.hasClass('view'));
					}
				}
			}).inject(action_);

		});

	}else{
	
		nothing = new Element('li', {
			'class' : 'nothing'
		}).set('html', 'Il n\'y a aucun element a afficher').inject('galleryView');
	
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function galleryPath(path){

	$('galleyPathAlbum').empty();

	li_ = new Element('li').inject('galleyPathAlbum');
	a_  = new Element('a', {
		'href'		: '#0',
		'id'		: '0',
		'html'		: 'Racine',
		'class'		: 'dropme',
		'events'	: {
			'click' : function(){
				galleryAlbum(0, false);
			}
		}
	}).inject(li_);
	
	if(path.length > 0){
	//	li_.addClass('dropme');
		
		path.each(function(me, i){
	
			li_ = new Element('li', {'html' : ' &raquo; '}).inject('galleyPathAlbum');
			li_ = new Element('li').inject('galleyPathAlbum');
	
			new Element('a', {
				'id'		: me.id_content,
				'href'		: '#'+me.id_content,
				'events'	: {
					'click' : function(){
						galleryAlbum(me.id_content, false);
					}
				}
			}).addClass('dropme').set('html', me.contentName).inject(li_);
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
		alert('Cet album ne peut pas etre modifié');
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

	$$('.dragme').setStyles({
		'left' : '',
		'top' : ''
	});


	mySortables = new Sortables('#galleryView', {
		constrain: false,
		handle: '.icone',
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
	});
	
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



