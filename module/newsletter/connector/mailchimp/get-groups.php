<?php
	$apiConnector	= $app->apiLoad('newsletterMailChimp');
	$lists	= $apiConnector->listInterestGroupings(array('id' => $_REQUEST['id']));
	if(sizeof($lists) == 0 || $lists['error'] != '') die('<br /><br />Aucun groupe associ&eacute;');
	
	//$app->pre($lists);
?>
	<br /><br />
	S&eacute;lectionner un groupe<br />
	<select name="listInterestGroupings[]" id="listInterestGroupings" multiple size="15" style="width:500px;height: 250px;">
<?php
	foreach($lists as $list) {
		
		foreach($list['groups'] as $group) {			
?>
		<option value="<?php echo $list['id']; ?>-<?php echo $group['name']; ?>">
			<?php echo $list['name'].' > '.$group['name'].' ('.$group['subscribers'].')'; ?>
		</option>
<?php
		}
	}				
?>
	</select>