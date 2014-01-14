<?php
if(sizeof($_POST['remove']) > 0){
	foreach($_POST['remove'] as $e){
		$app->apiLoad('newsletter')->newsletterTemplateRemove($e);
	}
	header("Location: template");
}else
	if($_POST['action']){
		$do = true;

		if($_SESSION['upFile'] == NULL) $_SESSION['upFile'] = uniqid();

		$up = USER.'/'.$_SESSION['upFile'];
		if(file_exists($up)) unlink($up);
		umask(0);

		# Si le fichier est bien deplace dans le bon dossier
		if(move_uploaded_file($_FILES['upFile']['tmp_name'], $up)){
			$_POST['templateData'] = file_get_contents($up);
			unlink($up);
		}

		$templateStyle = json_encode($_POST['style']);

		$def['k_newslettertemplate'] = array(
			'templateName' 	=> array('value' => $_POST['templateName'], 'check' => '.'),
			'templateData' 	=> array('value' => $_POST['templateData'], 'check' => '.'),
			'templateStyle'	=> array('value' => $templateStyle)
		);
		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->apiLoad('newsletter')->newsletterTemplateSet($_POST['id_newslettertemplate'], $def);
			$message = ($result)
				? 'OK: Enregistrement dans la base'
				: 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;
		}else{
			$message = 'KO: Validation failed';
		}
	}

if($_REQUEST['id'] != NULL){
	$mylayout = $app->dbOne('SELECT * FROM `@nlwrap` WHERE id_wrap='.$_GET['id']);
}

#$type = $app->apiLoad('newsletter')->newsletterTemplateGet(array('debug' => false));
$layouts = $app->dbMulti('SELECT * FROM `@nlwrap`');

###### DSNR
$blocs = $app->dbMulti('SELECT * FROM `@nlblocs` LIMIT 0, 100');

?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/newsletter.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/dsnr-ui.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/bootstrap-alert.css" />
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
	?></header>

<div id="app"><div class="wrapper">

	<div>

		<div class="alert alert-info">
			Sélectionnez un arrière-plan de newsletter sur lequel ajouter vos blocs. Vous pouvez ajouter autant de blocs que vous le souhaitez dans chaque niveau. Les niveaux restés vides n'ajouteront pas d'espaces blancs dans votre newsletter.
			<br/>Pour ajouter un bloc dans l'arrière plan, cliquez dessus. Puis, dans le menu déroulant, sélectionnez le niveau auquel vous souhaitez l'accrocher.
		</div>

		<form action="template" method="post" id="listing">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing table">
				<thead>
				<tr>
					<th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
					<th>Nom</th>
					<th width="100">
						<!--<input type="text" class="field roundTextInput roundSearchInput" onkeyup="recherche($(this))" onkeydown="recherche($(this))" size="10" style="float:right;" />-->
					</th>
				</tr>
				</thead>
				<tbody><?php
				if(sizeof($layouts) > 0){
					foreach($layouts as $e){ ?>
						<tr>
							<td><input type="checkbox" name="remove[]" value="<?php echo $e['id_newslettertemplate'] ?>" class="cb" /></td>
							<!--<td colspan="2"><a href="template?id_newslettertemplate=<?php /*echo $e['id_newslettertemplate'] */?>" class="sniff"><?php /*echo $e['templateName'] */?></a></td>-->
							<td colspan="2">
								<a href="?id=<?php echo $e['id_wrap']; ?>"><?php echo $e['name'] ?></a>
								<a target="_blank" href="preview?id_newslettertemplate=<?php echo $e['id_wrap'] ?>" class="btn btn-mini sniff right">Voir</a>
								<a href="?id=<?php echo $e['id_wrap']; ?>" class="sniff btn btn-mini right" style="margin-right: 10px;">Utiliser</a>
							</td>
						</tr>
					<?php }
				}else{ ?>
					<tr>
						<td colspan="3" style="text-align:center; padding:30px 0px 30px 0px; font-weight:bold;">Aucun gabarit existant.</td>
					</tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<?php if(sizeof($type) > 0){ ?>
					<tr>
						<td width="30" height="25"><input type="checkbox" onchange="$$('.cb').set('checked', this.checked);" /></td>
						<td colspan="3"><a href="#" onClick="applyRemove();" class="btn btn-mini">Supprimer la selection</a></td>
					</tr>
				<?php }else{ ?>
					<tr>
						<td colspan="3">&nbsp;</td>
					</tr>
				<?php } ?>
				</tfoot>
			</table>
		</form>

	</div>
</div></div>

<div id="canvas">

	<div class="templatename"><input id="templatename" name="templatename" placeholder="Nom du gabarit" type="text" /><a class="btn btn-mini savetemplate" href="#">Assembler le gabarit</a></div>

	<ul class="left">
		<li>Blocs disponibles</li>
		<?php
		foreach($blocs as $b) {
			echo '<li class="itembloc draggable" data-bloc="' .$b['id_bloc']. '">'.$b['blocName'].' <a class="delete" onclick="var me=this.parentNode; me.parentNode.removeChild(me)">SUPPRIMER</a> </li>';
		}
		?>
	</ul>

	<iframe id="dsnrframe" class="right" style="height:600px" frameborder="0" scrolling="auto" href="<?php echo $mylayout['path'] ?>"></iframe>
	<!--<ul class="right" id="dropzone" style="min-height:200px;"></ul>-->
</div>



<?php include(COREINC.'/end.php'); ?>

<script type="text/javascript" src="ui/vendors/jquery-ui-1.10.3/jquery-1.9.1.js"></script>
<script type="text/javascript" src="ui/js/dsnr/dsnr-templatebuild.js"></script>

	<script type="text/javascript">

		var LAYOUT_ID = "<?php echo $_GET['id']; ?>";
		var LAYOUT_PRESET = <?php echo (!empty($mylayout['preset'])) ? $mylayout['preset']  : '{}' ?>;

		//var lastDragIndex; // index du dernier element drag
		//var lastClonedItem;

		/*$(function() {

			window.frameReady = function() {
				$('iframe#dsnrframe').contents().find('head').append('<link rel="stylesheet" id="dsnr-style" type="text/css" href="/admin/newsletter/ui/css/dsnr-drag.css" />');
				setDraggable($('li.draggable')); // activer le drag sur les li de .left
				setDroppable();
			}

			var src = $('iframe#dsnrframe').attr('href')
			$('iframe#dsnrframe').attr('src', src);

			// ajouter css dsnr iframe

			// appliquer le preset dans la iframe
			if (LAYOUT_PRESET) { // apply preset if found
				for (var k in LAYOUT_PRESET) {

					for (var bloc = 0; bloc < LAYOUT_PRESET[k].length; bloc++) {
						$('ul.left li.itembloc[data-bloc="'+ LAYOUT_PRESET[k][bloc] +'"]')
							.clone().appendTo($('iframe#dsnrframe').contents().find('td.dsnr-injector[data-injector="'+ k +'"]'))
					}

				}
			}

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

			function setDraggable($el) {
				$el.draggable({
					iframeFix: true,
					revert: true,
					start: function(e, ui) {
						lastDragIndex = ui.helper.index(); // sauver l'index avant le drag
					},
					drag: function(e, ui) {
						//console.log('DRAG', e)
						//console.log('ITEM ', ui)
					},
					stop: function(e, ui) {
					}
				});
			}

			function setDroppable() {
				$('iframe#dsnrframe').contents().find('html').droppable({
					iframeFix: true,
					drop: function(e, ui) {

						var position = ui.offset;
						var dropped = false;
						//var drop = $('iframe#dsnrframe').contents()[0].elementFromPoint(350, (position.top+50));
						var drop = $('iframe#dsnrframe').contents()[0].elementFromPoint(350, ui.position.top)

						// Handle drop on item & drop on children (.dsnr-injector-text)
						if ($(drop).hasClass('dsnr-injector')) {
							dropped = true;
							ui.draggable.draggable( "disable" ).appendTo($(drop)).removeAttr('style');
						} // drop on children
						if ($(drop).parent().hasClass('dsnr-injector')) {
							dropped = true;
							ui.draggable.draggable( "disable" ).appendTo($(drop).parent()).removeAttr('style');
						}


						lastClonedItem = ui.draggable.attr('data-bloc');
						if (dropped && lastDragIndex == $('#canvas ul.left li').length) {
							// Clone el, cas particulier si dernier de la liste
							$('#canvas ul.left li:eq('+ (lastDragIndex - 1) +')').after(
								ui.draggable.clone().css('position', 'relative').removeClass('draggable ui-draggable ui-draggable-dragging')
							);
							setDraggable($('ul.left .itembloc[data-bloc="'+ui.draggable.attr('data-bloc')+'"]'))
						} else if (dropped) {
							$('#canvas ul.left li:eq('+ lastDragIndex +')').before(
								ui.draggable.clone().css('position', 'relative').removeClass('draggable ui-draggable ui-draggable-dragging')
							);
							setDraggable($('ul.left .itembloc[data-bloc="'+ui.draggable.attr('data-bloc')+'"]'))
						}

					},

					over: function(e, ui) {
						*//*lastOverElement = $('iframe#dsnrframe').contents()[0].elementFromPoint(350, (ui.position.top - 100))
						console.log('element from point ', e, ui)
						//if (elem)
						if ($(lastOverElement).hasClass('dsnr-injector-text')) {
							$(lastOverElement).css('background', 'red');
						}*//*
					}
				});

			}


			$(document).on('click', function(e) {
				console.log(e)
			})


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
		})*/

	</script>

</body>
</html>