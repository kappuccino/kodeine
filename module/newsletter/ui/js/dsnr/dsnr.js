"use strict";

var Dsnr = {};

$(function() {

	var dsnr = new Dsnr({
			iframe: $('#dsnrframe'),
			style: 'ui/css/dsnr.css'
	});
});

Dsnr = function(opt) {

	// CONST
	Dsnr.DEBUG = true;
	Dsnr.VERSION = 5;
	Dsnr.BLOC = TEMPLATE_ID; // echoed on page

	// Available connectors for this DSNR app
	Dsnr.connectors = {
		'UserConnector' : Dsnr.ConnectorUser
	};

	// constructr
	this.$el = opt.iframe.contents();

	this
		.fetchTemplate()
		.then(_.bind(function(data) { // get template

			console.log('GOT TEMPLATE', data)

			this.templateData = data;
			return data;

		}, this))
		.then(_.bind(function(data) { // append template

			this.templatePosition = data.position;

			this.appendTemplate(data.contents, data.position); // add ajax contents in iframe
			this.appendStyles(opt.style); // add style link in <head>
			this.appendUiInject(); // add ui wrapper in iframe

			return
		}, this))
		.then(_.bind(function() { // start dsnr NodeScanner

			Dsnr.MyNodeCollection = new Dsnr.NodeCollection({
				startAt : this.$el.find('body')
			});

			Dsnr.Templates = new Array(); // templates store

			this.buildNodeModels(this.$el.find('*[data-editable]'));
			this.brandPage(); // "brands" the page with template values (ex: name, etc)

		}, this));

};

// Build Models & View for NodeScanner collection
Dsnr.prototype = {

	// builds all node models & views starting from root (<html />)
	// when done, refines its collections with templates nodes
	buildNodeModels: function(nodes) {

		Dsnr.Views = new Array(); // views storage
		for (var i = 0; i < nodes.length; i++) {

			var model = new Dsnr.NodeModel({
					node: $(nodes[i]),
					connectors: this.templateData['connectors'] })

				, view = new Dsnr.NodeView({
					el: $(nodes[i]),
					model: model,
					root: this.$el });

			Dsnr.Views.push(view);
			// push in the right collection
			Dsnr.MyNodeCollection.push(model);

		}

		this.buildTemplateModels();

	},

	// find the children nodes of every templateCollection found earlier.
	// These nodes will be adequately replaced by templateViews and templateModels
	// if needed (data-editable=true) and removed/added to the appropriate collections
	buildTemplateModels: function() {
		console.log('ready to build template models ')
	},

	appendTemplate: function(template, position) {

		$('#dsnrframe').contents()[0].write(this.templateData.layout.layout);
		$('#dsnrframe').contents().find('.dsnr-injector').eq(position).append(template)

		this.resizeFrame()
	},

	appendUiInject: function(url) {
		this.$el.find('body').append(
			'<div id="dsnr-ui-inject">' +
				'<div id="dsnr-toolbar-wrap"></div>' +
				'</div>'
		);
	},

	appendStyles: function(url) {
		this.$el.find('head').append(
			'<link rel="stylesheet" type="text/css" media="all" href="'+url+'">'
		);
	},

	resizeFrame: function() {
		// TODO responsive dsnr frame
	},

	brandPage: function() {
		$('.templatename').html('Edition : '+this.templateData.blocName);
		// append options btn and bind
		$('.templatename').append('<a class="btn btn-primary" disabled="disabled" data-toggle="options"><i class="fui-gear"></i></a>');

		// append save btn and bind
		$('.templatename').append('<a class="btn btn-primary" data-toggle="validate"><i class="fui-check-inverted"></i></a>')
			.on('click', _.bind(this.saveTemplate, this));

	},

	saveTemplate: function() {
		this.$el.find('#dsnr-ui-inject').remove();

		// clean around template
		var remove = $('#dsnrframe').contents().find('.dsnr-injector').eq(this.templatePosition).children('.dsnr-injector-text').remove();
		var template = $('#dsnrframe').contents().find('.dsnr-injector').eq(this.templatePosition).html()
		//var template = this.$el.find('.dsnr-injector').eq(this.templateData.position).html();


		Dsnr.log('-- Extracted template, saving...')

		//this.savePopup(_.bind(this.writeTemplate, this, template));
		this.savePopup(this.writeTemplate, template);
	},

	writeTemplate: function(template, name) {

		console.log('WRITE TEMPLATE ', template, name);
		this.setTemplate(template, name).done(_.bind(function(data) {

			if (!data.ok) return Dsnr.error('-- Error saving template') // error first

			Dsnr.log('-- Template saved !')
			window.location.reload();

		}, this));

	},

	savePopup: function(callback, template) {
		var overlay = $('<div class="save overlay" />').appendTo('body')
			, wrap    = $('<div class="wrap" />').appendTo(overlay)
			, title   = $('<div class="title">Enregistrer le bloc <br/>(entrez un nouveau nom pour dupliquer)</div>').appendTo(wrap)
			, input   = $('<input type="text" name="templatename" value="'+ this.templateData.blocName +'" />').appendTo(wrap)
			, btnError = $('<a class="btn btn-danger">Annuler</a>').appendTo(wrap).on('click', _.bind(function() {
				overlay.remove();
			}, this ))
			, btnSave = $('<a class="btn btn-primary">Sauver</a>').appendTo(wrap).on('click', _.bind(function() {

				console.log('applying cb')
				var name = input.val();
				callback.apply(this, [template, name]);

			}, this ));

		overlay.addClass('active');
	},

	// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	// SERVER ACTIONS

	setTemplate: function(template, name) {

		console.log('name', name);

		return $.ajax({
			url: 'helper/ajax-dsnr-bloc',
			dataType: 'json',
			type: 'POST',
			data: {
				contents: template,
				id: TEMPLATE_ID,
				position: this.templatePosition,
				name: $.trim(name)
			}
		});
	},

	fetchTemplate: function() {
		return $.ajax({
			url: 'helper/ajax-dsnr-bloc',
			dataType: 'json',
			data: {
				id: TEMPLATE_ID,
				layout: LAYOUT_ID
			}
		});
	}

};

Dsnr.log = function() {
	if (Dsnr.DEBUG && console)
		return console.log.apply(console, arguments);
};
Dsnr.error = function() {
	if (Dsnr.DEBUG && console)
		return console.error.apply(console, arguments);
};