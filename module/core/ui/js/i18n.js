'use strict';

var i18n = {
	models: {},
	views: {},
	collections: {},
	router: {}
};

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// MODEL

i18n.models.i18n            = Backbone.Model.extend({
});

i18n.models.files           = Backbone.Model.extend({
});

i18n.models.labels          = Backbone.Model.extend({
});

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// COLLECTION

i18n.collections.files      = Backbone.Collection.extend({

	model: i18n.models.files,

	url: 'helper/i18n-file'

});

i18n.collections.labels     = Backbone.Collection.extend({

	model: i18n.models.labels,

	url: 'helper/i18n-label'

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// VIEWS

i18n.views.module           = Backbone.View.extend({

	el: $('#modules'),

	initialize:function(){
	},

	events:{
		'click li' : 'select'
	},

	/////////////

	unselect: function(){
		this.$el.find('li.selected').removeClass('selected');
		i18n.views.myApp.module = '';
	},

	select: function(e){
		var target = $(e.target);
		var module = target.data('module');

		this.unselect();
		target.addClass('selected');

		i18n.views.myApp.module = module;
		i18n.views.myFiles.reload();
	}

});

i18n.views.files            = Backbone.View.extend({

	el: $('#files'),

	initialize:function(){
		this.listenTo(i18n.collections.myFiles, 'reset',  this.reset);
		this.ul = this.$el.find('ul');
	},

	events: {
	},

	/////////////

	empty: function(){
		this.ul.empty();
	},

	reset: function(){
		this.empty();
		i18n.collections.myFiles.each(this.itemAdd, this);
	},

	itemAdd: function(m){
		var view = new i18n.views.filesItem({model: m});
		this.ul.append(view.render().el);
	},

	reload: function(){
		i18n.collections.myFiles.fetch({data: {
			'module': i18n.views.myApp.module
		}});
	},

	unselect: function(){
		this.ul.find('li.selected').removeClass('selected');
	}

});

i18n.views.filesItem        = Backbone.View.extend({

	tagName: "li",

	events: {
		"click": "select"
	},

	/////////////

	select: function(e){
		var target  = $(e.target);

		i18n.views.myFiles.unselect();
		target.addClass('selected');

		i18n.views.myApp.file = this.model.get('file');
		i18n.views.myLabels.reload();
	},

	render: function() {
		this.$el.html(this.model.get('file'));
		return this;
	}

});

i18n.views.labels           = Backbone.View.extend({

	el: $('#labels'),

	initialize:function(){
		this.listenTo(i18n.collections.myLabels, 'reset',  this.reset);
		this.ul = this.$el.find('ul');
	},

	events: {
	},

	/////////////

	empty: function(){
		this.ul.empty();
	},

	reset: function(){
		this.empty();
		i18n.collections.myLabels.each(this.itemAdd, this);
	},

	itemAdd: function(m){
		var view = new i18n.views.labelsItem({model: m});
		this.ul.append(view.render().el);
	},

	reload: function(){
		i18n.collections.myLabels.fetch({data: {
			'module':   i18n.views.myApp.module,
			'file':     i18n.views.myApp.file
		}});
	},

	unselect: function(){
		this.ul.find('li.selected').removeClass('selected');
	}
});

i18n.views.labelsItem       = Backbone.View.extend({

	tagName: "li",

	events: {
		"click": "select"
	},

	/////////////

	select: function(e){
		var target = $(e.target);
		i18n.views.myLabels.unselect();
		target.addClass('selected');
	},

	render: function() {
		this.$el.html(this.model.get('label'));
		return this;
	}

});

i18n.views.form             = Backbone.View.extend({

	el: $('#form'),

	initialize:function(){
		this.inputModule    = this.$("#inputModule");
		this.inputKey       = this.$("#inputKey");
		this.inputValue     = this.$("#inputValue");
		this.buttonValidate = this.$("#buttonValidate");
	},

	events:{
	},

	/////////////

	fill: function(){
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// APP

i18n.views.app              = Backbone.View.extend({

	el: $('#i18n'),

	initialize: function() {
		this.module     = '';
		this.file       = '';
		this.label      = '';
		this.languages  = this.$el.data('languages').split(',');
	}

});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// ROUTEUR

i18n.router                 = Backbone.Router.extend({

	routes: {
	//	"invoice/:id":  "invoice"
	},

	initialize: function() {
		this.on('route:invoice', function(id) {
			i18n.views.myForm.loadFromRouteur(id);
		})
	}
});


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$(function(){

	// Collections
	i18n.collections.myFiles    = new i18n.collections.files;
	i18n.collections.myLabels   = new i18n.collections.labels;

	// Boot
	i18n.views.myApp            = new i18n.views.app;
	i18n.views.myLabels         = new i18n.views.labels;
	i18n.views.myFiles          = new i18n.views.files;
	i18n.views.myModule         = new i18n.views.module;


	// Views (ordre important)
	i18n.views.myForm     = new i18n.views.form;

	// Routeur
	i18n.myRouter = new i18n.router;
	Backbone.history.start();

});