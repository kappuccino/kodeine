<?php

	$api	= $app->apiLoad('newsletter');
	$pref	= $app->configGet('newsletter');

	$apiConnector	= $app->apiLoad('newsletterCloudApp');

	if($_POST['action']){
		$do = true;

		# Search
		if(sizeof($_POST['id_newsletterSearch']) > 0){
			foreach($_POST['id_newsletterSearch'] as $e){
				$newslettersearch[] = $e;
			}
			$newsletterSearch = '@@'.implode('@@', $newslettersearch).'@@';
		}

		# Groupe
		if(sizeof($_POST['id_newsletterGroup']) > 0){
			foreach($_POST['id_newsletterGroup'] as $e){
				$newsletterGroup[] = $e;
			}
			$newsletterGroup = '@@'.implode('@@', $newsletterGroup).'@@';
		}

		# Liste
		if(sizeof($_POST['id_newsletterList']) > 0){
			foreach($_POST['id_newsletterList'] as $e){
				$newsletterList[] = $e;
			}
			$newsletterList = '@@'.implode('@@', $newsletterList).'@@';
		}
	
		$def['k_newsletter'] = array(
		#	'is_archive'				=> array('value' => $_POST['is_archive'],			'zero'  => true),
			'newsletterAllUser'			=> array('value' => $_POST['newsletterAllUser'],	'zero'  => true),
			'newsletterSearch'			=> array('value' => $newsletterSearch),
			'newsletterGroup'			=> array('value' => $newsletterGroup),
			'newsletterList'			=> array('value' => $newsletterList),
			'newsletterListRaw'			=> array('value' => $_POST['newsletterListRaw'])
		);

		if(!$app->formValidation($def)) $do = $false;

		if($do){
			$result	 = $app->apiLoad('newsletter')->newsletterSet($_POST['id_newsletter'], $def);
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Probleme, APP : <br />'.$app->db_error;

			if($result && $_POST['do'] == 'back'){
				header("Location: ./data?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
				die();
			}else
			if($result && $_POST['do'] == 'test'){
				$app->apiLoad('newsletter')->newsletterPreview($_POST['id_newsletter']);
				$message = ($result) ? 'OK: Newsletter enregistré et envoyé en mode [TEST] ('.$pref['test'].')' : 'KO: Erreur Test';
			}else
			if($result && $_POST['do'] == 'send'){
				if($data['newsletterSendDate'] == NULL){
					header("Location: push?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter);
					exit();
				}else{
					$message = 'KO: Cette newsletter est en cours d\'envois ou bien elle a déjà été envoyé.';
				}
			}
	
			header("Location: data-list?id_newsletter=".$app->apiLoad('newsletter')->id_newsletter.'&message='.urlencode($message));

		}
	}

	if($_REQUEST['id_newsletter'] != NULL){
		$data = $app->apiLoad('newsletter')->newsletterGet(array(
			'id_newsletter' 	=> $_REQUEST['id_newsletter']
		));

		$title = $data['newsletterName'];
	}else{
		$title = 'Nouvelle newsletter';
	}
	
		


?><!DOCTYPE html>
<head>
	<?php include(COREINC.'/head.php'); ?>
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/bootstrap3/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/flatui/css/flat-ui.css" />
	<link rel="stylesheet" type="text/css" media="all" href="ui/css/dsnr-ui.css" />
</head>

<body>

<header><?php
	include(COREINC.'/top.php');
	include(dirname(dirname(__DIR__)).'/ui/menu.php');
    #include(dirname(dirname(__DIR__)).'/ui/steps.php');
?></header>

<div class="inject-subnav-right hide">

	<?php /*if($_REQUEST['id_newsletter'] > 0){ */?><!--
	<li><a href="preview?id_newsletter=<?php /*echo $_REQUEST['id_newsletter'] */?>" class="btn btn-small" target="_blank">Prévisualiser</a></li>
    <li><a href="javascript:$('#do').val('test');$('#data').submit();" class="btn btn-small">Envoyer un mail de test</a></li>
	<?php /*} */?>
	<?php /*if($data['newsletterSendDate'] == NULL){ */?>
	<li><a href="javascript:$('#do').val('send');$('#data').submit();" class="btn btn-small btn-danger">Envoyer aux abonnés</a></li>
	<?php /*} */?>
	<?php /*if($data['newsletterSendDate'] != NULL){ */?>
	<li><a href="analytic?id_newsletter=<?php /*echo $_REQUEST['id_newsletter'] */?>" class="btn btn-small">Consulter les statistiques</a></li>
	<li><a href="data" class="btn btn-small">Nouveau</a></li>
	<?php /*} */?>
	<li><a href="javascript:$('#data').submit();" class="btn btn-small btn-success">Enregistrer</a></li>-->
				
</div>

<div id="app">

<?php	if($message == NULL && $_GET['message'] != NULL) $message = urldecode($_GET['message']);
	if($message != NULL){
		list($class, $message) = $app->helperMessage($message);
		echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	}
?>

<form action="data-list?id_newsletter=<?php echo $data['id_newsletter'] ?>" method="post" id="data" enctype="multipart/form-data">

	<input type="hidden" name="action" value="1" />
	<input type="hidden" name="id_newsletter" value="<?php echo $data['id_newsletter'] ?>" />
	<input type="hidden" name="do" id="do" value="" />

	<table cellpadding="5" width="100%" class="tile">
		<!--<tr>
			<td>Archivage</td>
			<td>
				<input type="checkbox" id="is_archive" name="is_archive" value="1" <?php if($app->formValue($data['is_archive'], $_POST['is_archive'])) echo "checked" ?> />
				<label for="is_archive">Si cette option est activée, la newsletter sera lisible par tout le monde depuis le site internet</label>
			</td>
		</tr>-->
		<tr valign="top">
			<td colspan="2">

				<div style="float: left;margin-left: 30px; width:300px;margin-bottom: 20px;">
					<h5>Destinataires <i id="totalView"></i></h5>
					<a href="javascript:$('#data').submit();" class="btn btn-small btn-success" style="color:white">Enregistrer la liste d'abonnés</a>
				</div>

				<span style="float:right;margin-bottom: 5px;">
					<div class="alert alert-info">
						<input type="checkbox" name="newsletterAllUser" id="newsletterAllUser" value="1" <?php if($app->formValue($data['newsletterAllUser'], $_POST['newsletterAllUser'])) echo "checked" ?> />
						<label for="newsletterAllUser">&nbsp;Si cette option est activée, cette newsletter sera envoyée à tous les utilisateurs, même ceux qui n'acceptent pas de recevoir de newsletter.</label>
					</div>

				</span>
				<table border="0" width="100%" class="dest desttab">
					<tr>
						<!--
						<td width="20%" height="18"><i>Abonnés aux newsletters	<span id="totalType"></span></i></td>
						-->
						<td width="25%"><i>Groupe d'utilisateurs				(<span id="totalGroup"></span>)</i></td>
						<td width="25%"><i>Utilisateur par critères				(<span id="totalSearch"></span>)</i></td>
						<td width="25%"><i>Liste enregistrées					(<span id="totalList"></span>)</i></td>
						<td width="25%"><i>Liste d'emails - un par ligne		(<span id="totalRaw"></span>)</i></td>
					</tr>
					<tr valign="top">
						<td><?php
							$val	= $app->formValue($data['newsletterGroup'], $_POST['id_newsletterGroup']);
							$val	= is_array($val) ? $val : array();

							echo $app->apiLoad('user')->userGroupSelector(array(
								'name'		=> 'id_newsletterGroup[]',
								'id'		=> 'id_newsletterGroup',
								'multi' 	=> true,
								'style' 	=> 'width:100%; height:130px;',
								'profile'	=> true,
								'events'	=> "onchange=\"getTotal()\"",
								'value'		=> $val
							));
						?></td>
						<td><select name="id_newsletterSearch[]" id="id_newsletterSearch" multiple="multiple" style="width:100%; height:130px;" onchange="getTotal()"><?php
							$val	= $app->formValue($data['newsletterSearch'], $_POST['id_newsletterSearch']);
							$val	= is_array($val) ? $val : array();
							$search	= $app->searchGet(array('type' => 'user'));
			
							foreach($search as $e){
								$chk = in_array($e['id_search'], $val) ? ' selected' : NULL;
								echo "<option value=\"".$e['id_search']."\" ".$chk.">".$e['searchName']."</option>";
							}
						?></select></td>
						<td><select name="id_newsletterList[]" id="id_newsletterList" multiple="multiple" style="width:100%; height:130px;" onchange="getTotal()"><?php
							$val	= $app->formValue($data['newsletterList'], $_POST['id_newsletterlist']);
							$val	= is_array($val) ? $val : array();
							$list	= $app->apiLoad('newsletter')->newsletterListGet();
			
							foreach($list as $e){
								$chk = in_array($e['id_newsletterlist'], $val) ? ' selected' : NULL;
								echo "<option value=\"".$e['id_newsletterlist']."\" ".$chk.">".$e['listName']."</option>";
							}
						?></select></td>
						<td>
							<textarea name="newsletterListRaw" id="newsletterListRaw" style="width:97%; height:124px;" onfocus="getTotal()" onblur="getTotal()"><?php
								echo $app->formValue($data['newsletterListRaw'], $_POST['newsletterListRaw'])
							?></textarea>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

</form>


</div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/jquery.tinymce.js"></script>
<script src="../core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script>
	
	function getTotal(){
		$('#totalType, #totalGroup, #totalSearch, #totalList, #totalRaw').html('...');
		
		url = '?';
		
		group = getSelected($('#id_newsletterGroup'));
		if(group.length > 0) url += '&group=' + group.join(',');

		search = getSelected($('#id_newsletterSearch'));
		if(search.length > 0) url += '&search=' + search.join(',');

		list = getSelected($('#id_newsletterList'));
		if(list.length > 0) url += '&list=' + list.join(',');
			
			var get = $.ajax({
				url : 'helper/total'+url,
				dataType : 'json'
			}).done(function(d) {
				$('#totalGroup').html(d.group);
				$('#totalSearch').html(d.search);
				$('#totalList').html(d.list);
				
				rawLength = $('#newsletterListRaw').val().split(/\n/g).length;
				if(rawLength == 1){
					var str = $.trim($('#newsletterListRaw').val());
					if(str == '') rawLength = 0;
				}

				if($('#newsletterListRaw').val().length == 0){
					$('#totalRaw').html('0');
				}else{
					$('#totalRaw').html(rawLength); // +' (validité des mails non vérifiée)');
				}

				$('#totalView').html('('+ (d.total + rawLength) +')');
			});

			var getget = $.ajax({
				url : 'helper/total'+url,
				dataType : 'json'
			}).done(function(d) {
				$('#totalGroup').html(d.group);
				$('#totalSearch').html(d.search);
				$('#totalList').html(d.list);
			});
	}
	
	$(function() {
		setTimeout(function() {
			getTotal();
		}, 250);
	});

	function getSelected(opt) {
		var selected = new Array();
		var index = 0;
		opt.find("option:selected").each( function() {
			selected.push($(this).val());
		});
		/*for (var intLoop = 0; intLoop < opt.length; intLoop++) {
			
			//if ((opt[intLoop].selected) || (opt[intLoop].checked)) {
			if ((opt[intLoop].value)) {
				//alert(opt[intLoop].value);
				selected.push(opt[intLoop].value);
			}
		}*/
		return selected;
	}
	
</script>
</body></html>