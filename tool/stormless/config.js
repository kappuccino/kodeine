#!/usr/local/bin/node

"use strict";

var _fs     = require('fs');
var _path   = require('path');

var config = {

	rootDir     : '/', // Défault
	lessFiles   : [],
	build       : {},

	init : function() {
		// Mettre a jour le root dir
		this.rootDir = _path.dirname(_path.dirname(_path.dirname(process.argv[1]))); // config kodeine
		// Lister tous les dossiers pour commencer
		this.startScan();
	},

	startScan : function() {

		var that = this;
		console.log("Scanning for *.less...");

		that.folderWalk(that.rootDir, function(err, files) {
			that.lessFiles = files;
			console.log('Found '+files.length+' less files');

			that.scanImports(that.lessFiles, function(err, build) {

				build = JSON.stringify(build);
				console.log("Build OK, time to relax.");
				that.writeCfg(build);
			});
		});

	},

	/* Scan récursif de tous les dossiers pour trouver les .less
	 *
	 * @param   dir     root du scan
	 * @param   done    func, callback
	 *
	 */
	folderWalk : function(dir, done) {
		var results = [];
		var that = this;

		_fs.readdir(dir, function(err, list) {

			if (err) return done(err);
			var pending = list.length;
			if (!pending) return done(null, results);

			list.forEach(function(file) {
				file = dir + '/' + file;
				_fs.stat(file, function(err, stat) {

					if (stat && stat.isDirectory()) {
						that.folderWalk(file, function(err, res) {
							results = results.concat(res);
							// Si rien en attente, callback
							if (!--pending) done(null, results);
						});
					} else {

						var ext = file.split('.').pop();
						if (ext == 'less') results.push(file);

						if (!--pending) done(null, results);
					}

				});
			});

		});

	},

	/* Trouve les @import dans les .less
	 *
	 * @param   files   array, liste des fichiers .less
	 * @param   done    func, callback
	 *
	 */
	scanImports : function(files, done) {

		var regImport   = /^@import(.*);$/gm;
		var regQuotes   = /"[^"]*"/;
		var pending     = files.length;
		var that        = this; // instance
		var build       = {};

		console.log("Scanning for @imports...");

		files.forEach(function(less) {

			_fs.readFile(less, 'utf8', function(err, data) {

				if (err) throw console.log('Read Error : '+err);
				if (!pending) return done(null, build);

				var imp = data.match(regImport);

				if (imp != null) {

					imp.forEach(function(str) {

						var noQuotes = str.match(regQuotes);
						if (noQuotes != null) {

							var cleanPath = that.fixPaths(less, noQuotes[0]);
							if (typeof build[cleanPath] === 'undefined') {
								build[cleanPath] = new Array();
							}
							build[cleanPath].push(less);
						}
					});
				}
				if (!--pending) done(null, build);

			});
		});

	},

	/* Normalise les chemins des @imports
	 *
	 * @param   parent  str, chemin absolu du fichier less testé
	 * @param   path    str, chemin du @import trouvé dans le fichier
	 * @return          str, chemin normalisé (absolu) pour le @import
	 *
	 */
	fixPaths : function(parent, path) {

		// Chemin de @parent nettoyé
		var parentDir = parent.split('/');
			parentDir.pop();
			parentDir = parentDir.join('/');

		// Clean les quotes de @path
		path = path.replace(/["]/g,'');

		// Chemin absolu
		if (path.charAt(0) == '/') {
			console.log("TODO :: support absolute path in fixPaths() ");
			throw new Error ("Absolute @import paths are not yet supported\nPlease fix this import \""+path+"\" in "+parent);

		} else
		// Chemin relatif
		if (path.charAt(0) == '.') {
			return _path.resolve(parentDir, path);
		} else {
		// Meme chemin que le parent
			return parentDir+'/'+path;
		}

	},

	/* Ecrit le build dans un fichier config.json
	 *
	 * @param   data  str, JSON stringifié du build
	 *
	 */
	writeCfg : function(data) {
		_fs.writeFile('stormless.json', data, function (err) {
			if (err) throw console.log("Error writing stormless.json : "+err);
			console.log('stormless.json written. Have fun !')
		});
	}

}

config.init();