importing = false; 
_done     = [];
_error    = [];
_doublon  = [];

function debuter(){
    $('#message').css('display', 'block').html('Import en cours d\'initialisation...');
    importer(0, 10, false);
}

function importer(offset, length, loop){

    if(!importing || loop){
        importing   = true;
        url         = 'helper/import-addressbook.php?';             

        $('#formulaire input').each(function(i, me){
            if(me.name != 'myFile' && me.value != ''){

                if(me.type == 'checkbox' && me.checked){
                    url += '&'+me.name+'='+me.value;
                }else
                if(me.type == 'select'){
                    url += '&'+me.name+'='+ me.options[me.selectedIndex].value;
                }else
                if(me.type == 'hidden'){
                    url += '&'+me.name+'='+me.value;
                }
                
            }
        });

        $('#formulaire select').each(function(i, me){
            url += '&'+me.name+'='+ me.options[me.selectedIndex].value;
        });

        url += '&offset='+offset+'&length='+length;
        
        var get = $.ajax({
            'url' : url,
            'dataType' : 'json'
        });
	
		get.done(function(jsonObj) {

            /*_done.extend(jsonObj.done);
            _error.extend(jsonObj.error);
            _doublon.extend(jsonObj.doublon);*/
            
            _done = _done.concat(_done,jsonObj.done);
            _error = _error.concat(_error,jsonObj.error);
            _doublon = _doublon.concat(_doublon,jsonObj.doublon);
        
            $('#message').html(
                'Import en cours ne pas fermer cette page : '+offset+'/'+jsonObj.todo+
                ' (Import&eacute;:'+ _done.length +
                ' Erreur:'+ _error.length +
                ' Doublon:'+ _doublon.length + ')'
            );
        
            if(offset < jsonObj.todo){
            	//alert (_done);
                setTimeout(function(){
                    importer(offset+length, length, true);
                },1000);
            }else{
                terminer();
            }
			
		});

    }
}

function terminer(){
    output = 'Import terminé<br />Nombre de membre créé : '+ _done.length;
    
    if(_error.length > 0){
        output += '<br />Nombre de membre en erreur : '+ _error.length +' (<a href="javascript:$(\'#error\').css(\'display\',\'block\');">voir</a>)'
    }
    
    if(_doublon.length > 0){
        output += '<br />Nombre de membre en double : '+ _doublon.length +' (<a href="javascript:$(\'#doublon\').css(\'display\',\'block\');">voir</a>)'
    }

    $('#message').html(output);

    __doublon = 'Doublon : ';
    $.each(_doublon, function(i, me){
        __doublon += me.user+'; ';
    });
    $('#doublon').html(__doublon);

    __error = 'En erreur : ';
    $.each(_error, function(i, me){
        __error += me.user+'; ';
    });
    $('#error').html(__error);

}