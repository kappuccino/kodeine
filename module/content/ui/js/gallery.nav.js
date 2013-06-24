
$(function(){

	var allow = true;

	$('form input, form textarea').
		on('focus', function(){
			allow = false;
		}).
		on('blur', function(){
			allow = true;
		});

	$(document).on('keydown', function(e){
		if(!allow) return;
		var link = '';

		if(e.keyCode == 38){ // up
			var link = $('#goToAlbum').attr('href');
		}else
		if(e.keyCode == 37){ // left
			var link = $('#goToLeft').attr('href');
		}else
		if(e.keyCode == 39){ // right
			var link = $('#goToRight').attr('href');
		}

		if(link != '') document.location = link;
	});

});

