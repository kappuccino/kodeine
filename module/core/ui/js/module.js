function install(a, mod, core){
	if($(a).hasClass('doing')) return;

	if(confirm("INSTALL ?")){
		$(a).html('En cours...').addClass('doing');	

		d({module:mod, install:true, core:core}, function(data){
			$(a).removeClass('doing').remove();
		});
	}
}

function enabled(state){

	mod  = this.attr('data-mod');
	core = this.attr('data-core');

	d({module:mod, enabled:true, core:core}, function(data){
	//	if(data.success) $().remove();
	});
}

function disabled(state){

	mod  = this.attr('data-mod');
	core = this.attr('data-core');

	d({module:mod, disabled:true, core:core}, function(data){
	//	if(data.success) $(a).remove();
	});
}

function patch(a, mod, again, core){
	if($(a).hasClass('doing')) return;

	if(confirm("Voulez-vous appliquer les patch sur ce module ?")){

		$(a).html('En cours...').addClass('doing');	

		d({module:mod, patch:true, again:again, core:core}, function(data){
			if(data.success) $(a).remove();
			$(a).removeClass('doing');	
		});
	}
}

function upgrade(a, mod){
	if($(a).hasClass('doing')) return;

	if(confirm("Voulez-vous mettre a jour ce module ?")){
		
		$(a).html('En cours...').addClass('doing');	

		d({module:mod, download:true, core:true}, function(data){
			if(data.success){
				$(a).html('Terminé').removeClass('doing');
				setTimeout(function(){
					$(a).html('Mise à jour');
				}, 1000);
			}
		});
	}
}

function checkRepo(){
	d({repository: true}, function(raw){
		list = raw.data;
		for(mod in list){
		
			console.log(mod);

			var curr = $('#this-'+mod).html();
			var repo = list[mod].version;
		
			$('#repo-'+mod).html(repo);
			
			if(parseInt(curr) <= parseInt(repo)){
				$('#upgd-'+mod).empty().append('<a class="btn btn-small" onclick="upgrade(this, \''+mod+'\')">Mise à jour</a>');
			}
		}
	});
}

// -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- 

function d(data, ca){
	var xhr = $.ajax({
		url: 'helper/module',
		dataType: 'json',
		data: data
	}).done(ca);
}

