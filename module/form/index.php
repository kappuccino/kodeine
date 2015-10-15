<?php
	if(!defined('COREINC')) die('Direct access not allowed');

    // Filter
    if(isset($_GET['cf'])){
        $app->filterSet('formDump', $_GET);
        $filter = array_merge($app->filterGet('formDump'), $_GET);
    }else
    if(isset($_POST['filter'])){
        $app->filterSet('formDump', $_POST['filter']);
        $filter = array_merge($app->filterGet('formDump'), $_POST['filter']);
    }else{
        $filter = $app->filterGet('formDump');
    }


    if(sizeof($_POST['remove']) > 0){
        foreach($_POST['remove'] as $e){
            $app->apiLoad('formDump')->formDumpRemove($e);
        }
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

<div class="inject-subnav-right hide">
	<li><a onclick="filterToggle('content<?php echo $id_type ?>');" class="btn btn-small"><?php echo _('Display settings'); ?></a></li>
</div>

<div id="app">
	
	<div class="quickForm" style="display:<?php echo ($filter['open']) ? 'block' : 'none;' ?>;">
	<form action="index" method="post" class="form-horizontal">

		<input type="hidden" name="id_type"			value="<?php echo $id_type ?>" />
		<input type="hidden" name="filter[open]"	value="1" />
		<input type="hidden" name="filter[offset]"	value="0" />

        <label class="control-label"><?php echo _('Search'); ?></label>
        <input type="text" name="filter[q]" class="input-small" placeholder="" value="<?php echo $filter['q'] ?>" size="5" />

        <label class="control-label"><?php echo _('Limit'); ?></label>
        <input type="text" name="filter[limit]" class="input-small" placeholder="" value="<?php echo $filter['limit'] ?>" size="3" />

        <label class="control-label"><?php echo _('Type de formulaire'); ?></label>
        <select name="filter[formKey]">
            <option></option><?php
            $keys = $app->apiLoad('formDump')->formDumpKeyGet();
            foreach($keys as $e){
                if($e['formKey'] != '') echo "<option value=\"".$e['formKey']."\"".(($filter['formKey'] == $e['formKey']) ? ' selected' : '').">".$e['formKey']."</option>";
            }
            ?></select>

		<button class="btn btn-mini" type="submit"><?php echo _('Filter'); ?></button>
        <button class="btn btn-mini"><?php echo _('Cancel'); ?></button>
	</form>
	</div>	

	
	<?php

    function view($app, $filter, $e , $count=NULL){

        $link = "#"; // data?id_form=".$e['id_form'];
        $data = json_decode($e['json'], true);
        $onclick = " onclick=\"$('.raw".$e['id_form']."').toggle();\" ";
        $ee = print_r($e, true);
        $raw = '';
        if(is_array($data)) {
            $raw = "<table style=\"display:none;margin-top: 10px;width: 100%;\" class=\"raw".$e['id_form']."\">";
            foreach($data as $k=>$el) {
                $raw .= "<tr><td width=150>".$k."</td><td>".$el."</td></tr>";
            }
            $raw .= "</table>";
        }


        echo
            "<tr valign=\"top\">".
            "<td><input type=\"checkbox\" name=\"remove[]\" value=\"".$e['id_form']."\" class=\"chk cb\" id=\"chk_remove_".$count."\" /></td>".
            "<td class=\"dateTime\">".
                "<a href=\"".$link."\" ".$onclick.">".
                "<span class=\"date\">".$app->helperDate($e['formDate'], '%d.%m.%Y %Hh%M')."</span> ".
                "</a>".
            "</td>".
            "<td>".
                "<a href=\"".$link."\" ".$onclick.">".$e['formKey']."</a>".
            "</td>".
            "<td>".
                "<a href=\"mailto:".$e['email']."\">".$e['email']."</a>".
            "</td>".
            "<td>".
                "<a href=\"".$link."\" ".$onclick.">".$e['formTitle']."</a>".$raw.
            "</td>";


        echo "</tr>";

    }


    // Forms
        if($filter['order'] == '' && $filter['direction'] == '') {
            $filter['order'] = 'formDate';
            $filter['direction'] = 'DESC';
        }
		$opt		= array(
			'debug'	 			=> false,
			'limit'				=> $filter['limit'],
			'offset'			=> $filter['offset'],
			'search'			=> $filter['q'],
			'order'				=> $filter['order'],
			'direction'			=> $filter['direction']
		);

        if($filter['formKey'] != '')  $opt['formKey'] = $filter['formKey'];

		
		$content= $app->apiLoad('formDump')->formDumpGet($opt);
		$total	= $app->apiLoad('formDump')->total;
		$limit	= $app->apiLoad('formDump')->limit;


        $dir = ($filter['direction'] == 'ASC') ? 'DESC' : 'ASC';

	
	?>

	<form method="post" action="index" id="listing">
		<input type="hidden" name="id_type"		value="<?php echo $id_type ?>" />
		<input type="hidden" name="language"	value="<?php echo $language ?>" />

		<table border="0" cellpadding="0" cellspacing="0" class="listing">
			<thead>
				<tr>
					<th width="10" class="icone"><i class="icon-remove icon-white"></i></th>

                    <th width="100" 	class="order <?php if($filter['order'] == 'formDate') echo 'order'.$dir; ?>" onClick="document.location='index?order=formDate&cf&direction=<?php echo $dir ?>'"><span><?php echo _('Date'); ?></span></th>
                    <th width="160" 	class="order <?php if($filter['order'] == 'formKey') echo 'order'.$dir; ?>" onClick="document.location='index?order=formKey&cf&direction=<?php echo $dir ?>'"><span><?php echo _('Type'); ?></span></th>
                    <th width="200" 	class="order <?php if($filter['order'] == 'email') echo 'order'.$dir; ?>" onClick="document.location='index?order=email&cf&direction=<?php echo $dir ?>'"><span><?php echo _('Email'); ?></span></th>
                    <th 	class="order <?php if($filter['order'] == 'formTitle') echo 'order'.$dir; ?>" onClick="document.location='index?order=formTitle&cf&direction=<?php echo $dir ?>'"><span><?php echo _('Info'); ?></span></th>

				</tr>
			</thead>
			<tbody>
			<?php if(sizeof($content) == 0){ ?>
				<tr>
					<td colspan="10" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">
						<?php echo _('No data'); ?><br /><br />
					</td>
				</tr>	
			<?php }else{
					$count = 0;
					foreach($content as $e){
						$count++; // count pour les labels
						view($app, $filter, $e, $count);

					}
				}
			?>
			</tbody>
			<?php if(sizeof($content) > 0){ ?>
			<tfoot>
				<tr>
					<td><input type="checkbox" onchange="cbchange($(this));" class="chk" id="chk_remove_all" /></td>
					<td colspan="3" height="25"><a href="#" onClick="apply();" class="btn btn-mini"><span><?php echo _('Remove selected lines'); ?></span></a></td>
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
</div>

<?php include(COREINC.'/end.php'); ?>
<script src="../core/vendor/datatables/jquery.dataTables.js"></script>
<script src="../core/vendor/bootstrap/js/bootstrap-dropdown.js"></script>

<script>

	function apply(){
		if(confirm("Confirmez-vous les changements sur la selection ?")){
			$('#listing').submit();
		}
	}

</script>

</body></html>
