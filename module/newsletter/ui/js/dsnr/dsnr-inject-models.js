"use strict";

// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    MODELS

//
Dsnr.NodeModel = Backbone.Model.extend({

	_nodeInstance: null,
	parent: null,
	view: null,
	isLocked: false,

	_connector: null,

	textDefaults : { // default props for text element

	},

	imgDefaults : { // defaults for img element

	},

	initialize: function(opt) {

		// construct
		this._nodeInstance = opt.node;

		if (opt.startAt) return; // first initialization coming from collection

		this.registerAttributes(); // register * attr

		this.registerData(); // register data-attr as attr

		this.setDefaults(); // set default values for nodeType
		this.getConnector(opt.connectors); // Register connector if template provided one

		// TODO moar saves/checks
	},

	registerAttributes: function() {
		for (var i in this._nodeInstance.get(0).attributes) {
			var attr = this._nodeInstance.get(0).attributes[i];
			this.set(attr.name+'Attr', attr.value);
		}
		this.set('tagName', this._nodeInstance.get(0).tagName);

	},

	setConnector: function(connector) { // register a (unique) connector for this model
		this.set('connector', connector);

		$.ajax({ // save in db
			url: 'helper/ajax-dsnr-content',
			type: 'POST',
			data: {
				save: true,
				bloc: Dsnr.BLOC,
				nodekey: this.get('nodekey'),
				connector: connector
			}
		});

	},

	getConnector: function(connectors) { // gets connector data (if any) from construct
		for(var i in connectors) {
			if (i == this.get('nodekey')) {
				this.set('connector', connectors[i]);
			}
		}
	},

	updateHtml: function() {
		this.set('contents', this._nodeInstance.html())
	},

	registerData: function() { // registers html & data-attributes in the model
		this.set(this._nodeInstance.data());
		if (this.get('type') == 'text') this.set('contents', this._nodeInstance.html())

	},

	connectorEdit: function(connector) {

		var models = [];
		// Adds every NodeModel found in this connector's wrapper in the temp ConnectorCollection

		this._nodeInstance.parents('[data-connectable="true"]').find('*[data-cid]').each(function() {
			models.push(Dsnr.MyNodeCollection.get($(this).attr('data-cid')))
		})

		Dsnr.MyConnectorCollection = new Dsnr.ConnectorCollection(models)

		Dsnr.log('-- Rendering new <'+ connector +'> connector');

		Dsnr.PanelView = new Dsnr.connectors[connector]({
			collection: Dsnr.MyConnectorCollection,
			model: this
		}).render();
	},

	uiEdit: function() { // set up appropriate uiEditView

		if (Dsnr.PanelView) Dsnr.PanelView.close();

		switch(this.get('type')) {
			case 'img':
				Dsnr.PanelView = new Dsnr.PanelImg({
					model: this
				}).render();
				break;

			case 'text':
				Dsnr.PanelView = new Dsnr.PanelText({
					model: this
				}).render();
				break;
		}

	},

	uiContentLink: function() {
		console.log('content link')
		Dsnr.PanelView = new Dsnr.PanelLink({
			model: this
		}).render();
	},

	setDefaults: function() {

		switch(this.get('type')) {
			case 'img': this._setImgDefaults();
				break;
			case 'text': this._setTextDefaults();
				break;
		}

	},

	saveConnector: function(prop) { // saves connector properties

	},

	_setImgDefaults: function() {

	},

	// sets helper values (mostly bools useful for templating)
	_setTextDefaults: function() {
		if (this.get('editor') != 'input') this.set('textarea', true);
	}

});


// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    COLLECTIONS

// Scan dom nodes to build a NodeModel collection
Dsnr.NodeCollection = Backbone.Collection.extend({

	model : Dsnr.NodeModel,

	// Dom lookup starting node
	initialize: function(opt) {

		this.on('add', function() {})
		this.on('remove', function() {})
	},

	clearHovers: function() { // clear all views highlights
		for(var i = 0; i < Dsnr.Views.length; i++) {
			Dsnr.Views[i].clearHover()
		}
	}


});

// Dummy templates collection
Dsnr.ConnectorCollection = Backbone.Collection.extend({

	model : Dsnr.NodeModel,

	initialize: function(opt) {
		// construct
		console.log('Init CONNECTOR collection', this);
	}

});


