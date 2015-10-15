<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	# Suppresion du TYPE (suppression de toute les donnes reliees)
	#
	if(count($_POST['killme']) > 0){
		foreach($_POST['killme'] as $id_type){
			$app->apiLoad('type')->typeRemove($id_type);
			$app->apiLoad('type')->typeRemoveProfile($id_type);
		}
		$app->go("./");
	}else
	
	# Gerer le TYPE
	#
	if($_POST['action']){

		$def['k_type'] = array(
			'is_business'		=> array('value' => $_POST['is_business'],			'zero'	=> true),
			'is_gallery'		=> array('value' => $_POST['is_gallery'],			'zero'	=> true),
			'is_ad'				=> array('value' => $_POST['is_ad'],				'zero' 	=> true),
			'is_cp'				=> array('value' => $_POST['is_cp'],				'zero' 	=> true),
			'use_group'			=> array('value' => $_POST['use_group'],			'zero' 	=> true),
			'use_search'		=> array('value' => $_POST['use_search'],			'zero' 	=> true),
			'use_chapter'		=> array('value' => $_POST['use_chapter'],			'zero' 	=> true),
			'use_category'		=> array('value' => $_POST['use_category'],			'zero' 	=> true),
			'use_socialforum'	=> array('value' => $_POST['use_socialforum'],		'zero' 	=> true),
			'typeName'			=> array('value' => $_POST['typeName'], 			'check'	=> '.'),
			'typeKey' 			=> array('value' => $_POST['typeKey'], 				'check'	=> '.'),
			'typeTemplate'		=> array('value' => $_POST['typeTemplate'])
		);

		if($app->formValidation($def)){
			$result  = $app->apiLoad('type')->typeSet($_POST['id_type'], $def);
			$id_type = $app->apiLoad('type')->id_type;
			$message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:'.$app->db_error;

			if($result){
				$app->apiLoad('field')->fieldCacheBuild();
				if($_POST['id_type'] == NULL) $app->apiLoad('type')->typeSetProfile($id_type);
				$app->go("./?id_type=".$id_type);
			}
		}else{
			$message = 'KO:'._('Validation failed');
		}
	}

	if($_REQUEST['id_type'] != NULL){
		$data = $app->apiLoad('type')->typeGet(array(
			'id_type' => $_REQUEST['id_type']
		));
	}else{
		$data = array(
			'is_cp'				=> true,
			'use_group'			=> true,
			'use_search'		=> false,
			'use_chapter'		=> false,
			'use_category'		=> true,
			'use_socialforum'	=> false
		);
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC . '/head.php'); ?>
    <link rel="stylesheet" type="text/css" href="ui/css/type.css" />
</head>
<body>

<header><?php
	include(COREINC . '/top.php');
	include(dirname(__DIR__) . '/content/ui/menu.php')
?></header>

<div class="inject-subnav-right hide">
	<li><a href="../field/" class="nomargin btn btn-mini"><?php echo _('Manage fields'); ?></a></li>
</div>

<div id="app"><div class="wrapper"><div class="row-fluid">

	<?php if(sizeof($_POST['remove']) > 0 ){ ?>
	<div class="message messageWarning">
		<p><?php echo _('<b>WARNING</b> you are about to remove data, this action is not cancelable (Database table destruction)') ?></p>
		
		<form action="./" method="post">
			<?php foreach($_POST['remove'] as $e){ ?>
			<input type="text" name="killme[]" value="<?php echo $e ?>" />
			<?php } ?>
			<input type="submit" value="valider" />
		</form>
	</div>
	<?php }else if(isset($_GET['noData'])){ ?>
	<div class="message messageWarning">
		<?php echo _('You can not add, see, browse data while there is no type defined'); ?>
	</div>
	<?php } ?>

	<form action="./" method="post" id="listing" class="span6">

		<table border="0" cellspacing="0" cellpadding="0" class="listing">
			<thead>
				<tr>
					<th width="20" class="icone"><i class="icon-remove icon-white"></i></th>
					<th><?php echo _('Name'); ?></th>
				</tr>
			</thead>
		</table>
			
		<ul id="items"><?php
	
			$types = $app->apiLoad('type')->typeGet(array(
				'profile'	=> true,
				'debug'		=> false
			));
			
			if(sizeof($types) > 0){
				foreach($types as $e){

					$color   = ($_REQUEST['id_type'] == $e['id_type']) ? ' selected' : '';
					$content = ($e['is_gallery']) ? 'gallery' : 'index';

					echo '<li id="'.$e['id_type'].'" class="clearfix '.$color.'"><div class="holder">';
						echo '<div class="check"><input type="checkbox" name="killme[]" value="'.$e['id_type'].'" /></div>';
						echo '<div class="handle"><i class="icon-move"></i></div>';

						echo '<div class="data">';
						echo '<a href="./?id_type='.$e['id_type'].'">'.$e['typeName'].'</a>';
						echo '</div>';

						echo '<div class="content clearfix">';
						echo '<a href="../content/'.$content.'?id_type='.$e['id_type'].'" class="view">'._('View').'</a> ';
                        echo '<a href="../field/asso?id_type='.$e['id_type'].'" class="fields">'._('Manage fields').'</a>';

						if($e['is_gallery'] != 1){
                            echo '<a href="row?id_type='.$e['id_type'].'" class="columns">'._('Columns').'</a>';
						}
						echo '</div>';

					echo '</div></li>';
				}
			
			}else{
				echo '<div class="noType">';
				echo _('You need to create a type to create new contents');
				echo '</div>';
			}
		?></ul>
	
		<div class="clearfix">
			<?php if(sizeof($types) > 0){ ?>
			<a onclick="apply();" class="btn btn-mini"><?php echo _('Remove selection'); ?></a>
			<a href="./" class="btn btn-mini"><?php echo _('Cancel'); ?></a>
			<?php } ?>
		</div>
	</form>

	<form action="./" method="post" id="data" class="span6">
		<input type="hidden" name="action" value="1" />
		<input type="hidden" name="id_type" value="<?php echo $data['id_type'] ?>" />
		
		<?php if($data['id_type'] == NULL){ ?>
			<div class="alert message messageWarning"><?php echo _('Once a type is created, you will not be able to change business, ad, or gallery settings'); ?></div>
		<?php } ?>
	
		<table cellpadding="0" cellspacing="0" border="0" class="form">
			<?php if($data['id_type'] == NULL){ ?>
			<tr valign="top">
				<td><?php echo _('Business'); ?></td>
				<td><input type="checkbox" name="is_business" value="1" <?php if($app->formValue($data['is_business'], $_POST['is_business'])) echo " checked" ?>/></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Gallery'); ?></td>
				<td><input type="checkbox" name="is_gallery" value="1" <?php if($app->formValue($data['is_galery'], $_POST['is_gallery'])) echo " checked" ?>/></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Ad'); ?></td>
				<td><input type="checkbox" name="is_ad" value="1" <?php if($app->formValue($data['is_ad'], $_POST['is_ad'])) echo " checked" ?> /></td>
			</tr>
			<?php }else{

				$on  = '<i class="icon icon-ok"></i>';
				$off = '<i class="icon icon-ban-circle"></i>';
			?>
			<tr valign="top">
				<td width="100"><?php echo _('Business'); ?></td>
				<td><?php echo ($data['is_business']) ? $on : $off; ?>
					<input type="hidden" name="is_business" value="<?php echo $data['is_business'] ?>" /></td>
			</tr>
			<tr valign="top">
                <td><?php echo _('Gallery'); ?></td>
				<td><?php echo ($data['is_gallery']) ? $on : $off; ?>
					<input type="hidden" name="is_gallery" value="<?php echo $data['is_gallery'] ?>" /></td>
			</tr>
			<tr valign="top">
                <td><?php echo _('Ad'); ?></td>
				<td><?php echo ($data['is_ad']) ? $on : $off; ?>
					<input type="hidden" name="is_ad" value="<?php echo $data['is_ad'] ?>" /></td>
			</tr>
			<?php } ?>
			<tr valign="top">
				<td width="150"><?php echo _('Back office view'); ?></td>
				<td><input type="checkbox" name="is_cp" value="1" <?php if($app->formValue($data['is_cp'], $_POST['is_cp'])) echo " checked" ?> /></td>
			</tr>
			<tr valign="top">
				<td><?php echo _('Relationships'); ?></td>
				<td>
					<input type="checkbox" name="use_group"			value="1" <?php if($app->formValue($data['use_group'], 			$_POST['use_group'])) 			echo "checked" ?> /> <?php echo _('Groups') ?><br />
					<input type="checkbox" name="use_search"		value="1" <?php if($app->formValue($data['use_search'],			$_POST['use_search'])) 			echo "checked" ?> /> <?php echo _('Smart groups') ?><br />
					<input type="checkbox" name="use_chapter"		value="1" <?php if($app->formValue($data['use_chapter'],		$_POST['use_chapter'])) 		echo "checked" ?> /> <?php echo _('Chapters') ?><br />
					<input type="checkbox" name="use_category"		value="1" <?php if($app->formValue($data['use_category'],		$_POST['use_category'])) 		echo "checked" ?> /> <?php echo _('Categories') ?><br />
					<input type="checkbox" name="use_socialforum"	value="1" <?php if($app->formValue($data['use_socialforum'],	$_POST['use_socialforum'])) 	echo "checked" ?> /> <?php echo _('Social forums') ?>
				</td>
			</tr>
			<tr>
				<td><?php echo _('Name'); ?></td>
				<td><input type="text" name="typeName" value="<?php echo $app->formValue($data['typeName'], $_POST['typeName']); ?>" /></td>
			</tr>
			<tr>
				<td><?php echo _('Key'); ?></td>
				<td><input type="text" name="typeKey" value="<?php echo $app->formValue($data['typeKey'], $_POST['typeKey']); ?>" /></td>
			</tr>
			<tr>
				<td><?php echo _('Template'); ?></td>
				<td><?php
					echo $app->apiLoad('template')->templateSelector(array(
						'name'	=> 'typeTemplate',
						'value'	=> $app->formValue($data['typeTemplate'], $_POST['typeTemplate'])
					));
				?></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<a onclick="$('#data').submit()" class="btn btn-mini"><?php echo _('Save'); ?></a>
					<a href="./" class="btn btn-mini"><?php echo _('New'); ?></a>
				</td>
			</tr>
		</table>
	</form>

</div></div></div>

<?php include(COREINC.'/end.php'); ?>
<script src="ui/js/type.js" type="text/javascript"></script>
<script>

    function apply(){
        if(confirm("<?php echo addslashes(_('SUPPRIMER ?')) ?>")) $('#listing').submit();
    }
</script>

</body></html>