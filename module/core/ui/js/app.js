requirejs.config({
//	'baseUrl': "",

	'paths': {
		'backbone':     '/admin/core/vendor/backbone/backbone-min',
		'underscore':   '/admin/core/vendor/underscore/underscore-min',
		'jquery':       '/admin/core/vendor/jquery/jquery-1.7.2'
	}

	/*'shim': {
		'backbone': {
			deps: ['underscore', 'jquery'], //These script dependencies should be loaded before loading backbone.js
			exports: 'Backbone'             //Once loaded, use the global 'Backbone' as the module value.
		},
		'underscore': {
			exports: '_'
		},
		'jquery': {
			exports: '$'
		}
	}*/

});

console.log('app.js');

// Load the main app module to start the app
//requirejs(["app/main"]);