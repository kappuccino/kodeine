var folders = [], percent = 0, total = 0, done = 0, log = $('#log'), id_type, id_album;

function discoverInit(){

	var root = $('#discoverFolder').val();
	var into = $('input[name="sel"]:checked').val();

	if(root == ''){
		log.html('Le champ est vide');
		return false;
	}else{
		log.html('A la découverte de '+root+'...<br /><b>patienter quelques instants</b><br />Création de l\'arborescence en cours...');
	}

	var xhr = $.ajax({
		url:       'helper/gallery-discover',
		dataType: 'json',
		data: {
			'action':   'folder',
			'id_type':   id_type,
			'id_album':  id_album,
			'into':      into,
			'folder':    root
		}
	});

	xhr.done(function(r){
		if(r.success){

			folders = r.data;
			total	= folders.length;

			if(total > 0){
				discover(0);
			}else{
				log.html('Rien a faire le dossier est vide');
			}

		}else{
			log.html('Erreur '+ r.error);
		}
	});

}


function discover(idx){

	if(idx >= total){
		log.html('Indexation terminée <a href="gallery?id_type='+id_type+'#album/'+id_album+'">Afficher</a>');
		return true;
	}

	var me = folders[idx];
	done++;

	$('#progress .bar').css('width',  Math.round((done/total)*100) +'%');
	log.html('Indexation de '+done+'/'+total+' ('+ me.folder +')');

	var xhr = $.ajax({
		url:       'helper/gallery-discover',
		dataType: 'json',
		data: {
			'action' 	: 'item',
			'id_type'	: id_type,
			'id_album'	: me.id_album,
			'folder'	: me.folder
		}
	});

	xhr.done(function(r){
		log.html(
			log.html()+' : '+r.data+' élement(s)'
		);

		setTimeout(function(){
			discover(idx+1);
		}, 500);
	});

}