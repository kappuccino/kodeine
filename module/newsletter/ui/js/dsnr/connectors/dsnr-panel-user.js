"use strict";

Dsnr.ConnectorUser = Dsnr.Connector.extend({

	name: 'ConnectorUser',

	onRender: function() {

	},

	onDataReceived: function(data) {

		/**
		 * Si besoin, parser les données ici
		 *
		 */

		this.connect(data);
	}

});