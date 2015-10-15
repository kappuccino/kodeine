"use strict";

// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    CUSTOM PANELVIEW

Dsnr.DsnrView = Backbone.View.extend({ // custom view (we need a "nicer" close method)

	close: function() {
		this.undelegateEvents();
		this.stopListening();
		this.$el.empty();
	}

});


// + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - +
//    PANEL CONNECTOR CUSTOM VIEW

Dsnr.Connector = Dsnr.DsnrView.extend({

	el: $('#panel-connector'),
	template: Handlebars.compile($('#tpl_panel_connector').html()),

	events: {
		'click .apply' : 'apply'
	},

	initialize: function() {
		Dsnr.log('-- Init new <'+ this.name +'> connector');
	},

	render: function() {
		this.$el.html(this.template({}));

		if (this.onRender) this.onRender();
		$("select[name='selectconnector']").selectpicker({style: 'btn-hg btn-primary', menuStyle: 'dropdown-inverse'});

		return this;
	},

	getData: function(ajaxParams) {

		ajaxParams.dataType = 'json';
		ajaxParams.type = 'post';

		return $.ajax(ajaxParams);

	},

	apply: function(e) {

		// ID entré dans le champ "id kodeine"
		var id = this.$el.find('#connectorid').val();
		// url du connector séléctionné dans le dropdown "selectconnector"
		var url = this.$el.find('select[name="selectconnector"] option:selected').attr('data-url');

		/**
		 * Tous les connecteurs doivent renvoyer un json type
		 *
		 * resp {
		 *  media : "...",
		 *  title : "...",
		 *  description : "...",
		 *  data : {
		 *    "raw data"
		 *  }
		 * }
		 *
		 */
		this.getData({
			'url' : '/admin/newsletter/helper/'+url,
			data: {'id' : id}
		}).done(_.bind(this.onDataReceived, this))


	},

	connect: function(data) {

		console.log(data)

		//console.log(this.collection.models)
		console.log(this.model)

		for (var key in data) {
			if (this.model._nodeInstance.find('[data-field="'+ key +'"]')) {
				if (key == "media") {
					this.model._nodeInstance.find('[data-field="'+ key +'"]').attr('src', data[key]);
				} else {
					this.model._nodeInstance.find('[data-field="'+ key +'"]').html(data[key]);
				}
			}
		}

		this.model.set('contents', this.model._nodeInstance.html())


		/*for (var i = 0; i < this.collection.models.length; i++) {
			var model = this.collection.models[i];
			if (model._nodeInstance.attr('data-field')) {

				var attr = model._nodeInstance.attr('data-prop');
				var key = model._nodeInstance.attr('data-field');

				if (attr) { // Si une "data-prop" est renseignée, appliquer dessus (ex, "src")
					Dsnr.log('-- Applying field <'+ key +'> on <'+ attr +'>');
					model._nodeInstance.attr(attr, data[key]);
				} else
				if (key) { // default sur texte du bloc
					this.collection.get(model.cid)._nodeInstance.html(data[key]);
					this.collection.get(model.cid).set('contents', data[key]);
				}
			}

		}*/

		//this.model.updateHtml();

	}


});
