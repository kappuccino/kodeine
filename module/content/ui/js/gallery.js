'use strict';

var gallery = {
	itemedMode: false,
	display: '',
	id_type:'',
	id_item:'',
	pickAlbum: null,
	pickMode: false,
	pickModel: null,

	/////////////////////////////////

	models: {},
	views: {},
	collections: {},
	router: {}
};

// MODELS //////////////////////////////////////////////////////////////////////////////////////////////////////////////

gallery.models.media         = Backbone.Model.extend({

	defaults: {
		opened: false
	},

	initialize: function() {
	}

});


// COLLECTIONS /////////////////////////////////////////////////////////////////////////////////////////////////////////

gallery.collections.media    = Backbone.Collection.extend({

	model: gallery.models.media,

	url: 'helper/gallery-view'

});

gallery.collections.tree     = Backbone.Collection.extend({

	model: gallery.models.media,

	url: 'helper/gallery-album'

});

gallery.collections.path     = Backbone.Collection.extend({

	model: gallery.models.media,

	url: 'helper/gallery-path'

});


// VIEWS ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

gallery.views.view           = Backbone.View.extend({

	el: $('#galleryView'),

	initialize:function(){
		this.listenTo(gallery.collections.myMedia, 'reset',  this.fill);
		this.listenTo(gallery.collections.myMedia, 'add',    this.fillItem);

		this.id_album = this.$el.data('id_album') || 0;
		this.order = [];
		this.isSortable = false;

		this.changeDisplay();
	},

	clear: function(){
		if(this.isSortable) this.$el.sortable('destroy');
		this.$el.empty();
	},

	fill: function(){
		this.clear();
		gallery.collections.myMedia.each(this.fillItem, this);
		this.makeSortable();
		this.lazyLoad();
	},

	lazyLoad: function(){

		if(gallery.display == 'list') return;

		$('img.lazy', this.$el).lazyload({
			container: this.$el,
			effect: 'fadeIn'
		});

		// Forcer le repaint de la page
		this.$el.trigger('scroll');
	},

	fillItem: function(m){
		var view  = new gallery.views.viewItem({model: m});
		this.$el.append(view.render().el);
		view.postRender();
	},

	//////////////

	changeDisplay: function(){
		if(this.$el.hasClass(gallery.display)) return;
		$('body').removeClass('grid list').addClass(gallery.display);

		if(gallery.display == 'grid') this.lazyLoad();
	},

	nav: function(id){
		console.log('nav ID='+id);
		this.id_album = id;
		this.load();

		gallery.views.myPath.load();
		gallery.myRouter.navigate('album/'+this.id_album, {trigger: false});
	},

	load: function(){
		gallery.collections.myMedia.fetch({data: {
			'id_album': this.id_album,
			'id_type':  gallery.id_type
		}});
	},

	//////////////

	makeSortable: function(){

		var self = this;

		this.$el.sortable({
			distance: 5,
			helper: 'clone',
			appendTo: 'body',
			items: "> li",
			cursorAt: {left: -30},

			create: function(){
				self.order = self.$el.sortable('toArray', {attribute : 'data-idc'});
				self.$el.disableSelection();
				self.isSortable = true;
			},

			stop: function() {
				var serial  = self.$el.sortable('toArray', {attribute : 'data-idc'});

				if(self.order.toString() != serial.toString()){
					self.order = serial;
					var items  = [], albums = [], mod = {};
					var lis    = $(self.$el.sortable('option', 'items'), self.$el.sortable());

					_.each(lis, function(e){
						mod = gallery.collections.myMedia.get($(e).data('cid'));

						(mod.get('is_album'))
							? albums.push(mod.get('id_content'))
							:  items.push(mod.get('id_content'));
					});

					gallery.views.myApp.saveOrder(albums, items);
				}
			}
		});
	}

});

gallery.views.viewItem       = Backbone.View.extend({

	tagName:    'li',
	className:  'gItem clearfix',

	initialize: function(){
		this.listenTo(this.model, 'remove', this.destroy);
		this.listenTo(this.model, 'change', this.reRender);

		this.html = '';
	},

	events: {
		'click .small':         'nav',
		'click .icone':         'nav',
		'click .delete':        'kill',
		'click .visibility':    'visibility',
		'click .poster':        'poster',
		'click .alias':         'alias'
	},

	//////////////

	nav: function(){
		if(this.model.get('dragging')) return;

		if(this.model.get('is_album')){
			gallery.views.myView.nav(this.model.get('id_content'));
		}else
		if(this.model.get('is_item') && gallery.pickMode){
			parent.opener.gallery.views.myApp.togglePosterRemote(gallery.pickAlbum, gallery.pickModel, this.model.get('id_content'));
			window.close();
		}
	},

	kill: function(e){
		e.stopPropagation();

		var cid = this.$el.data('cid');
		var idc = this.model.get('id_content');

		gallery.views.myApp.remove(cid, idc);
	},

	visibility: function(e){
		e.stopPropagation();
		var target  = $(e.target);
		var state   = target.hasClass('off') ? 'OFF' : 'ON';
		gallery.views.myApp.toggleVisibility(this.model.get('id_content'), state);

		target.toggleClass('off');
	},

	poster: function(e){
		e.stopPropagation();

		var target  = $(e.target);
		var state   = target.hasClass('off') ? 'OFF' : 'ON';
		var item    = target.parents('.gItem');
		var toggle  = gallery.views.myApp.togglePoster(this, state, item);

		if(toggle){
			$('#galleryView .action .poster').not(target).addClass('off');
			target.toggleClass('off');
		}
	},

	alias: function(e){
		e.stopPropagation();
		gallery.views.myApp.createAlias(this.model.get('id_content'));
	},

	//////////////

	templateAlbum: _.template($('#view-album').html()),
	templateItem:  _.template($('#view-item').html()),

	reRender: function(){
		this.render().postRender();
	},

	render: function() {
		var data    = this.model.toJSON();

		if(data.is_album){
			this.html = this.templateAlbum(data)
		}else{
			this.html = this.templateItem(data);
		}

		this.$el.html(this.html);
		this.html = '';

		return this;
	},

	postRender: function(){
		this.$el.attr('data-idc', this.model.get('id_content'));
		this.$el.attr('data-cid', this.model.cid);

		this.icone();
		//console.log('ma() pour viewItem');
		//gallery.views.myApp.makeDroppable(this);

		if(gallery.id_item == this.model.get('id_content')){
			this.$el.addClass('current');

			var before = gallery.views.myView.$el.offset().top;
			var scroll = this.$el.offset().top - before;

			gallery.views.myView.$el.animate({
				scrollTop: scroll
			}, 500);

		}else{
			this.$el.removeClass('current');
		}
	},

	destroy: function(){
		this.remove();
	},

	//////////////

	icone: function(){
		var preview = this.model.get('preview');
		if(preview == undefined) return;

		var icone   = $('.icone', this.$el);
		var respon  = (preview.height > preview.width) ? 'height' : 'width';
		var img     = $('<img />').addClass('lazy responsive-'+respon).attr('data-original', preview.url);

		if(this.model.get('is_album')) img.addClass('posterized');

		icone.empty().append(img);
	}

});

gallery.views.tree           = Backbone.View.extend({

	initialize:function(options){

		options         = options || {};
		this.level      = options.level || 1;
		this.el         = options.el  || $('#galleryTree');
		this.$el        = $(this.el);
		this.collection = options.collection || gallery.collections.myTree;

		this.listenTo(this.collection, 'reset',  this.fill);

		this.id_album   = this.$el.data('id_album') || 0;
	},

	dynEl: function(){
		return this.$el;
	//	return ((this.$el.attr('id') == 'galleryTree') ? $('section', this.$el) : this.$el)
	},

	clear: function(){
		this.dynEl().empty();
	},

	fill: function(){
		this.clear();
		this.collection.each(this.fillItem, this);
	},

	fillItem: function(m){
		var view  = new gallery.views.treeItem({model: m, level: this.level});
	//	this.dynEl().append(view.render().el);
		this.$el.append(view.render().el);
		view.postRender();
	},

	//////////////

	load: function(root){
		root = root || false;

		gallery.collections.myTree.fetch({data: {
			root:     root,
			id_album: this.id_album,
			id_type:  gallery.id_type
		}});
	}

});

gallery.views.treeItem       = Backbone.View.extend({

	tagName:    'li',

	initialize: function(options){
		options     = options || {};
		this.level  = options.level || 0;
	},

	events: {
		'click .toggle':    'toggle',
		'click .item':      'nav'
	},

	//////////////

	toggle: function(e){
		e.stopPropagation();

		if(this.model.get('opened')){
			this.model.set('opened', false);
			this.$el.removeClass('opened');
		}else{
			this.model.set('opened', true);
			this.$el.addClass('opened');
			this.open();
		}
	},

	open: function(){
		var ul = this.item.next('ul');

		// Creation d'une nouvelle COLLECTION + VIEW
		this.col = new gallery.collections.tree;
		this.vue = new gallery.views.tree({
			level:      (this.level+1),
			el:         ul,
			collection: this.col
		});

		this.$el.append(this.vue.el);

		// Déclenche le chargement pour la vue/collection avec le model courant
		this.col.fetch({data: {
			'id_album': this.model.get('id_content'),
			'id_type':  gallery.id_type
		}});
	},

	nav: function(e){
		e.stopPropagation();
		gallery.views.myView.nav(this.model.get('id_content'));
	},

	//////////////

	padding: function(){
		var padding = this.level * 10
		this.item.css('padding-left', padding+'px');
	},

	template: _.template($('#tree-item').html()),

	render: function() {
		var data    = this.model.toJSON();
		var html    = this.template(data)

		this.$el.html(html);
		return this;
	},

	postRender: function(){

		this.$el.attr({
			'data-idc': this.model.get('id_content')
		});

		this.item = $('.item', this.$el);
		this.padding();

	//	console.log('makeDroppable() pour treeItem');
		gallery.views.myApp.makeDroppable(this, this.$el.find('.item .name'));
	}

});

gallery.views.path           = Backbone.View.extend({

	el: $('#galleryPath'),

	initialize:function(){
		this.listenTo(gallery.collections.myPath, 'reset',  this.fill);
	},

	events: {
	},

	clear: function(){
		this.$el.empty();
	},

	fill: function(){
		this.clear();
		var path = gallery.collections.myPath;
		var max  = path.length

		for(var i=0; i<max; i++){
			var item = new gallery.views.pathItem({model: path.models[i]});
			this.$el.append(item.render().el);
			item.postRender();

			if(i < max-1){
				var sep = new gallery.views.pathItem({sep: true});
				this.$el.append(sep.render().el);
			}
		}

	},

	//////////////

	nav: function(id){
		this.id_album = id;
		this.load();
		gallery.myRouter.navigate('album/'+this.id_album, {trigger: false});
	},

	load: function(){
		gallery.collections.myPath.fetch({data: {
			'id_album': gallery.views.myView.id_album,
			'id_type':  gallery.id_type
		}});
	}

});

gallery.views.pathItem       = Backbone.View.extend({

	tagName:    'li',
	className:  'clearfix',

	initialize: function(options){
		options     = options || {};
		this.sep    = options.sep || false;
	},

	events: {
		'click .name': 'nav'
	},

	//////////////

	nav: function(e){
		e.stopPropagation();
		gallery.views.myView.nav(this.model.get('id_content'));
	},

	//////////////

	templateItem:   _.template($('#path-item').html()),
	templateSep:    _.template($('#path-sep').html()),

	render: function() {
		var html    = (this.sep)
			? this.templateSep({})
			: this.templateItem(this.model.toJSON());

		this.$el.html(html);
		return this;
	},

	postRender: function(){
		this.$el.attr('data-idc', this.model.get('id_content'));

	//	console.log('makeDroppable() pour pathItem');
		gallery.views.myApp.makeDroppable(this);
	}

});

gallery.views.action         = Backbone.View.extend({

	el: $('#galleryAction'),

	initialize:function(){
		gallery.display = $('body').data('display') || 'list';

		if(gallery.display == 'list') this.viewList();
		if(gallery.display == 'grid') this.viewGrid();

	},

	events: {
		'click #toggleGrid': 'viewGrid',
		'click #toggleList': 'viewList'
	},

	//////////////

	viewList: function(){
		gallery.display = 'list';
		$('body').attr('data-display', 'list');

		$('#toggleGrid', this.$el).show();
		$('#toggleList', this.$el).hide();

		if(gallery.views.myApp)  gallery.views.myApp.toggleDisplay('list');
		if(gallery.views.myView) gallery.views.myView.changeDisplay();
	},

	viewGrid: function(){
		gallery.display = 'grid';
		$('body').attr('data-display', 'grid');

		$('#toggleGrid', this.$el).hide();
		$('#toggleList', this.$el).show();

		if(gallery.views.myApp)  gallery.views.myApp.toggleDisplay('grid');
		if(gallery.views.myView) gallery.views.myView.changeDisplay();
	}

});


// APP /////////////////////////////////////////////////////////////////////////////////////////////////////////////////

gallery.views.app            = Backbone.View.extend({

	el: $('body'),

	initialize: function(){
		gallery.id_item   = this.$el.data('id_item');
		gallery.id_type   = this.$el.data('id_type');
		gallery.pickMode  = this.$el.data('pick');
		gallery.pickAlbum = this.$el.data('album');
		gallery.pickModel = this.$el.data('model');

		// Collections
		gallery.collections.myMedia = new gallery.collections.media;
		gallery.collections.myTree  = new gallery.collections.tree;
		gallery.collections.myPath  = new gallery.collections.path;

		// Views
		gallery.views.myAction  = new gallery.views.action;
		gallery.views.myView    = new gallery.views.view;
		gallery.views.myTree    = new gallery.views.tree;
		gallery.views.myPath    = new gallery.views.path;

		// Routeur
		gallery.myRouter = new gallery.router;
		Backbone.history.start();

		// Fix TOP
		$('#gallery').css('top', $('#gallery').position().top);
		$('#gallery').css({
			'bottom': 0,
			'position': 'absolute',
			'right': '0px',
			'left': '0px'
		});

		// Upload
		this.uploadInit();

		// Load Tree
		gallery.views.myTree.load(true);

		this.buttonEdit = this.$('#buttonEdit');


		// UI Item
		this.modalUpload = $('#modal-upload');
	},

	events: {
		'click #buttonAdd':         'addAlbum',
		'click #buttonEdit':        'editAlbum',
		'click #buttonImport':      'importAlbum',
		'click #buttonUpload':      'uploadShow',
		'click #buttonCloseUpload': 'clearModal',
		'click #distantDownload':   'distantDownload',
		'click #removeAllItems':    'removeAllItems',
		'click #sortAZ':            'sortAZ',
		'click #sortZA':            'sortZA'
	},

	addAlbum: function(){
		var id_album = gallery.views.myView.id_album
		document.location = 'gallery-album?id_album='+id_album+'&id_type='+gallery.id_type;
	},

	editAlbum: function(){
		var id_album = gallery.views.myView.id_album
		if(id_album > 0) document.location = 'gallery-album?id_content='+id_album;
	},

	importAlbum: function(){
		var id_album = gallery.views.myView.id_album
		document.location = 'gallery-import?id_album='+id_album+'&id_type='+gallery.id_type;
	},

	/////////

	uploadInit: function(){
		var self = this;

		document.addEventListener('dragleave', function(e) {
			// Stop FireFox from opening the dropped file(s)
			e.preventDefault(); e.stopPropagation();

			if (e.pageX === 0) {
				self.clearModal();
				self.isDrag = false;
			}

		}, false);

		document.addEventListener('dragenter', function(e) {
			// Stop FireFox from opening the dropped file(s)
			e.preventDefault(); e.stopPropagation();

			if (self.isDrag) return;
			self.isDrag = true;
			self.upload();

		}, false);

		document.addEventListener('dragover', function(e) {
			// Stop FireFox from opening the dropped file(s)
			e.preventDefault(); e.stopPropagation();

		}, false);
	},

	uploadShow: function(){
		this.clearModal();
		this.modalUpload.css('display', 'block');
	},

	upload: function(){
		this.uploadShow();

		var self         = this;
		var isSafari     = (/safari/.test(navigator.userAgent.toLowerCase())) ? true : false;
		var isSafariFive = (isSafari && /version\/5/.test(navigator.userAgent.toLowerCase())) ? true : false;

		if(typeof($('#file_upload').data('uploadifive')) !== "undefined") return;

		// SI ON A ACCES AU FILEREADER DU BROWSER
		if(typeof FileReader !== 'undefined' && !isSafariFive) {

			$('#file_upload').uploadifive({
				'buttonText':       'Parcourir',
				'auto':             true,
				'formData':         {'id_album': gallery.views.myView.id_album},
				'queueID':          'upqueue',
				'uploadScript':     'helper/gallery-upload',

				'onUpload':         function(){
					this.data('uploadifive').settings.formData = {
						'id_album': gallery.views.myView.id_album
					};
				},
				'onUploadComplete': function(file, data){
					var data = $.parseJSON(data);
					if(data.model) gallery.collections.myMedia.add(data.model);
				},
				'onQueueComplete':  function(file, data) {

					$('#upqueue').empty();
					self.clearModal();
				//	self.refresh();
					self.isDrag = false;
				}

				/*'onSelect':         function(event, ID, fileObj){ },
				 'onDrop':           function(file, count){ },
				 'onUploadComplete': function(file, data){ },*/
			});

		}else{
			$('#file_upload').uploadify({
				'buttonText':       'Parcourir',
				'auto':             true,
				'formData':         {'id_album': gallery.views.myView.id_album},
				'queueID':          'ipqueue',
				'uploader':         'helper/upload',
				'swf':              '../../../media/ui/_uploadify/uploadify.swf',

				'onUploadStart':    function(){
					$('#file_upload').data('uploadify').settings.formData = {
						'id_album' : gallery.views.myView.id_album
					};
				},
				'onQueueComplete':  function() {
					$('#queue').empty();
					self.clearModal();
				//	self.refresh();
					self.isDrag = false;
				}
			});
		}

	},

	clearModal: function(e){
		if(e != undefined) e.stopPropagation();

	//	this.wall.css('display', 'none');
	//	this.modalNewDir.css('display', 'none');
		this.modalUpload.css('display', 'none');
	//	this.modalMeta.css('display', 'none');
	},

	distantDownload: function(e){
		var remoteUrl = $('#distantUpload').val();
		if(remoteUrl.length == 0) return;

		$.ajax({
			url: 'helper/gallery-upload',
			type: 'post',
			dataType: 'json',
			data: {
				remoteUrl: remoteUrl,
				id_album: gallery.views.myView.id_album
			}
		}).done($.proxy(function(data){
			if(data.success && data.remote.length > 0){
				_.each(data.remote, function(value, key, list){
					if(value.model) gallery.collections.myMedia.add(value.model);
				});
			}

			this.clearModal();
		}, this));
	},

	/////////

	makeDroppable: function(instance, el){

		var self = this, el = el || this.$el;

		el.droppable({
			hoverClass: 'fly',
			tolerance: 'pointer',
			greedy: true,

			drop: function(e, ui) {

				var me   = ui.draggable.data('idc');
				var to   = instance.$el.data('idc');
				var cid  = ui.draggable.data('cid');
				var mod  = gallery.collections.myMedia.get(cid);
			//	var tree = gallery.views.myTree.$el.find('li[data-idc="'+ instance.$el.data('idc') +'"]');

			//	console.log('Drop Event', 'me:'+me, 'to:'+to, 'cid:'+cid);
			//	console.log('model',  mod.toJSON());
			//	console.log('instance', instance.model.toJSON());

				if(!instance.model.get('is_album')) return;
				if(me == to) return;
				if(mod.get('id_album') == instance.model.get('id_content')) return false;

			//	console.log("tree", tree);
			//	console.log('ok...');
			//	return;

			//  ????
			// 	gallery.views.myView.$el.sortable('disable');

				(mod.get('is_album'))
					? self.moveAlbum(cid, me, to)
					: self.moveItem(cid, me, to);

				gallery.views.myView.$el.sortable('enable');
			}
		});
	},

	/////////

	action: function(data, back, type){

	//	console.log("[XHR ACTION]", 'data', data, 'back', back);

		var xhr = $.ajax({
			url:        'helper/gallery-action',
			dataType:   'json',
			data:       data,
			type:       (type || 'get')
		});

		xhr.done(function(js){
			if(typeof back == 'function') back(js);
		});

	},

	saveOrder: function(albums, items){
		var id_album = gallery.views.myView.id_album;
	//	console.log("saveOrder", id_album, 'albums', albums, 'items', items);

		this.action({
			action:     'order',
			id_album:   id_album,
			items:      items.join('.'),
			albums:     albums.join('.')
		}, null, 'post')
	},

	moveItem: function(cid, me, to){
	//	console.log("moveItem()", "cid", cid, "me", me, "to", to);

		this.action({
			action:     'moveItem',
			id_album:   to,
			id_content: me
		}, function(js){

			if(js.success){
				gallery.collections.myMedia.get(cid).destroy();
				gallery.views.myView.makeSortable();
			}

		})
	},

	moveAlbum: function(cid, me, to){
	//	console.log("moveAlbum()", "cid", cid, "me", me, "to", to);

		this.action({
			action:     'moveAlbum',
			id_album:   to,
			id_content: me
		}, function(js){
			console.log(js);
			if(js.success){
				gallery.collections.myMedia.get(cid).destroy();
				gallery.views.myView.makeSortable();
			}
		})
	},

	remove: function(cid, me){

		var model  = gallery.collections.myMedia.get(cid);
		var action = model.get('is_album') ? 'removeAlbum' : 'removeItem';


		if(confirm('Voulez-vous vraiment supprimer cet élément ?')){
			this.action({
				action:     action,
				id_content: me
			}, function(js){

				if(js.success){
					gallery.collections.myMedia.get(cid).destroy();
					gallery.views.myView.makeSortable(); // reset ?
				}

			})
		}
	},

	toggleVisibility: function(me, state){
	//	console.log("Toggle visibility", me, state);

		this.action({
			action:     'toggleView',
			id_content: me,
			state:      state
		})
	},

	togglePoster: function(view, state, el){
				
		var model = view.model;

		// Pick un POSTER d'après un ITEM
		if(model.get('is_item')){

			this.action({
				action:     'togglePoster',
				id_album:   gallery.views.myView.id_album,
				id_content: model.get('id_content'),
				state:      state
			});

		}else
		if(model.get('is_album')){

			if(state == 'ON'){
				this.action({
					action:     'togglePoster',
					id_album:   model.get('id_content'),
					state:      state
				});

				this.togglePosterRemove(el, view);
			}else{

				window.open('gallery?pick&album='+model.get('id_content')+'&id_type='+gallery.id_type+'&model='+model.cid, '@', 'height=500');

				return false;
			}
		}

		return true;
	},

	togglePosterRemove: function(el, view){
		var icone = $('.icone', el);
		var tmp   = $('<div/>').append(view.templateAlbum(view.model.toJSON()));
		var src   = $('.media .icone img', tmp).attr('src');
		var img   = $('<img />').attr('src', src);

		icone.empty().append(img);
	},

	togglePosterRemote: function(id_album, model, id_content){

		this.action({
			action:     'togglePoster',
			id_album:   id_album,
			id_content: id_content,
			state:      'OFF'
		}, $.proxy(function(d){

			var mod = gallery.collections.myMedia.get(model);

			mod.set({
				'hasPoster': true,
				'preview': d.preview
			});

			gallery.views.myView.lazyLoad();

		}, this));

	},

	toggleDisplay: function(){
		this.action({
			action:  'toggleDisplay',
			id_type: gallery.id_type,
			display: gallery.display
		});
	},

	removeAllItems: function(){

		if(confirm('Voulez-vous supprimer tous les items présents dans cet album ?')){
			this.action({
				action:   'removeItemAll',
				id_album: gallery.views.myView.id_album,
				id_type:  gallery.id_type
			}, function(){

				var items = gallery.collections.myMedia.filter(function(item) {
					return item.get("is_item") === true;
				});

				if(items.length  > 0){
					_.each(items, function(n, i){
						n.destroy();
					});
				}

			});
		}

	},

	sort: function(){
		var albums=[], items=[];

		// Re-Render
		gallery.collections.myMedia.sort();
		gallery.views.myView.fill();

		// Save
		gallery.collections.myMedia.each(function(e){
			if(e.get('is_album')){
				albums.push(e.get('id_content'));
			}else{
				items.push(e.get('id_content'));
			}
		});

		gallery.views.myApp.saveOrder(albums, items);

		delete gallery.collections.myMedia.comparator;
	},

	sortAZ: function(){
		if(confirm('Voulez-vous classer la liste des items par ordre croissant ? cela effacera l\'ordre actuel')){

			gallery.collections.myMedia.comparator = function(m){
				return m.get('contentName');
			}

			this.sort();
		}
	},

	sortZA: function(){
		if(confirm('Voulez-vous classer la liste des items par ordre décroissant ? cela effacera l\'ordre actuel')){

			gallery.collections.myMedia.comparator = function(a, b) {
				if (a.get('contentName') > b.get('contentName')) return -1; // before
				if (b.get('contentName') > a.get('contentName')) return 1; // after
				return 0; // equal
			};

			this.sort();
		}

	},



	createAlias: function(id_content){
		this.action({
			action:  'createAlias',
			id_albm: gallery.views.myView.id_album,
			id_content: id_content
		}, function(d){

			if(d.success){
				var m = new gallery.models.media(d.new);
				console.log(m);
				gallery.views.myView.fillItem(m);
			}

		});
}

});


// ROUTEUR /////////////////////////////////////////////////////////////////////////////////////////////////////////////

gallery.router               = Backbone.Router.extend({

	routes: {
		'album/:id': 'album',
		'*path':     'defaut'
	},

	initialize: function(){
	/*	this.on('route:album', function(id) {
			gallery.views.myView.nav(id);
		});
		*/
	},

	album: function(id){
		gallery.views.myView.nav(id);
	},

	defaut: function(){
		gallery.views.myView.nav(gallery.views.myView.id_album);
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(function(){
	gallery.itemedMode = $('body').hasClass('itemed');
	gallery.views.myApp = new gallery.views.app;
});