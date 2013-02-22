'use strict';

var gallery = {
	id_type:'',
	models: {},
	views: {},
	collections: {},
	router: {}
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODELS

gallery.models.media         = Backbone.Model.extend({

	defaults: {
		opened: false
	},

	initialize: function() {
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// COLLECTIONS

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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VIEWS

gallery.views.view           = Backbone.View.extend({

	el: $('#galleryView'),

	initialize:function(){
		this.listenTo(gallery.collections.myMedia, 'reset',  this.fill);
		this.id_album = this.$el.data('id_album') || 0;
		this.order = [];
	},

	clear: function(){
		this.$el.sortable('destroy');
		this.$el.empty();
	},

	fill: function(){
		this.clear();
		gallery.collections.myMedia.each(this.fillItem, this);
		this.makeSortable();
	},

	fillItem: function(m){
		var view  = new gallery.views.viewItem({model: m});
		this.$el.append(view.render().el);
		view.postRender();
	},

	//////////////

	nav: function(id){
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

		this.$el.disableSelection();

		this.$el.sortable({
			distance: 30,
			helper: 'clone',
			appendTo: 'body',
			items: "> li",
			cursorAt: { left: -30 },

			create: function(){
				self.order = self.$el.sortable('toArray', {attribute : 'data-idc'});
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
	className:  'gItem',

	initialize: function(){
		this.listenTo(this.model, 'remove', this.destroy);

		this.html = '';
	},

	events: {
		'click .icone':         'nav',
		'click .delete':        'kill',
		'click .visibility':    'visibility',
		'click .poster':        'poster'
	},

	//////////////

	nav: function(){
		if(this.model.get('dragging')) return;
		if(this.model.get('is_album')){
			gallery.views.myView.nav(this.model.get('id_content'));
		}
	},

	kill: function(e){
		e.stopPropagation();

		var cid = this.$el.data('cid');
		var idc = this.model.get('id_content');

		gallery.views.myApp.removeItem(cid, idc);
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
		gallery.views.myApp.togglePoster(this.model.get('id_content'), state);

		target.toggleClass('off');
	},

	//////////////

	templateAlbum: _.template($('#view-album').html()),
	templateItem:  _.template($('#view-item').html()),

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
		//console.log('makeDroppable() pour viewItem');
		gallery.views.myApp.makeDroppable(this);

	},

	destroy: function(){
		console.log('remove model, remove view');
		this.remove();
	},

	//////////////

	icone: function(){
		var preview = this.model.get('preview');
		if(preview == undefined) return;

		var icone   = $('.icone', this.$el);
		var respon  = (preview.height > preview.width) ? 'height' : 'width';
		var img     = $('<img />').addClass('responsive-'+respon);

		img.attr('src', preview.url);

		if(this.model.get('is_album')){
			img.addClass('posterized');
		}

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
		this.dynEl().append(view.render().el);
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

		var ul = this.item.next('ul');

		if(this.model.get('opened')){
			this.model.set('opened', false);
			this.$el.removeClass('opened');
			return;
		}else{
			this.model.set('opened', true);
			this.$el.addClass('opened');
		}

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
		this.$el.attr('data-idc', this.model.get('id_content'));

		this.item = $('.item', this.$el);
		this.padding();

	//	console.log('makeDroppable() pour treeItem');
		gallery.views.myApp.makeDroppable(this);
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

/////////////////////////////

gallery.views.app            = Backbone.View.extend({

	el: $('body'),

	initialize: function(){
		gallery.id_type = this.$el.data('id_type');

		// Collections
		gallery.collections.myMedia     = new gallery.collections.media;
		gallery.collections.myTree      = new gallery.collections.tree;
		gallery.collections.myPath      = new gallery.collections.path;

		// Views
		gallery.views.myView   = new gallery.views.view;
		gallery.views.myTree   = new gallery.views.tree;
		gallery.views.myPath   = new gallery.views.path;

		// Routeur
		gallery.myRouter = new gallery.router;
		Backbone.history.start();

		// Load Tree
		gallery.views.myTree.load(true);

		this.buttonEdit = this.$('#buttonEdit');
	},

	events: {
		'click #buttonAdd':     'addAlbum',
		'click #buttonEdit':    'editAlbum',
		'click #buttonImport':  'importAlbum'
	},

	addAlbum: function(){
		document.location = 'gallery-album?id_type=' + this.id_type;
	},

	editAlbum: function(){
		var id_album = gallery.views.myView.id_album
		if(id_album > 0) document.location = 'gallery-album?id_content=' + id_album;
	},

	importAlbum: function(){
		var id_album = gallery.views.myView.id_album
		document.location = 'gallery-import?id_album='+id_album+'&id_type='+gallery.id_type;
	},

	/////////

	makeDroppable: function(instance){

		var self = this;

		instance.$el.droppable({
			hoverClass: 'fly',
			tolerance: 'pointer',
			greedy: true,
			drop: function(e, ui) {

				var me  = ui.draggable.data('idc');
				var to  = instance.$el.data('idc');
				var cid = ui.draggable.data('cid');
				var mod = gallery.collections.myMedia.get(cid);

				console.log('Drop Event', 'me:'+me, 'to:'+to, 'cid:'+cid);
				console.log('mod',      mod.toJSON());
				console.log('instance', instance.model.toJSON());

				if(!instance.model.get('is_album') || me == to) return;

				gallery.views.myView.$el.sortable('disable');

				(mod.get('is_album'))
					? self.moveAlbum(cid, me, to)
					:  self.moveItem(cid, me, to);

				gallery.views.myView.$el.sortable('enable');
			},

			over: function(e, ui) {
			}
		});
	},

	/////////

	action: function(data, back){

		console.log("[XHR ACTION]", 'data', data, 'back', back);

		var xhr = $.ajax({
			url:        'helper/gallery-action',
			dataType:   'json',
			data:       data
		});

		xhr.done(function(js){
			if(typeof back == 'function') back(js);
		});

	},

	saveOrder: function(albums, items){
		var id_album = gallery.views.myView.id_album;
		console.log("saveOrder", id_album, 'albums', albums, 'items', items);

		this.action({
			action:     'order',
			id_album:   id_album,
			items:      items.join('.'),
			albums:     albums.join('.')
		})
	},

	moveItem: function(cid, me, to){
		console.log("moveItem()", "cid", cid, "me", me, "to", to);

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
		console.log("moveAlbum()", "cid", cid, "me", me, "to", to);

		this.action({
			action:     'moveAlbum',
			id_album:   to,
			id_content: me
		})
	},

	removeItem: function(cid, me){
		console.log("Remove Item", 'cid', cid, 'me', me);

		if(confirm('???')){
			this.action({
				action:     'remove',
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
		console.log("Toggle visibility", me, state);

		this.action({
			action:     'toggleView',
			id_content: me,
			state:      state
		})
	},

	togglePoster: function(me, state){
		var id_album = gallery.views.myView.id_album;
		console.log("Toggle poster", me, state);

		this.action({
			action:     'togglePoster',
			id_album:   id_album,
			id_content: me,
			state:      state
		})

	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ROUTEUR

gallery.router               = Backbone.Router.extend({

	routes: {
		'album/:id': 'album',
		'*path':     'defaut'
	},

	initialize: function(){

		/*this.on('route:album', function(id) {
		 gallery.views.myView.nav(id);
		 });*/
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
	gallery.views.myApp = new gallery.views.app;
});