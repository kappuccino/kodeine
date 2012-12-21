$(function() {

	$("#items").sortable({
		'handle':	'.handle',
		'stop':		function() {
			serialMe();
		}
	});
	
});

function serialMe(){
	var db = [];
	$.each($('#items li'), function(m){
		db.push($(this).attr('id'))
	});

	var get = $.ajax({
		url: 'helper/type?order=' + db.join('-')
	});
}

function remove(){
	if(confirm("SUPPRIMER ?")){
		if(confirm("VRAIMENT VRAIMENT ? (gros pepins si c'est une erreur)")){
			$('#listing').submit();
		}
	}
}