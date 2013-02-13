function applyRemove(){
	if(confirm("SUPPRIMER ?")){
		$('#listing').submit();
	}
}

function sauver(){
	ordre = mySortables.serialize();
	ordre = ordre[0].join(',');
	document.location='config.field.php?apply='+ordre+'&move='+$('move').value;
}