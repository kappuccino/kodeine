function setup(iso, a){

	div = 	$('<div class="item item-'+iso+'"/>').appendTo('#items');

	$('<div class="top clearfix">' + 
			'<span class="left">'+ languages[iso] +'</span>'+
			'<a class="btn btn-mini right toggle" onclick="on("'+ iso +'")\">Activer/D&eacute;sactiver l\'&eacute;diteur de texte</a>'+
			'<a class="btn btn-mini right" onclick=\"kill(\''+ iso +'\');\" style=\"margin-right:10px;\">Supprimer cette version</a>'+
		'</div>'+
				
		'<div class="textarea">'+
			'<textarea id="'+ iso +'" name="data['+ iso +']"></textarea>'+
		'</div>'
	).appendTo(div);

	$(a).remove();
	$('#kill-'+iso).remove();
}

function kill(iso){

	/*var restant = $('#dataItem .item').length;

	if(restant-1 == 0){
		if(!confirm("Si vous supprimer cette langue, le label sera supprime.")){
			return false;
		}
	}*/

	t = $('#dataItem').find('.item-'+iso);
	if(t.length > 0) t.remove();

	$('<a onclick="setup(\''+iso+'\',this)" class="btn btn-mini">Ajouter '+eval('languages.'+iso)+'</a>').appendTo('.add');
	$('<input type="hidden" value="'+ iso +'" name="kill[]" id="kill-'+ iso +'" />').appendTo('.add');
}

function searchInTheme(label){

	$('.searchResult').empty();

	var get = $.ajax({ 
		'url': 		'helper/find',
		'dataType': 'json',
		'data': {
			'label': label
		}
	}).done(function(r) {
		if(r.length == 0){
			$('<li>Aucun fichier trouv&eacute;</li>').appendTo('.searchResult');
			return true;
		}
	
		for(i=0; i<r.length; i++){
			$('<li>'+r[i]+'</li>').appendTo('.searchResult');
		}
	});
}

function applyRemove(f){
	if(confirm("Voulez vous effectuer la suppression ?")){
		$('#form-'+f).submit();
	}
}

function addLabel(){
	var label = prompt("Quel nom donner a ce label ?");
	if(label != ''){
		$('#addLabel').val(label);
		$('#addForm').submit();
	}
	
	return true;
}

function on(iso){
	if (tinyMCE.getInstanceById(iso)){
		tinyMCE.execCommand('mceRemoveControl', false, iso);
	}else{
		tinyMCE.init({
			mode		: "exact",
			elements	: iso,
			theme		: "advanced",
			plugins		: "safari,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,"+
						  "inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,"+
						  "fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",
	        remove_script_host		: true,
	        convert_urls 			: false,
			theme_advanced_toolbar_location		: "top",
			theme_advanced_toolbar_align		: "left",
			theme_advanced_statusbar_location	: "bottom",
			theme_advanced_resizing				: false,
		});
	}
}
