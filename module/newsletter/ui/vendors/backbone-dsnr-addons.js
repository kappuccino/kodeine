/*"use strict";

var Dsnr = Dsnr || {};

Dsnr.View = Backbone.View.extend({

	constructor: function(){

		// bind avec le contexte; voir http://underscorejs.org/#bindAll
		_.bindAll(this, "render");

		var args = Array.prototype.slice.apply(arguments);
		Backbone.View.prototype.constructor.apply(this, args);

		Marionette.MonitorDOMRefresh(this);
		this.listenTo(this, "show", this.onShowCalled, this);
	}

});*/


