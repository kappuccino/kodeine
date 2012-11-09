document.addEvent('domread', function(){
});

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Independent job script
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
def = {
	'internalLink' : {
		list: [],
		current: 0,
		xqr : new Request.JSON({
			'url' : 'ressource/lib/safe.tool.php',
			'onSuccess': function(d){
			
				var section = $('section-internalLink');
				var listing	= section.getElement('.listing');

				if(d.isBroken){
					listing.setStyle('display', 'block');

					new Element('div', {
						'html' : '<a href="content.data.php?id_content='+d.id_content+'">'+d.contentName+'</a>'
					}).inject(listing, 'bottom');
				}

				(function(){
					job('internalLink', def.internalLink.current+1);
				}).delay(100);
			}
		})
	},
	'contentCache' : {
		list: [],
		current: 0,
		xqr : new Request.JSON({
			'url' : 'ressource/lib/safe.tool.php',
			'onSuccess': function(d){
				(function(){
					job('contentCache', def.contentCache.current+1);
				}).delay(100);
			}
		})
	},
	'mediaCache' : {
		list: [],
		current: 0,
		xqr : new Request.JSON({
			'url' : 'ressource/lib/safe.tool.php',
			'onSuccess': function(d){
				(function(){
					job('mediaCache', def.mediaCache.current+1);
				}).delay(100);
			}
		})
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function lancer(what){

	var section 	= $('section-'+what);
	var verbose		= section.getElement('.verbose');
	var progress	= section.getElement('.progress');
	var action		= section.getElement('.action');

	verbose.setStyle('display', 	'block');
	action.setStyle('display', 		'none');

	new Request.JSON({
		'url' : 'ressource/lib/safe.tool.php',
		'onSuccess': function(d){
			if(d.list.length > 0){
				progress.setStyle('display', 	'block');
				verbose.setStyle('display', 	'block').set('html', d.list.length+' element(s)');

				eval('def.'+what+'.list=d.list');
				job(what, 0);
			}else{
				verbose.set('html', 'Aucune donnée à consolider');
				action.setStyle('display',   'block');
			}
		}
	}).get({
		'todo' : 'list',
		'what' : what
	});
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function progressBar(what, p){

	var section 	= $('section-'+what);
	var bar			= section.getElement('.progress .bar');

	bar.setStyle('width', p+'%');
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function job(what, n){
	
	var myDef	= eval('def.'+what);
	var list	= myDef.list;
	var xqr 	= myDef.xqr;

	var section 	= $('section-'+what);
	var verbose		= section.getElement('.verbose');
	var progress	= section.getElement('.progress');
	var action		= section.getElement('.action');


	if(n > list.length){
		verbose.set('html', "Routine terminée : "+eval('def.'+what+'.list.length') + " élement(s) consolidé(s)");
		progress.setStyle('display', 'none');
		action.setStyle('display', 'block');

		return false;
	}

	// On positionne le curseur sur le N courrant
	eval('def.'+what+'.current='+n);

	// On envoit la sauce
	xqr.get({
		'what'	  : what,
		'element' : list[n] // ID ou Folder
	});

	// On met a jour la barre de progression
 	var percent = (n > 0) ? Math.round((n / list.length) * 100) : 0;
 	progressBar(what, percent);
 	
 	// On met a jour le texte
	verbose.set('html', "En cours de traitement : "+ eval('def.'+what+'.current') +' sur '+ eval('def.'+what+'.list.length'));
 
}














