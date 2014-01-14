"use strict";

var Dsnr = Dsnr || {};

// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    UI VIEWS

Dsnr.ToolbarView = Backbone.View.extend({

	template: _.template($('#tpl_toolbar').html()),

	initialize: function(opt) {
		this.eventSource = opt.source;
	},

	render: function() {

		console.log('TOOLBAR VIEW, AVAILABLE CONNECTORS : ', Dsnr.connectors)

		this.$el.html(this.template({}))

		for (var conn in Dsnr.connectors) {
			var add = $('<a class="btn btn-primary" data-toggle="'+ conn +'"><i class="fui-user"></i></a>');
			this.$el.children().append(add);
		}

		// Clean left panel when targeting a new node
		if (Dsnr.PanelView) Dsnr.PanelView.close();

		this.$el.css({ // position ui toolbar
			top: this.eventSource.offset().top - (this.$el.outerHeight(true) + 15),
			left: this.eventSource.offset().left
		});

		this.bindUiEvents();

		return this;
	},

	bindUiEvents: function() {

		this.$el.find('a').on('click', _.bind(function(e) {
			var target = $(e.currentTarget).attr('data-toggle');

			if (target == 'edit') {
				this.model.uiEdit();
			} else {
				this.model.connectorEdit(target)
			}

		}, this ));

	}

});

// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    NODE VIEWS

Dsnr.NodeView = Backbone.View.extend({

	initialize: function(opt) {
		// constructr

		this._root = opt.root;

		console.log('testing', this.$el, '(parents ? ', this.$el.parents('[data-connectable="true"]').length)

		if (!this.$el.parents('[data-connectable="true"]').length) {

			console.log('Has NO parents', this.$el)

			this.bindHoverEvents();
			this.highlightNode();
		}

		// define this view's model in the html
		this.$el.attr('data-cid', this.model.cid);

		this.model.on('change', _.bind(function(model) { // catch change event and apply changes to template
			Dsnr.log("-- <"+model.get('nodename')+"> updated : ", model.changed);

			this.applyModelChanges(model.changed);
		}, this ));
	},

	bindHoverEvents: function() {

		/*if (this.$el.parents('[data-connectable="true"]').length) {
			console.log('forcing over on parent')
			this.$el.parents('[data-connectable="true"]').toggle('mouseover');
			return false;
		}*/

		this.$el.on('mouseover', _.bind(function(e) { // activate hover outline

			// prevents wrong target due to bubbling & injection in img tag
			if (e.currentTarget === this.$el.get(0) && this.$el.get(0).tagName != 'img') {

				Dsnr.MyNodeCollection.clearHovers(); // clean other hovers
				this.$el.on('click', _.bind(this.onClick, this)); // bind click event
				$(e.currentTarget).addClass('dsnr-hover') // add MY hover
			} else {

				return false; // exit event
			}

		}, this ));

	},

	onClick: function() {

		this.toolbar = new Dsnr.ToolbarView({
			el: $('#dsnr-toolbar-wrap', this._root),
			model: this.model,
			source: this.$el
		}).render();
	},

	clearHover: function() {
		this.$el.removeClass('dsnr-hover');
		this.$el.off('click');
	},

	highlightNode: function() {
		this.$el.addClass('dsnr-editable');
	},

	applyModelChanges: function(changes) {

		// loop to easily access {changes}' key
		// No matter how many changes in the model, backbone will fire
		// as many events as needed, no need to handle everything in
		// a single loop (cf; return closure)
		console.log(this.model);

		for (var i in changes) {

			// Apply contents change (text or html)
			if (i == 'contents') return (function() {
				this.$el.html(this.model.get('contents'));
			}).apply(this)

			// Apply attr changes
			if (i.indexOf('Attr') > -1) return (function(attr) {
				var property = attr.substring(0, attr.indexOf('Attr')); // get attr name
				this.$el.attr(property, this.model.changed[attr]); // apply changes to $el
			}).apply(this, [i])

		}
	}

});
