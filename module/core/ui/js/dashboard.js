

function dashboardLoad(id, mod, page){

	$.ajax({
		'url': '../'+mod+'/dashboard/'+page
	}).done(function(html){
		$('#'+id).html(html);
	});

}


