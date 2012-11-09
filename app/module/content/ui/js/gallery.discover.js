function discoverInit(){

	folders	= [];
	total	= 0;
	done	= 0;
	size	= $('discoverFolder').getCoordinates().width;

	var into	= $('inside').checked ? 'inside' : 'create';
	var root	= $('discoverFolder').value;

	if(root == ''){
		$('log').set('html', 'Le champ est vide');
		return false;
	}else{
		$('log').set('html', 'A la découvert de '+root+' : <b>patienter quelques instans</b> creation de l\'arborescence en cours...');
	}

	$('discoverFolder').setStyles({
		'background' : 'url(ressource/img/gallery-field-loader.gif) no-repeat -800px 0px'
	});

	new Request.JSON({
		url: 'ressource/lib/gallery.discover.php',
		onComplete:function(r){
			if(r.success){

				folders = r.data;
				total	= folders.length;
				step	= size / total;
	
				if(folders.length > 0){
					discover(0);
				}else{
					$('log').set('html', 'Rien a faire le dossier est vide');
				}

			}else{
				$('log').set('html', 'Erreur '+ r.error);
			}
		}
	}).post({
		'action' 	: 'folder',
		'id_type'	: id_type,
		'id_album'	: id_album,
		'into'		: into,
		'folder'	: root
	});
}

function discover(idx){
	if(idx >= total){
		$('log').set('html', 'Indexation terminée <a href="content.gallery.index.php?id_type='+id_type+'#'+id_album+'">Afficher</a>');
		$('discoverFolder').setStyle('background', '');
		return true;
	}

	done++;
	me = folders[idx];

	$('log').set('html', 'Indexation de '+done+'/'+total+' ('+ me.folder +')');
	
	$('discoverFolder').setStyles({
		'background' : 'url(ressource/img/gallery-field-loader.gif) no-repeat '+(-800 + (done * step))+'px 0px'
	});

	new Request.JSON({
		url: 'ressource/lib/gallery.discover.php',
		onComplete:function(r){
			$('log').set('html', $('log').get('html')+' : '+r.data+' element(s)');
			(function(){
				discover(idx+1);
			}).delay(500);
		}
	}).get({
		'action' 	: 'item',
		'folder'	: me.folder,
		'id_type'	: id_type,
		'id_album'	: me.id_album
	});
}