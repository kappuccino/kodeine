$(function(){
	tab(languages[0]);
	
	$.each(languages, function(i, iso){
		$('#chapterName-'+iso).bind({
			'keyup' : function(){
				urlCheck(iso, true);
			},
			'keydown' : function(){
				urlCheck(iso, true);
			}
		});
		$('#chapterUrl-'+iso).bind({
			'keyup' : function(){
				urlCheck(iso, false);
			},
			'keydown' : function(){
				urlCheck(iso, false);
			}
		});
		$('#transform-'+iso).bind({
			'change' : function(){
				urlCheck(iso, true);
			}
		});
	});


	//urlCheck('', false);
	uls = $('#list ul');
	ser = [];
	
	console.log(uls);
	if(uls.length > 0){
	    uls.each(function(i, ul){
	    	
	    	var mySortables = $(ul).sortable({
	    		'handle': '.handle',
	    		'start': function(e, ui) {
	    			$(this).parent().children().each(function(i,m) {
	    				$(m).find('div.holder').addClass('view-same');
	    			});
	    		},
	    		'stop': function(e, ui) {
	    			$(this).parent().children().each(function(i,m) {
	    				$(m).find('div.holder').removeClass('view-same');
	    			});
	    		}
	    		
	    	});
	    	
	        $(ul).children().find('div.handle').bind({
	
	            'mouseenter' : function(){
	                $(this).parent().parent().parent().children().find('div.holder').addClass('view-same');
	            },
	
	            'mouseleave' : function(){
	                $(this).parent().parent().parent().children().find('div.holder').removeClass('view-same');
	            }
	        });
	    });
	}
});

function toggleCopy(iso){
	chk = ($('#copy-'+iso).prop('checked')) ? true : false;
	
	$('#chapterName-'+iso).prop('disabled', chk);
	$('#chapterUrl-'+iso).prop('disabled', chk);
	$('#chapterModule-'+iso).prop('disabled', chk);

	$('#transform-'+iso).prop('disabled', chk);
	$('#chapterMedia-'+iso).prop('disabled', chk);
	$('#chapterDescription-'+iso).prop('disabled', chk);
}

function tab(iso){
	$('.view').css('display', 'none');
	$('#view-'+iso).css('display', '');

	$('.is-tab').removeClass('is-selected');
	$('#tab-'+iso).addClass('is-selected');
}


function urlCheck(iso, d){
	
	
	if(!$('#transform-'+iso)[0].checked) return false;

	if(d){
		url = liveUrlTitle($('#chapterName-'+iso).val());
		$('#chapterUrl-'+iso).val(url);
	}else{
		url = $('#chapterUrl-'+iso).val();
	}

	id_chapter = $('#id_chapter').val();

	var get = $.ajax({
		url : 'helper/url?id_chapter='+id_chapter+'&language='+iso+'&url='+url,
		dataType : 'json'
	}).done(function(obj) {
		
		if(url != obj.url){
			$('#chapterUrl-'+iso).val(obj.url);
		}else{
			$('#alert-'+iso).css('display', 'none');
			$('#errorUrl-'+iso).val("");
		}
	});

}

function serialMe(){
    ser = serialAll(0);
    $('#serialized').val(JSON.stringify(ser));
       $('#order').submit();
}

function serialAll(mid){
    var tmp = [];
    li = $('#mid-'+mid).children();

    li.each(function(i, e){
        if($('#mid-'+e.id).length > 0){
            tmp[i] = {id_chapter:$(e).attr('id'), sub:serialAll($(e).attr('id'))};
        }else{
            tmp[i] = {id_chapter:$(e).attr('id'), sub:[]};
        }
    });
    
    return tmp;
}
