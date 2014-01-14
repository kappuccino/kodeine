// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    IMAGE PANEL

Dsnr.PanelImg = Dsnr.DsnrView.extend({

	el: $('#panel-img'),
	template: Handlebars.compile($('#tpl_ui_img').html()),

	events: {
		'click .btn-primary': 'saveChanges',
		'click .btn-danger': 'deleteChanges',
		'click .imgselect': 'mediaPicker'
	},

	initialize: function(opt) {

	},

	render: function() {

		this.delegateEvents(this.events); // avoid ghosts bindings between views

		this.$el.html(this.template(this.model.toJSON()));
		this.populateInput();

		return this;
	},

	populateInput: function() {
		this.$el.find('input.url').val(this.model.get('srcAttr'));
	},

	saveChanges: function() {
		this.model.set('srcAttr', this.$el.find('[data-nodeattr="src"]').val());
		this.model.set('heightAttr', this.$el.find('[data-nodeattr="height"]').val());
		this.model.set('widthAttr', this.$el.find('[data-nodeattr="width"]').val());
	},

	mediaPicker: function(){
		window.open('/admin/media/index?popMode=1&field=contentMedia&method=sort', 'pick', 'height=600,width=550;');
	},

	importMedia: function(media) {
		var media = 'http://'+SERVER+media;
		this.$el.find('input.url').val(media);
	},

	deleteChanges: function() {

	}


});


// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    TEXT PANEL

Dsnr.PanelText = Dsnr.DsnrView.extend({

	el: $('#panel-text'),
	template: Handlebars.compile($('#tpl_ui_text').html()),

	events: {
		'click .btn-primary': 'saveChanges',
		'click .btn-danger': 'deleteChanges'
	},

	initialize: function() {},

	render: function() {

		//this.delegateEvents(this.events); // avoid ghosts bindings between views

		this.$el.html(this.template(this.model.toJSON()));

		if (this.model.get('richtext')) this.handleRichText();

		return this;
	},

	handleRichText: function() {

		this._editor = $('textarea#dsnr-textarea').ckeditor({
			allowedContent: true // force keep inline styles
		});

		var editor = CKEDITOR.instances["dsnr-textarea"];
		CKEDITOR.plugins.addExternal('kodeineimg', '/admin/core/vendor/ckeditor-plugins/kodeineimg/', 'plugin.js');
		CKEDITOR.plugins.load('kodeineimg', function(plugins) {
			plugins['kodeineimg'].init(editor)
		});

	},

	nl2br: function(str) {
		return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2');
	},

	saveChanges: function() {

		//console.log('CHANGE :(', this.model, this.model.get('richtext'))
		//console.log($(CKEDITOR.instances['dsnr-textarea'].getData()).html())

		if (this.model.get('richtext')) // save rich text after cleaning CKed <p> tag with a $.html() trick
			return this.model.set('contents', CKEDITOR.instances['dsnr-textarea'].getData())

		if (this.model.get('textarea')) // save textarea
			return this.model.set('contents', this.nl2br($('textarea#dsnr-textarea').val()))

		// default, save input
		return this.model.set('contents', $('input#dsnr-textinput').val())
	},

	deleteChanges: function() {

	}


});

// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    LINK PANEL

Dsnr.PanelLink = Dsnr.DsnrView.extend({

	el: $('#panel-content'),
	template: Handlebars.compile($('#tpl_ui_link').html()),

	render: function() {

		this.$el.html(this.template(this.model.toJSON()));

		if (!this.model.get('connector')) {
			this.addDropdowns();
			this.registerEvents(); // register dropdown events
		} else {

			Dsnr.log('-- Loading saved connector <'+this.model.get('connector').connectorType+'>');

			switch (this.model.get('connector').connectorType) {
				case 'content' : new Dsnr.ConnectorContent({ model: this.model, el: $('#contentpanel', this.$el) }).render();
					break;
				case 'user' : new Dsnr.ConnectorUser({ model: this.model, el: $('#contentpanel', this.$el) }).render();
					break;
				case 'category' : new Dsnr.ConnectorCategory({ model: this.model, el: $('#contentpanel', this.$el) }).render();
					break;
			}

			$("select[name='selectfamily']", this.$el).remove();

		}

		return this;
	},

	addDropdowns: function() {
		$("select[name='selectfamily']", this.$el).selectpicker({style: 'btn-primary', menuStyle: 'dropdown-inverse'});
	},

	registerEvents: function() {
		Dsnr.log('-- Registering Linker dropdown options');
		$("select[name='selectfamily']").on('change', _.bind(this._selectFamily, this));
	},

	_resetForm: function() {
		Dsnr.error("TODO -- PanelContent.resetForm");
		return false;
	},

	_validateOption: function(option) { // tests if the passed option is a valid one
		if (option.val() == 0) return this._resetForm();
		return true;
	},

	_selectFamily: function(e) {

		var option = $(e.target).children('option:selected');
		if (!this._validateOption(option)) return; // test validation

		switch (parseInt(option.val())) {
			case 1 :
				this.currentPanel = new Dsnr.ConnectorCategory({ model: this.model, el: $('#categorypanel', this.$el) }).render();
				break;
			case 2 :
				this.currentPanel = new Dsnr.ConnectorContent({ model: this.model, el: $('#contentpanel', this.$el) }).render();
				break;
			case 3 :
				this.currentPanel = new Dsnr.ConnectorUser({ model: this.model, el: $('#userpanel', this.$el) }).render();
				break;
		}

	}

});

