<?php
	if($_POST['action'] == 'sql'){
		$do = true;

		$def['k_media'] = array(
			'mediaUrl'		=> array('value' => $_POST['url']),
			'mediaTitle'	=> array('value' => $_POST['mediaTitle']),
			'mediaCaption'	=> array('value' => $_POST['mediaCaption'])
		);

		if(!$app->formValidation($def)) $do = false;

		if($do){
			$result = $app->mediaDataSet($_POST['url'], $def);
		}
	}

	$me		= $app->mediaParser($_REQUEST['url']);
	$parent	= $me['clean'].'.'.$me['type'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<script type="text/javascript" src="<?php echo KPROMPT ?>/app/admin/ressource/js/mootools-1.2.5-core.js"></script>
	<script type="text/javascript" src="<?php echo KPROMPT ?>/app/admin/ressource/js/mootools-1.2.4.4-more.js"></script>
	<style>
		body{
			color:#000;
			padding: 0px;
			margin: 0px;
			font-family: Arial;
			font-size: 12px;
		}
		a{
			color:#000;
			text-decoration: underline;
		}
		textarea{
			width:99%;
			font-family:Arial;
			font-size:12px;
			padding:3px;
		}
		
		
		 td{
			border: 1px solid #000;
		}
	</style>
</head>
<body>
<?php

	if(isset($_REQUEST['reloadIPTC'])){
		$app->mediaIndex($_REQUEST['url']);
	}else
	if(isset($_REQUEST['removeIPTC'])){
		$app->mediaIndexRemove($_REQUEST['url']);
	}

	if($_REQUEST['url'] != $parent){
		if(file_exists(KROOT.$parent)){
			$me 	= $parent;
			$data 	= $app->mediaDataGEt($parent);
		}else{
			die("Parent introuvable");
		}
	}else{
		$me = $_REQUEST['url'];
		$data = $app->mediaDataGEt($_REQUEST['url']);
	}
	
	$infos = $app->mediaInfos($me);
?>
<form method="post" action="media.metadata.php" id="meta" name="meta">

	<input type="hidden" name="action" value="sql" />
	<input type="hidden" name="url" value="<?php echo $me ?>"
	
	<table border="5" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td colspan="2">
				<a href="javascript:$('meta').submit();">Enregistrer</a> ou <a href="javascript:parent.panelHide();">Annuler</a>
				&nbsp; &nbsp; &nbsp; 
				<i><?php echo basename($_REQUEST['url']); if($me != $_REQUEST['url']) echo " <i>(".$me.")</i>"; ?></i>
			</td>
		</tr>
		<tr valign="top">
			<td width="50%"><b>Titre</b><br />		<textarea name="mediaTitle" 	rows="4"><?php echo $data['mediaTitle'] ?></textarea></td>
			<td width="50%"><b>Description</b><br /><textarea name="mediaCaption"	rows="4"><?php echo $data['mediaCaption'] ?></textarea></td>
		</tr>
	</table>

</form>

<script>
/*
	function addCustomKeyWord(){
	
		if($('customKeyWord')){
			newKey = $('customKeyWord').value;
			lstKey = newKey.split(',');
			
			for(i=0; i<lstKey.length; i++){
				newKey = lstKey[i];

				if(newKey != ''){
					container 	= new Element('span', 	{'id' : 'kKey-'+newKey}).inject('customKeyHolding');
					field 		= new Element('input',	{'type' : 'hidden', 'name' : 'customKeys[]', 'value' : newKey}).inject(container);
					label 		= new Element('span', 	{'html' : newKey}).inject(container);
					image 		= new Element('img',{
						'align'	: 'absmiddle',
						'title' : 'kKey-'+newKey,
						'src' : 'ressource/icone/removekw.png',
						'events': {
							'click' : function(){
								removeCustomKeyWord(this);
							}
						}
					}).inject(container);
			
					var remote = new Request.JSON({
						url: 'content/lib.action.php',
						headers: {'contentType': 'text/html'},
						onComplete:function(r){
							if(r.message  != null) admin.log(r.message);
							if(r.callBack != null) eval(r.callBack);
						}
					}).get({'action':'customKeyword', 'todo':'insert', 'src':'<?php echo $me ?>', 'key':newKey});
				}
			}
			$('customKeyWord').value = '';			
		}
	}

	function removeCustomKeyWord(oldKey){
		var value  = oldKey.getParent().id.substring(5, oldKey.getParent().id.length);
	 
		var remote = new Request.JSON({
			url: 'content/lib.action.php',
			headers: {'contentType': 'text/html'},
			onComplete:function(r){
				if(r.message  != null) admin.log(r.message);
				if(r.callBack != null) eval(r.callBack);

				oldKey.getParent().destroy();
			}
		}).get({'action':'customKeyword', 'todo':'remove', 'src':'<?php echo $me ?>', 'key':value});
	}
*/
</script>

</body></html>