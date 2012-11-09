

function dashboardLoad(id, mod, page){

	$.ajax({
		'url': '/admin/'+mod+'/dashboard/'+page
	}).done(function(html){
		$('#'+id).html(html);
	});

}


