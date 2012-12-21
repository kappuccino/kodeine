$(function(){

	login_	= $('input[name="login"]');
	pass_	= $('input[name="password"]');
	lost_	= $('input[name="lost"]');
	regen_	= $('input[name="regen"]');

	if($.browser.msie) {
		$('input[placeholder]').each(function(){  
		    var input = $(this);        

		    input.val(
		    	input.attr('placeholder')
		    ).focus(function(){
		         if (input.val() == input.attr('placeholder')) input.val('');
		    }).blur(function(){
		        if(input.val() == '' || input.val() == input.attr('placeholder')) input.val(input.attr('placeholder'));
		    });
		});
		
		// Desactiver au submit
		$('form').submit(function() {
		        $(this).children('input[placeholder]').each(function(placeholder) {
				if($(this).attr('placeholder') == $(this).val() ) $(this).val('');
			});
		});
	}

	lostView	= false;
	submitted	= false;

	$('input.field').bind('keydown', function(e){
		if(e.keyCode == 13 && !submitted){
			$('form').submit();
			submitted = true;
		}
	});
	
	if($('.regen').length > 0){
		$('.form').stop().animate({
			'margin-top': '-126px'
		}, 500);
	}
});

/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- */
function toggle(){

	if(lostView){
		var to = '0px';
		lostView = false
	}else{
		var to = '-42px';
		lostView = true;
		lost_.val(login_.val());
	}

	$('.form').stop().animate({
		'margin-top': to
	}, 500);
}

/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- */
function lostPwd(){

	if(lost_.val() == '') return false;

	$('.form').stop().animate({
		'margin-top': '-84px'
	}, 500);

	$.ajax({
		url: 'helper/lost',
		type: "POST",
		dataType: "json",
		data: {
			'email': lost_.val()
		}
	}).done(function() { 
		setTimeout(function(){
			/*login_.val(lost_.val());
			
			$('.form').stop().animate({
				'margin-top': '0'
			}, 500);*/
		}, 1000);
	});

}

/* -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 
-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- */
function rgx(token){

	$.ajax({
		url: 'helper/lost',
		type: "POST",
		dataType: "json",
		data: {
			'token': token,
			'regen': regen_.val()
		}
	}).done(function(data){ 
		if(data.success){
			pass_.val(regen_.val());
			
			$('.form').stop().animate({
				'margin-top': '0px'
			}, 500, function(){
				setTimeout(function(){
					$('form').submit();
				}, 500);
			});
		}
	});

}
