<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	if($_REQUEST['id_type'] == NULL){
		$type = $app->apiLoad('type')->typeGet(array('profile' => true));
		
		if(sizeof($type) == 0) header("Location: ../type/?noData");
	}

	if(isset($_REQUEST['duplicate'])){
		$app->apiLoad('content')->contentDuplicate($_REQUEST['duplicate']);
	}
	if(sizeof($_POST['see']) > 0){
		foreach($_POST['see'] as $e => $v){
			$app->dbQuery("UPDATE k_content SET contentSee=".$v." WHERE id_content=".$e);
		}
	}
	if(sizeof($_POST['remove']) > 0){
		foreach($_POST['remove'] as $e){
			$app->apiLoad('content')->contentRemove($_REQUEST['id_type'], $e, $_REQUEST['language']);
		}
	}

	// Type
	if(isset($_REQUEST['id_type'])){
		$type 		= $app->apiLoad('type')->typeGet();
		$id_type	= $_REQUEST['id_type'];
		$cType		= $app->apiLoad('type')->typeGet(array('id_type' => $id_type));

		// Filter (verifier content / album)
		if($id_type == NULL)		die("APP : id_type IS NULL");
		if($cType['is_gallery'])	header("Location: gallery?id_type=".$cType['is_type']);

		// Filter
		if(isset($_GET['cf'])){
			$app->filterSet('content'.$id_type, $_GET);
			$filter = array_merge($app->filterGet('content'.$id_type), $_GET);
		}else
		if(isset($_POST['filter'])){
			$app->filterSet('content'.$id_type, $_POST['filter']);
			$filter = array_merge($app->filterGet('content'.$id_type), $_POST['filter']);
		}else{
			$filter = $app->filterGet('content'.$id_type);
		}

		$dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';
	}

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>

<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>

<?php if(isset($_REQUEST['id_type'])){ ?>
<div class="inject-subnav-right hide">
	<li><a onclick="filterToggle('content<?php echo $id_type ?>');" class="btn btn-small"><?php echo _('Display settings'); ?></a></li>
	<li>
		<div class="btn-group">
			<a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-list"></i> <?php echo $cType['typeName']; ?> <span class="caret"></span></a>
			<ul class="dropdown-menu"><?php
			foreach($app->apiLoad('type')->typeGet(array('profile' => true)) as $e){
				echo '<li class="clearfix">';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery' : 'index').'?id_type='.$e['id_type'].'" class="left">'.$e['typeName'].'</a>';
				echo '<a href="'.(($e['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$e['id_type'].'" class="right"><i class="icon icon-plus-sign"></i></a>';
				echo '</li>';
			}
			?></ul>
		</div>
	</li>
	<li><a href="<?php echo (($cType['is_gallery']) ? 'gallery-album' : 'data' )."?id_type=".$id_type; ?>" class="btn btn-small btn-success"><?php printf(_('Add a %s'), $cType['typeName']); ?> </a></li>
</div>
<?php } ?>

<div id="app"><?php

	if(!isset($_REQUEST['id_type'])){ ?>

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th><?php echo _('Types') ?></th>
					<th width="100"><?php echo _('Listing') ?></th>
					<th width="200"><?php echo _('Add') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($type as $e){
				$list = ($e['is_gallery']) ? 'gallery' : './';
				?>
				<tr>
					<td><a href="<?php echo $list ?>?id_type=<?php echo $e['id_type'] ?>"><?php echo $e['typeName'] ?></a></td>
					<td><a href="<?php echo $list ?>?id_type=<?php echo $e['id_type'] ?>" class="btn btn-mini">Voir la liste</a></td>
					<td><?php
						if($e['is_gallery'] != '1'){
							echo '<a href="data?id_type='.$e['id_type'].'" class="btn btn-mini btn-success" style="color: #FFF;">Ajouter un nouveau</a>';
						}
					?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

	<?php }else{ ?>

	<div class="quickForm" style="display:<?php echo ($filter['open']) ? 'block' : 'none;' ?>;">
	<form action="index" method="post" class="form-horizontal">

		<input type="hidden" name="id_type"			value="<?php echo $id_type ?>" />
		<input type="hidden" name="filter[open]"	value="1" />
		<input type="hidden" name="filter[offset]"	value="0" />

        <label class="control-label"><?php echo _('Search'); ?></label>
        <input type="text" name="filter[q]" class="input-small" placeholder="" value="<?php echo $filter['q'] ?>" size="5" />

        <label class="control-label"><?php echo _('Limit'); ?></label>
        <input type="text" name="filter[limit]" class="input-small" placeholder="" value="<?php echo $filter['limit'] ?>" size="3" />

		<label class="control-label"><?php echo _('Category'); ?></label>
		<?php
			echo $app->apiLoad('category')->categorySelector(array(
				'name'		=> 'filter[id_category]',
				'value'		=> $filter['id_category'],
				'language'	=> 'fr',
				'one'		=> true,
				'empty'		=> true
			)); ?>

		<?php if($cType['is_business'] == '1'){ ?>
		<label class="control-label"><?php echo _('Shop'); ?></label>
		<select name="filter[id_shop]">
			<option></option><?php
			$shop = $app->apiLoad('shop')->shopGet();
			foreach($shop as $e){
				echo "<option value=\"".$e['id_shop']."\"".(($filter['id_shop'] == $e['id_shop']) ? ' selected' : '').">".$e['shopName']."</option>";
			}
		?></select>
		<?php } ?>
		
		<label class="control-label"><?php echo _('Language'); ?></label>
		<select name="filter[language]"><?php
			foreach($app->countryGet(array('is_used' => 1)) as $e){
				$sel = ($e['iso'] == $filter['language']) ? ' selected' : NULL;
				echo "<option value=\"".$e['iso']."\"".$sel.">".$e['countryLanguage']."</option>";
			}		
		?></select>

		&nbsp;<?php echo _('All'); ?> 		    <input type="radio"  name="filter[viewChildren]" 	value="0" <?php if(!$filter['viewChildren']) echo ' checked'; ?> />
		&nbsp;<?php echo _('Ordered'); ?> 	    <input type="radio"  name="filter[viewChildren]" 	value="1" <?php if( $filter['viewChildren']) echo ' checked'; ?> />
		&nbsp;<?php echo _('Inheritance'); ?> 	<input type="hidden" name="filter[categoryThrough]" value="0" />

		<input type="checkbox" name="filter[categoryThrough]" value="1" <?php if($filter['categoryThrough']) echo ' checked'; ?> />

		<button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
        <button class="btn btn-mini"><?php echo _('Cancel'); ?></button>
        <a href="../type/row?id_type=<?php echo $id_type; ?>" class="btn btn-mini"><?php echo _('Manage columns'); ?></a>
	</form>
	</div>	

	
	<?php
		// Content
		$language	= ($filter['language'] != '') ? $filter['language'] : 'fr';
		$opt		= array(
			'debug'	 			=> false,
			'id_type' 			=> $id_type,
			'useChapter'		=> false,
			'useGroup'			=> false,
			'contentSee'		=> 'ALL',
		#	'assoUser'			=> true,
			'language'			=> $language,
			'id_category'		=> $filter['id_category'],
			'categoryThrough'	=> (($filter['categoryThrough'] && $filter['id_category'] != '') ? true : false),
			'limit'				=> $filter['limit'],
			'offset'			=> $filter['offset'],
			'search'			=> $filter['q'],
			'order'				=> $filter['order'],
			'direction'			=> $filter['direction'],
			'id_search'			=> $filter['id_search'],
		);
		
		if($filter['viewChildren']){
			$opt['id_parent'] = '0';
		}else{
			$opt['id_parent'] = '*';
		}
		
		if($filter['id_shop']) $opt['id_shop'] = $filter['id_shop'];
		
		$content= $app->apiLoad('content')->contentGet($opt);
		$total	= $app->apiLoad('content')->total;
		$limit	= $app->apiLoad('content')->limit;
	
		$fields = $app->apiLoad('field')->fieldGet(array('id_type' => $id_type, 'debug' => false));
		$lang	= $app->countryGet(array('is_used' => true));

    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    function view($app, $cType, $filter, $e, $level=0, $count=NULL){

        if(intval($e['id_content']) == 0) return false;

        $version = $app->apiLoad('content')->contentVersionGet(array(
            'id_content'	=> $e['id_content'],
            'language'		=> $e['language']
        ));

        $colspan = '';
        if(!$cType['is_business']) {
            //$colspan = 'colspan="2"';
        }

        foreach($app->dbMulti("SELECT language FROM k_contentdata WHERE id_content=".$e['id_content']) as $l){
            $languages .= "<a href=\"data-language?id_content=".$e['id_content']."&language=".$l['language']."\" class=\"lang\">".strtoupper($l['language'])."</a> ";
        }

        $link = "data?id_content=".$e['id_content']."&language=".$e['language'];

        echo
            "<tr valign=\"top\">".
            "<td><input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_content']."\" class=\"chk cb\" id=\"chk_remove_".$count."\" /></td>".
            "<td>".
            "<input type=\"hidden\"		name=\"see[".$e['id_content']."]\" value=\"0\" />".
            "<input type=\"checkbox\"	name=\"see[".$e['id_content']."]\" value=\"1\" class=\"chk cs\" ".(($e['contentSee']) ? "checked" : '')." id=\"chk_see_".$count."\" />".
            "</td>".
            "<td class=\"icone\"><a href=\"javascript:duplicate(".$e['id_content'].");\"><i class=\"icon-tags\"></i></a></td>".
            //"<td style=\"padding-left:5px;\">".sizeof($version)."</td>".
            //"<td><a href=\"comment?id_content=".$e['id_content']."\">".$e['contentCommentCount']."</a></td>".
            "<td>".$languages."</td>".
            "<td><a href=\"".$link."\">".$e['id_content']."</a></td>".
            "<td class=\"dateTime\">".
            "<a href=\"".$link."\">".
            "<span class=\"date\">".$app->helperDate($e['contentDateCreation'], '%d.%m.%Y')."</span> ".
            "<span class=\"time\">".$app->helperDate($e['contentDateCreation'], '%Hh%M')."</span>".
            "</a>".
            "</td>".
            "<td class=\"dateTime\">".
            "<a href=\"".$link."\">".
            "<span class=\"date\">".$app->helperDate($e['contentDateUpdate'], '%d.%m.%Y')."</span> ".
            "<span class=\"time\">".$app->helperDate($e['contentDateUpdate'], '%Hh%M')."</span>".
            "</a>".
            "</td>";

        if($cType['is_business']){
            echo "<td><a href=\"".$link."\" style=\"width:100%;display:block;\">".$e['contentRef']."</a></td>";
        }

        echo
            "<td style=\"padding-left:".(5 + ($level * 15))."px;\" ".$colspan."><a href=\"".$link."\" style=\"width:100%;display:block;\">".$e['contentName']."</a></td>";


        viewRow($app, $cType, $filter, $e, $level, $count);

        echo "</tr>";

        if($filter['viewChildren']){
            $subs = $app->dbMulti("SELECT id_content FROM k_content WHERE id_parent=".$e['id_content']." ORDER BY pos_parent ASC");

            foreach($subs as $sub){
                $sub = $app->apiLoad('content')->contentGet(array(
                    'debug'	 		=> false,
                    'raw'			=> true,
                    'language'		=> $e['language'],
                    'id_content' 	=> $sub['id_content']
                ));

                view($app, $cType, $filter, $sub, $level+1, null);
            }
        }

    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

    function viewRow($app, $cType, $filter, $e, $level=0, $count=NULL){

        if(is_array($cType['typeListLayout'])) {
            foreach($cType['typeListLayout'] as $f) {
                $field = array();
                if(is_numeric($f['field'])) {
                    $field	= $app->apiLoad('field')->fieldGet(array('id_field' => $f['field']));
                    $aff    = $e['field'][$field['fieldKey']];
                }else {
                    if($f['field'] == 'contentMedia') $field['fieldType'] = 'media';
                    $aff    = $e[$f['field']];
                    if(in_array($f['field'], array('contentDateStart', 'contentDateEnd')) && $e[$f['field']] != NULL) {
                        $aff =  "<span class=\"date\">".$app->helperDate($aff, '%d.%m.%Y')."</span> ".
                                "<span class=\"time\">".$app->helperDate($aff, '%Hh%M')."</span>";
                    }
                }

                $islink = true;

                // Type date
                if($field['fieldType'] == 'date') $aff      = $app->helperDate($aff, '%d.%m.%Y');
                if($field['fieldType'] == 'boolean') $aff   = ($aff == '1') ? 'Oui' : 'Non';

                // Type Array
                if(is_array($aff)) {
                    $tmp = array();

                    // Type User
                    if($field['fieldType'] == 'user') {
                        foreach($aff as $a) {
                            $tmp[]  =  '<a href="../user/data?id_user='.$a['id_user'].'" title="ID : '.$a['id_user'].'">'.$a['userMail'].'</a>';
                        }
                        $aff   = implode(' , ', $tmp);
                        $islink = false;
                    }
                    // Type Media
                    if($field['fieldType'] == 'media') {
                        $i = 0;
                        $affmedia = '';
                        foreach($aff as $type=>$a) {
                            if($i > 0) $affmedia .= "<br />";
                            $i++;
                            $affmedia  .= '<b>'.$type.' ('.sizeof($a).')</b><br />';
                            $medias = array();
                            foreach($a as $aa) {
                                $medias[]  = '<a href="'.$aa['url'].'" target="_blank" title="'.$aa['url'].'">'.basename($aa['url']).'</a> ';
                            }
                            $affmedia .= implode(' , ', $medias);
                        }
                        $aff = $affmedia;

                        $islink = false;
                    }
                    // Type Category
                    if($field['fieldType'] == 'category') {
                        if(key($aff) == '0') {
                            foreach($aff as $a) {
                                $tmp[]  =  $a['categoryName'];
                            }
                            $aff    = implode(' , ', $tmp);
                        }else
                            $aff = $aff['categoryName'];
                    }
                    // Type table externe
                    if($field['fieldType'] == 'dbtable') {
                        $aff = print_r($aff, true);
                    }
                    // Type Content
                    if($field['fieldType'] == 'content') {
                        if(key($aff) == '0') {
                            foreach($aff as $a) {
                                $tmp[]  =  '<a href="data?id_content='.$a['id_content'].'" title="ID : '.$a['id_content'].'" >'.$a['contentName'].'</a>';
                            }
                            $aff    = implode(' , ', $tmp);
                        }else
                            $aff = $aff['id_content'].' - '.$aff['contentName'];
                    }
                }

                echo "<td>";
                if($islink) echo "<a href=\"".$link."\">".$aff."</a>";
                else echo $aff;
                echo "</td>";
            }
        }

    }
	
	?>

	<form method="post" action="index" id="listing">
		<input type="hidden" name="id_type"		value="<?php echo $id_type ?>" />
		<input type="hidden" name="language"	value="<?php echo $language ?>" />

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="10" class="icone"><i class="icon-remove icon-white"></i></th>
					<th width="20" class="icone order <?php if($filter['order'] == 'k_content.contentSee') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentSee&direction=<?php echo $dir ?>'"><span><i class="icon-eye-open icon-white"></i></span></th>
						
					<th width="20" class="icone"><i class="icon-tags icon-white"></i></th>
					<!--<th width="20" class="icone"><i class="icon-file icon-white"></i></th>-->
					<!--<th width="20" class="icone"><i class="icon-comment icon-white"></i></th>-->
					<th width="<?php echo 20 + (sizeof($lang) * 20) ?>"class="icone"><i class="icon-globe icon-white"></i></th>
					<th width="60" 	class="order <?php if($filter['order'] == 'k_content.id_content') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.id_content&direction=<?php echo $dir ?>'"><span>#</span></th>
					<th width="115" class="order <?php if($filter['order'] == 'k_content.contentDateCreation')  echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateCreation&direction=<?php echo $dir ?>'"><span><?php echo _('Created'); ?></span></th>
					<th width="115" class="order <?php if($filter['order'] == 'k_content.contentDateUpdate') 	echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentDateUpdate&direction=<?php echo $dir ?>'"><span><?php echo _('Updated'); ?></span></th>
						
					<?php if($cType['is_business']){ ?>
					<th width="200" class="order <?php if($filter['order'] == 'k_content.contentRef') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_content.contentRef&direction=<?php echo $dir ?>'"><span><?php echo _('Reference'); ?></span></th>
					<?php } ?>

					<th class="filter order <?php if($filter['order'] == 'k_contentdata.contentName') echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=k_contentdata.contentName&direction=<?php echo $dir ?>'"><span><?php echo _('Name'); ?></span></th>
                    <?php
                        if(is_array($cType['typeListLayout'])) {
                            foreach($cType['typeListLayout'] as $e) {
                                if(is_numeric($e['field'])) {
                                    $field	    = $app->apiLoad('field')->fieldGet(array('id_field' => $e['field']));
                                    $fieldbdd = 'k_content'.$_REQUEST['id_type'].'.field'.$e['field'];
                                }else {
                                    $field = array('fieldName' => $e['field']);
                                    $fieldbdd = 'k_content.'.$e['field'];
                                }
                    ?>
                        <th width="<?php echo $e['width']; ?>" class="order <?php if($filter['order'] == $fieldbdd) 	echo 'order'.$dir; ?>" onClick="document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&cf&order=<?php echo $fieldbdd; ?>&direction=<?php echo $dir ?>'">
                            <span><?php echo $field['fieldName']; ?></span>
                        </th>

                    <?php
                            }
                        }
                    ?>

				</tr>
			</thead>
			<tbody>
			<?php if(sizeof($content) == 0){ ?>
				<tr>
					<td colspan="10" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">
						<?php echo _('No data'); ?><br /><br />
						<a href="data?id_type=<?php echo $id_type ?>"><?php printf(_('Create a new %s'), $cType['typeName']) ?></a>
					</td>
				</tr>	
			<?php }else{
					$count = 0;
					foreach($content as $e){
						$count++; // count pour les labels
						view($app, $cType, $filter, $e, 0, $count);

					}
				}
			?>
			</tbody>
			<?php if(sizeof($content) > 0){ ?>
			<tfoot>
				<tr>
					<td><input type="checkbox" onchange="cbchange($(this));" class="chk" id="chk_remove_all" /></td>
					<td><input type="checkbox" onchange="cschange($(this));" class="chk" id="chk_see_all" /></td>
					<td colspan="5" height="25"><a href="#" onClick="apply();" class="btn btn-mini"><span><?php echo _('Apply changes'); ?></span></a></td>
                    <?php
                        $cs = $cType['is_business'] ? 2 : 1;
                        $cs += sizeof($cType['typeListLayout']);
                    ?>
                    <td colspan="<?php echo $cs; ?>" class="pagination" align="right"><?php
						echo 'Total: '.$total.' &nbsp; ';
						$app->pagination($total, $limit, $filter['offset'], 'index?cf&id_type='.$id_type.'&offset=%s');
					?></td>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
	</form>

<?php } ?></div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>

<script>

	function duplicate(id){
		if(confirm("DUPLIQUER ?")){
			document.location='index?id_type=<?php echo $_REQUEST['id_type'] ?>&duplicate='+id;
		}
	}

	function apply(){
		if(confirm("Confirmez-vous les changements sur la selection ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>
