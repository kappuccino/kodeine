'use strict';

var media = {
	models:         {},
	views:          {},
	collections:    {},
	router:         {}
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODELS

media.models.media          = Backbone.Model.extend({

	defaults: {
		path: ''
	}

});

media.models.meta           = Backbone.Model.extend({

	defaults: {
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// COLLECTIONS

media.collections.media     = Backbone.Collection.extend({

	model: media.models.media,

	url: 'helper/folder'

});

media.collections.path      = Backbone.Collection.extend({

	model: media.models.media,

	url: 'helper/path'

});

media.collections.meta      = Backbone.Collection.extend({

	model: media.models.meta,

	url: 'helper/meta'

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VIEWS

media.views.view            = Backbone.View.extend({

	el: $('#view'),

	initialize:function(){
		this.listenTo(media.collections.myMedia, 'reset',  this.fill);
		this.folder = '';
	},

	clear: function(){
		this.$el.sortable('destroy');
		this.$el.empty();
	},

	fill: function(){
		this.clear();
		media.collections.myMedia.each(this.fillItem, this);
	},

	fillItem: function(m){
		var view  = new media.views.viewItem({model: m});
		this.$el.append(view.render().el);
		view.postRender();
		media.views.myApp.size();
	},

	//////////////

	nav: function(folder){
		this.folder = folder;
		this.load();

		media.views.myPath.load();
		media.myRouter.navigate('/'+this.folder, {trigger: false});
	},

	navTo: function(folder){
		var goto = this.folder + '/' + folder;
		this.nav(goto);
	},

	load: function(){
		media.collections.myMedia.fetch({data: {
			'folder': this.folder
		}});
	}

});

media.views.viewItem        = Backbone.View.extend({

	tagName:    'li',
	className:  'item',

	initialize: function(){
		this.listenTo(this.model, 'remove', this.destroy);
		this.html = '';
	},

	events: {
		'click .icone':         'nav',
		'click .delete':        'kill',
		'click .duplicate':     'duplicate',
		'click .lock':          'lock',
		'click .pdfCover':      'pdfCover',
		'click .fullsize':      'fullsize',
		'keyup .title input':   'renameOnEnter',
		'blur  .title input':   'resetTitle',
		'click .playAudio':     'playAudio',
		'click .playVideo':     'playVideo',
		'click .poster':        'videoPoster',
		'click .meta':          'meta',
		'click .uri':           'uri',
		'click .select':        'select'
	},

	//////////////

	nav: function(){
		if(this.model.get('dragging')) return;
		if(this.model.get('is_folder')){
			media.views.myView.navTo(this.model.get('url'));
		}
	},

	kill: function(e){
		e.stopPropagation();

		var mod = media.collections.myMedia.get(this.model.cid)
		var me  = media.views.myView.folder + '/'+ mod.get('url');

		media.views.myApp.remove(me, mod);
	},

	duplicate: function(e){
		e.stopPropagation();

		var mod = media.collections.myMedia.get(this.model.cid)
		var me  = media.views.myView.folder + '/'+ mod.get('url');

		media.views.myApp.duplicate(me);
	},

	lock: function(e){
		e.stopPropagation();
		this.lockIcon.toggleClass('locked');
		media.views.myApp.lock(this.helperUrl());
	},

	pdfCover: function(e){
		e.stopPropagation();

		var mod = media.collections.myMedia.get(this.model.cid)
		var me  = media.views.myView.folder + '/'+ mod.get('url');

		media.views.myApp.pdfCover(me);
	},

	resetTitle: function(e){
		if(this.titleField.data('blur') == 'NO'){
			this.titleField.removeAttr('data-blur');
			return;
		}

		this.titleField.val(this.model.get('url'));
		if(e == undefined) this.titleField.blur();
	},

	renameOnEnter: function(e){
		if(e.keyCode == 27){
			this.resetTitle();
		}else
		if(e.keyCode == 13){
			var src = media.views.myView.folder + '/' + this.model.get('url');
			var dst = media.views.myView.folder + '/' + this.titleField.val();

			this.titleField.attr('data-blur', 'NO').blur();

			media.views.myApp.rename(src, dst);
		}
	},

	fullsize: function(e){
		e.stopPropagation();
		media.views.myApp.fullSize(this.helperUrl());
	},

	playAudio: function(e){
		e.stopPropagation();
		media.views.myApp.playAudio(this.helperUrl());
	},

	playVideo: function(e){
		e.stopPropagation();
		media.views.myApp.playVideo(this.helperUrl());
	},

	videoPoster: function(e){
		e.stopPropagation();
		media.views.myApp.videoPoster(this.helperUrl());
	},

	meta: function(e){
		e.stopPropagation();

		if(this.panelView != undefined){
			console.log('deja');
			return;
		}

		this.panelView = new media.views.meta({
			item:   this,
			url:    this.helperUrl()
		});

		this.$el.append(this.panelView.render().el);

		this.panelView.postRender();

	},

	uri: function(e){
		e.stopPropagation();
		alert(media.views.myView.folder+'/'+this.model.get('url'));
	},

	select: function(e){
		if(this.model.get('is_folder')){
			media.views.myApp.select(this.helperUrl(), 'folder');
		}else{
			media.views.myApp.select(this.helperUrl());
		}
	},

	//////////////

	helperUrl: function(){
		var mod = media.collections.myMedia.get(this.model.cid)
		var me  = media.views.myView.folder + '/'+ mod.get('url');
		return me;
	},

	//////////////

	templateFolder: _.template($('#view-folder').html()),
	templateItem:   _.template($('#view-item').html()),

	render: function() {
		var data = this.model.toJSON();

		if(this.model.get('is_folder')){
			this.html = this.templateFolder(data)
		}else{
			this.html = this.templateItem(data);
		}

		this.$el.html(this.html);
		this.html = '';

		return this;
	},

	postRender: function(){
		this.$el.attr('data-cid', this.model.cid);

		this.titleField = $('.title input', this.$el);
		this.lockIcon   = $('.lock', this.$el);

		this.icone();
		this.makeDraggable();

		media.views.myApp.makeDroppable(this);
	},

	destroy: function(){
		console.log('remove model, remove view');
		this.remove();
	},

	//////////////

	makeDraggable: function(){
		var self = this;

		this.$el.find().not('input').disableSelection();

		this.$el.draggable({
			distance: 30,
			helper: 'clone',
			appendTo: 'body',
			cursorAt: { left: -30 },
			stop: function(e, ui) {
				ui.helper.remove();
			}
		});

	},

	icone: function(){

		var preview = this.model.get('preview');
		if(preview == undefined) return;

		var icone  = $('.icone', this.$el);
		var respon = (preview.height > preview.width) ? 'height' : 'width';
		var img    = $('<img />').addClass('responsive-'+respon);

		img.attr('src', preview.url);

		icone.empty().append(img);
	}

});

media.views.path            = Backbone.View.extend({

	el: $('#path'),

	initialize:function(){
		this.listenTo(media.collections.myPath, 'reset',  this.fill);
	},

	events: {
	},

	clear: function(){
		this.$el.empty();
	},

	fill: function(){
		this.clear();

		var myPath  = media.collections.myPath;
		var max     = myPath.length;
		var path    = '';
		var mod     = {};
		var item    = {};

		for(var i=0; i<max; i++){
			if(i < max){
				var sep = new media.views.pathItem({sep: true});
				this.$el.append(sep.render().el);
			}

			mod  = myPath.models[i];
			path = path + '/' + mod.get('url');
			mod.set('path', path);

			item = new media.views.pathItem({model: mod});
			this.$el.append(item.render().el);
			item.postRender();
		}
	},

	//////////////

	nav: function(id){
		this.id_album = id;
		this.load();
		media.myRouter.navigate('album/'+this.id_album, {trigger: false});
	},

	load: function(){
		media.collections.myPath.fetch({data: {
			'folder': media.views.myView.folder
		}});
	}

});

media.views.pathItem        = Backbone.View.extend({

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
		media.views.myView.nav(this.model.get('path'));
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
		this.$el.attr('data-cid', this.model.cid);

		media.views.myApp.makeDroppable(this);
	}

});

media.views.meta            = Backbone.View.extend({

	tagName:    'div',
	className:  'floating-meta',

	initialize:function(options){
		options = options || {};

		this.url        = options.url;
		this.item       = options.item;
		this.collection = new media.collections.meta;

		this.listenTo(this.collection, 'reset',  this.fill);

		this.collection.fetch({data: {
			'url': this.url
		}});
	},

	events: {
		'click .save':      'save',
		'click .close':     'close'
	},

	//////////////

	save: function(){
		this.model.set('title',     this.inputTitle.val());
		this.model.set('caption',   this.inputCaption.val());

		this.model.save();
	},

	close: function(){
		delete this.item.panelView;
		this.$el.remove();
	},

	//////////////

	clear: function(){
		this.inputTitle.val('');
		this.inputDescription.val('');
	},

	fill: function(){
		var mod = this.collection.models[0];
		this.model = mod;

		console.log(mod);

		this.inputCaption.val(this.model.get('caption'));
		  this.inputTitle.val(this.model.get('title'));
	},

	//////////////

	template: _.template($('#modal-meta').html()),

	render: function(){
		var html = this.template({});
		this.$el.html(html);
		return this;
	},

	postRender: function(el){
		this.position(el);

		this.inputTitle   = $('textarea[name="title"]',       this.$el);
		this.inputCaption = $('textarea[name="caption"]', this.$el);
	},

	position: function(){

		var itemOff     = this.item.$el.offset();
		var itemWidth   = this.item.$el.width();
		var floatWidth  = this.$el.width();
		var length      = (itemOff.left + itemWidth) + floatWidth;

		(length > $(window).width())
			? this.$el.removeClass('left').addClass('right')
			: this.$el.removeClass('right').addClass('left');

	//	this.$el.css('height', $('#slider').val());
		media.views.myApp.size();
	}


});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

media.views.app             = Backbone.View.extend({

	el: $('body'),

	initialize: function(){

		// Position
		var path    = $('#path');
		var pos     = path.position();
		var height  = path.height();
		var top     = pos.top + height;
		$('#view').css('top', top);

		// Collections
		media.collections.myMedia   = new media.collections.media;
		media.collections.myPath    = new media.collections.path;

		// Views
		media.views.myView          = new media.views.view;
		media.views.myPath          = new media.views.path;

		// Routeur
		media.myRouter = new media.router;
		Backbone.history.start();

		// Upload
		this.uploadInit();

		// UI Item
		this.modalNewDir    = $('#modal-newdir');
		this.modalUpload    = $('#modal-upload');
		this.modalMeta      = $('#modal-meta');
		this.inputNewFolder = $('#modal-newdir input');
		this.wall           = $('#fade-wall');
		this.slider         = $('#slider');
	},

	events: {
		'click #button-folder':         'refresh',
		'click #button-newdir':         'newDir',
		'click #button-upload':         'upload',
		'click #fade-wall':             'clearModal',
		'click #cancelDir':             'clearModal',
		'keydown':                      'keydown',
		'click #newDir':                'newFolder',
		'keydown #modal-newdir input':  'newFolderOnEnter',
		'click #distantDownload':       'download',
		'change #slider':               'size'
	},

	/////////

	keydown: function(e){
		if(e.keyCode == 27) this.clearModal();
	},

	clearModal: function(e){
		if(e != undefined) e.stopPropagation();

		this.wall.css('display', 'none');
		this.modalNewDir.css('display', 'none');
		this.modalUpload.css('display', 'none');
		this.modalMeta.css('display', 'none');
	},

	wallShow: function(){
		this.wall.css('display', 'block');
	},

	refresh: function(){
		media.views.myView.load();
	},

	newDir : function(){
		this.clearModal();
		this.wallShow();
		this.modalNewDir.css('display', 'block');
		this.inputNewFolder.focus();
	},

	upload: function(){

		this.clearModal();
		this.wallShow();
		this.modalUpload.css('display', 'block');

		var self         = this;
		var isSafari     = (/safari/.test(navigator.userAgent.toLowerCase())) ? true : false;
		var isSafariFive = (isSafari && /version\/5/.test(navigator.userAgent.toLowerCase())) ? true : false;

		if(typeof($('#file_upload').data('uploadifive')) !== "undefined") return;

		// SI ON A ACCES AU FILEREADER DU BROWSER
		if(typeof FileReader !== 'undefined' && !isSafariFive) {

			$('#file_upload').uploadifive({
				'buttonText':       'Parcourir',
				'auto':             true,
				'formData':         {'f' : media.views.myView.folder },
				'queueID':          'queue',

				'uploadScript':     'helper/upload',
				'onUpload':         function(){
					this.data('uploadifive').settings.formData = {'f' : media.views.myView.folder};
				},
				'onQueueComplete':  function() {
					$('#queue').empty();
					self.clearModal();
					self.refresh();
					self.isDrag = false;
				}

				/*'onSelect':         function(event, ID, fileObj){ },
				'onDrop':           function(file, count){ },
				'onUploadComplete': function(file, data){ },*/
			});

		}else{
			/*alert('En raison d\'un bug inhérent à la version de votre navigateur, l\'upload de fichiers est '+
			'indisponible. Merci de mettre à jour votre navigateur.');
			this.clearModal();*/

			$('#file_upload').uploadify({
				'buttonText':       'Parcourir',
				'auto':             true,
				'formData':         {'f' : media.views.myView.folder },
				'queueID':          'queue',
				'uploader':         'helper/upload',

				'swf':              'ui/_uploadify/uploadify.swf',
				'onUploadStart':    function(){
					$('#file_upload').data('uploadify').settings.formData = {'f' : media.views.myView.folder};
				},
				'onQueueComplete':  function() {
					$('#queue').empty();
					self.clearModal();
					self.refresh();
					self.isDrag = false;
				}
			});
		}

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

	makeDroppable: function(instance){
		var self = this;

		instance.$el.droppable({
			hoverClass: 'fly',
			tolerance: 'pointer',
			greedy: true,
			drop: function(e, ui) {

				var folder  = media.views.myView.folder;

				var me      = ui.draggable.data('cid');
				var to      = instance.$el.data('cid');

				var me_     = media.collections.myMedia.get(me);
				var src     = folder+'/'+me_.get('url');

				if(instance.$el.parents('ul').attr('id') == 'path'){
					var to_ = media.collections.myPath.get(to);
					var dst = to_.get('path');
				}else{
					var to_ = media.collections.myMedia.get(to);
					var dst = folder+'/'+to_.get('url');
				}

				if(me == to) return;
				if(to_.get('is_file')) return;
				if(to_.get('path') == folder) return;

				self.moveTo(src, dst, ui);
			},

			over: function(e, ui) {
			}
		});
	},

	size: function(){
		var value  = parseInt(this.slider.val());

		$('.item').css('width', value+20);

		$('.item .media, .item .icone').css({
			'height':  value,
			'width':   value
		});

		$('.item .floating-meta').css({
			'height':  (value + 10)
		});

	},

	/////////

	select: function(file, prompt){
		var prompt = prompt || this.detectType(file);
		var file   = decodeURIComponent(file);

		switch(method){
			case 'editable':    parent.opener.editable.imageBack(file);
			case 'mce':         parent.opener.insertRichEditor(field, '<img src="'+file+'" />'); break;
			case 'sort':        parent.opener.mediaInsert(field, prompt+'@@'+file, 'sort'); break;
			case 'sort-embed':  parent.mediaInsert(field, prompt+'@@'+file, 'sort'); break;
			case 'line':
				var fld = parent.opener.document.getElementById(field);
				fld.value  = file;
				fld.fireEvent('change', fld);
				break;
		}
	},

	detectType: function(file){
		var ext = file.substr(file.lastIndexOf(".") + 1).toLowerCase();
		var def = {
			'image':        ['png', 'gif', 'jpeg', 'jpg', 'tiff', 'tif', 'psd', 'bmp'],
			'flash':        ['swf', 'fla'],
			'powerpoint':   ['ppt'],
			'excel':        ['xls', 'xlm', 'xlt'],
			'pdf':          ['pdf'],
			'doc':          ['doc', 'docx', 'txt'],
			'audio':        ['mp3', 'aif', 'aiff', 'wav'],
			'video':        ['mov', 'avi', 'm4v', 'mp4', 'mpg', 'mpeg', 'wmv', 'flv'],
			'html':         ['htm', 'html', 'php', 'php3', 'php4', 'php5'],
			'archive':      ['zip', 'tar', 'tgz', 'sit', 'rar', 'arj', 'sitx', 'sea', 'lha', 'lzh', 'bin', 'hqx', 'gz', 'tbz', 'z', 'taz']
		};

		for(var type in def){
			if($.inArray(ext, def[type]) !== -1) return type;
		}

		return 'unknown';
	},

	/////////

	action: function(data, back){
		console.log("[XHR ACTION]", 'data', data, 'back', back);

		var xhr = $.ajax({
			url:        'helper/action',
			dataType:   'json',
			data:       data
		});

		xhr.done(function(js){
			if(typeof back == 'function') back(js);
		});

	},

	moveTo: function(me, to, ui){
		this.action({
			action: 'move',
			src:    me,
			dst:    to
		}, function(d){
			if(d.success) ui.draggable.remove();
		})
	},

	remove: function(me, mod){
		if(!confirm('Supprimer ?')) return;

		this.action({
			action: 'remove',
			src:    me
		}, function(d){
			if(d.success) mod.destroy();
		})
	},

	duplicate: function(me){
		var self = this;
		this.action({
			action: 'duplicate',
			src:    me
		}, function(d){
			if(d.success) self.refresh();
		})
	},

	lock: function(me){
		var self = this;

		this.action({
			action: 'lock',
			src:    me
		}, function(d){
		})
	},

	pdfCover: function(me){
		var self = this;

		this.action({
			action: 'pdfCover',
			src:    me
		}, function(d){
			self.refresh();
		})
	},

	newFolder: function(){
		var self  = this;
		if(this.inputNewFolder.val() == '') return;

		this.action({
			'action': 'newdir',
			'src'   : media.views.myView.folder + '/' + this.inputNewFolder.val()
		}, function(){
			self.inputNewFolder.val('');
			self.clearModal();
			self.refresh();
		})
	},

	newFolderOnEnter: function(e){
		if(e.keyCode == 13) this.newFolder();
	},

	rename: function(src, dst){
		this.action({
			action: 'rename',
			src:    src,
			dst:    dst
		});
	},

	download: function(e){
		var self = this;

		var xhr = $.ajax({
			url:        'helper/action',
			dataType:   'json',
			type:       'POST',
			data:       {
				action: 'download',
				src:    media.views.myView.folder,
				data:   $('#distantUpload').val()
			}
		});

		xhr.done(function(){
			self.clearModal();
			self.refresh();
		});
	},

	fullSize: function(me){
		window.open(me);
	},

	videoPoster: function(me){
		window.open('helper/poster?url='+me);
	},

	playVideo: function(me){
		window.open('helper/player-video?url='+me);
	},

	playAudio: function(me){
		window.open('helper/player-audio?url='+me);
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ROUTEUR

media.router                = Backbone.Router.extend({

	routes: {
		'':       'first',
		'*path':  'folder'

	},

	initialize: function(){
	},

	folder: function(){
		var f = '/'+Backbone.history.fragment;
		media.views.myView.nav(f);
	},

	first: function(){
		media.views.myView.nav('/media');
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(function(){
	media.views.myApp = new media.views.app;
});

// The End Bro'