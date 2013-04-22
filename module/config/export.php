<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	require(USER.'/config/config.php');
	$conf = $config['mysql'] ?: $config['db'];

	if(isset($_GET['dw'])){
		$file = $conf['dump'].'/'.$_GET['dw'];
		$out  = file_get_contents($file);

		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: ".gmdate("D,d M YH:i:s")." GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Length: ".strlen($out));
		header("Content-type: plain/text");
		header("Content-Disposition: attachment; filename=\"".$_GET['dw']."\"" );
		echo $out;
		exit();

	}else
	if(isset($_GET['dump'])){
		$app->dbDump();
		$app->go('export');
	}else
	if(sizeof($_POST['del']) > 0){
		foreach($_POST['del'] as $d){
			$d = $conf['dump'].'/'.$d;
			if(file_exists($d)) unlink($d);
		}
		$app->go('export');
	}else
	if($_GET['reload'] != ''){
		$tmp = $conf['dump'].'/tmp-'.time().'.sql';
		$sql = $conf['dump'].'/'.$_GET['reload'];
		$sql = file_get_contents($sql);
		$sql = str_replace("`k_", "`x_", $sql);
		file_put_contents($tmp, $sql);

		#echo "<pre>";
		system("mysql --host=".$config['db']['host']." --user=".$config['db']['login']." --password=".$config['db']['passwor']." ".$config['db']['database']." < ".$tmp);
		#echo "</pre>";

		foreach($app->dbMulti("SHOW TABLES") as $e){
			$table = $e['Tables_in_'.$database];
			if(preg_match("#^k_#", $table)){
				$app->dbQuery("DROP TABLE `".$table."`");
			}
		}

		foreach($app->dbMulti("SHOW TABLES") as $e){
			$table = $e['Tables_in_'.$database];
			$name  = str_replace('x_', 'k_', $table);
			$app->dbQuery("RENAME TABLE `".$table."` TO `".$name."`");
		}

		unlink($tmp);
		$app->go('export?dumped');
	}

	$files = $app->fsFile($conf['dump'], 'export-*.sql');
	$files = is_array($files) ? $files : array();
	rsort($files);

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

<div id="app"><div class="wrapper">

	<?php if(isset($_GET['dump'])) echo "<pre>mysqldump... ended (".$r.")\n</pre>"; ?>

	<div class="alert"><?php
		echo _('Folder /user/dump is used to backup database exoprt files.');
	?></div>

    <form action="export" method="post" id="listing">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing mar-top-20">
        <thead>
            <tr>
                <th width="30" class="icone"><i class="icon-remove icon-white"></th>
                <th width="30" class="icone"><i class="icon-hdd icon-white"></th>
                <th width="200"><?php echo _('Date') ?></th>
                <th width="200"><?php echo _('Size') ?></th>
                <th><?php echo _('Infos') ?></th>
            </tr>
        </thead>
	    <tbody><?php

			if(sizeof($files) > 0){
				$chkdel = 0;
				foreach($files as $e){
					$chkdel++;

					$t += filesize($e);
					preg_match('#export-([0-9]{1,})(-(patch))?.sql#', basename($e), $m);
					$more = ($m[3] == 'patch') ? _('Automatic backup (patch)') : _('User export');

					echo '<tr>';
					echo '<td class="check-red"><input type="checkbox" name="del[]" value="'.basename($e).'" class="cb chk" id="chkdel'.$chkdel.'" /></td>';
					echo '<td><a href="export?dw='.basename($e).'"><i class="icon-hdd"></td>';
					echo '<td><a href="#" onClick="con("'.basename($e).'");\">'.date("Y-m-d H:i:s", $m[1]).'</a></td>';
					echo '<td>'.round((filesize($e) / 1024 / 1024), 2).' Mo</td>';
					echo '<td>'.$more.'</td>';
					echo '</tr>';
				}
			}else{
				echo
				'<tr>'.
					'<td colspan="5" style="padding:40px 0px 40px 0px; text-align:center; font-weight:bold">'._('No backup').'</td>'.
				"</tr>";
			}

		?></tbody>
		<?php if(sizeof($files) > 0){ ?>
            <tfoot>
	            <tr>
	                <td class="check-red"><input type="checkbox" class="chk" id="delall" onchange="cbchange($(this));" /></td>
	                <td colspan="2"><a href="#" onClick="apply();" class="btn btn-mini"><span><?php echo _('Remove selected files') ?></span></a></td>
	                <td><?php echo round(($t / 1024 / 1024), 2) ?> Mo</td>
	                <td></td>
	            </tr>
            </tfoot>
		<?php } ?>
	</table>
	</form>

    <div style="margin-top:20px;">
        <a href="export?dump" class="btn"><?php echo _('Backup the database now') ?></a>
    </div>

</div></div>

<?php include(COREINC.'/end.php'); ?>

<script>

    function con(f){
        if(confirm("<?php echo addslashes(_('Would you really reload this backup ?\n\nThis will empty your current database then load data from the selected file.\n\nUSE AT YOUR OWN RISK')) ?>")){
            document.location = 'export?reload='+f;
        }
    }

	function apply(){
		if(confirm("<?php echo addslashes(_('Would you really want to remove those backup?')) ?>")) $('#listing').submit();
	}

</script>

</body></html>