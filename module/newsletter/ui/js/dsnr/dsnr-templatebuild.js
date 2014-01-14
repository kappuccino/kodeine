
// Assembler un template a partir de blocs et d'un layout
$(function() {

	// reçoit les elements html permettant de selectionner le spot où
	// intégrer le bloc sur lequel on a cliqué
	var $selector = "";

	// iframe ready
	window.frameReady = function() {
		$('iframe#dsnrframe').contents().find('head').append('<link rel="stylesheet" id="dsnr-style" type="text/css" href="/admin/newsletter/ui/css/dsnr-drag.css" />');
		$selector = buildInjectors();
	}


	var src = $('iframe#dsnrframe').attr('href')
	$('iframe#dsnrframe').attr('src', src);

	// appliquer le preset dans la iframe
	if (LAYOUT_PRESET) { // apply preset if found
		for (var k in LAYOUT_PRESET) {
			for (var bloc = 0; bloc < LAYOUT_PRESET[k].length; bloc++) {
				$('ul.left li.itembloc[data-bloc="'+ LAYOUT_PRESET[k][bloc] +'"]')
					.clone().appendTo($('iframe#dsnrframe').contents().find('td.dsnr-injector[data-injector="'+ k +'"]'))
			}
		}
	}

	// lister les emplacements dispos dans le template
	// var injectors = $('.dsnr-injector');

	$('ul.left li.itembloc').on('click', function(e) {

		var li = $(this);
		if ($(this).parents('.dsnr-injector').length > 0) {
			return false;
		}

		$selector.appendTo('body').css({
			position : 'absolute',
			top : e.pageY,
			left : e.pageX
		});

		$(document).off('click', ".injector-select .item");
		$(document).one('click', ".injector-select .item", function() {
			$('.injector-select').remove();
			$('iframe#dsnrframe').contents().find('.dsnr-injector')
				.eq($(this).attr('data-index'))
				.append(li.clone());
		})

	});

	// SAUVER LE TEMPLATE
	$('a.savetemplate').on('click', function() {

		if (confirm('Désirez-vous enregistrer cet agencement en tant que réglages par defaut ?')) {
			var data = {};
			// iterer sur tous les injectors autorisés du layout et extraire le pattern des des blocs
			$('iframe#dsnrframe').contents().find('li.itembloc').each(function() {
				if ($(this).parent().hasClass('dsnr-injector')) {

					if (!data[$(this).parent().attr('data-injector')]) {
						data[$(this).parent().attr('data-injector')] = [];
					}
					data[$(this).parent().attr('data-injector')].push($(this).attr('data-bloc'));
				}
			});

			$.ajax({
				url: 'helper/ajax-dsnr-layoutpreset',
				type: 'POST',
				data: {
					preset: data,
					id: LAYOUT_ID
				}
			})
		}

		var defers = [];

		var itemblocs = $('iframe#dsnrframe').contents().find('.dsnr-injector');
		itemblocs.each(function(bloc) {

			$(this).find('li.itembloc').each(function() {
				// Retrieve every nl bloc
				defers.push(
					$.ajax({
						url: 'helper/ajax-dsnr-bloc',
						dataType: 'json',
						data: {id: $(this).attr('data-bloc')}
					})
				);

			});
		});

		$.when.apply($, defers)
			// $.when ne recoit pas un array de deferred mais une serie d'args; faker avec .apply()
			.then(function() {

				if (!arguments[0]) return false;
				if (!arguments[0].length) arguments = [arguments]; // si un seul defered répond

				// item, renvoie les resolutions individuellement, y acceder le tableau arguments
				for (var i = 0; i < arguments.length; i++) {
					var bloc_id = arguments[i][0]['id_bloc'];
					var bloc = $('iframe#dsnrframe').contents().find('li.itembloc[data-bloc="'+bloc_id+'"]');
					bloc.replaceWith(arguments[i][0]['contents']);
				}

				saveCleanTemplate().done(function() {console.log('ALL DONE :3 ')});
			});
	});


});

function saveCleanTemplate() {

	$('iframe#dsnrframe').contents().find('.dsnr-injector-text').remove();
	$('iframe#dsnrframe').contents().find('#dsnr-style').remove();

	$('iframe#dsnrframe').contents().find('*')
		.removeClass('dsnr-injector')

	return $.ajax({
		url: '',
		type: 'POST',
		data: {
			action: true,
			templateName: $('#templatename').val(),
			templateData: '<!DOCTYPE HTML>'+$('iframe#dsnrframe').contents().find('html')[0].outerHTML
		}
	});

}

function buildInjectors() {

	var $build = $('<ul class="injector-select" />');

	$('iframe#dsnrframe').contents().find('.dsnr-injector').each(function(k,i) {
		var index = $('iframe#dsnrframe').contents().find('.dsnr-injector').index($(this));
		var li = $('<li class="item" data-index="'+ index +'">'+ $(this).attr('data-injector') +'</li>');

		$build.append(li);
	});

	return $build;
}