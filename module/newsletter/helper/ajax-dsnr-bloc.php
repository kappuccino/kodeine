<?php

	$table = '@nlblocs';

	## GET BLOC & LAYOUT
	if (isset($_GET['id']) && is_numeric($_GET['id'])) {

		$bloc = $app->dbOne('SELECT * FROM `'. $table .'` WHERE id_bloc='.intval($_GET['id']));
		if (isset($_GET['layout'])) $layout = $app->dbOne('SELECT * FROM `@nlwrap` WHERE id_wrap='.intval($_GET['layout']));

		//$layouts = $app->dbMulti('SELECT * FROM `@nlwrap`');

		$bloc['connectors'] = json_decode( $bloc['connectors'], true );
		$bloc['contents'] = $bloc['contents'];

		if ($layout) {
			$layout['layout'] = $layout['layout'];
			$bloc['layout'] = $layout;
		}

		echo json_encode($bloc);
	}

	## SAVE BLOC
	if (isset($_POST['id']) && isset($_POST['contents']) && isset($_POST['position'])) {

		$bloc = $app->dbOne('SELECT * FROM `'. $table .'` WHERE id_bloc='.intval($_POST['id']));

		if ($_POST['name'] == $bloc['blocName']) { ## update
			$bloc = $app->dbQuery('UPDATE `'. $table .'` SET `contents` = "'. addslashes($_POST['contents']) .'" WHERE `'. $table .'`.`id_bloc`='. $_POST['id'] );
		} else { ## save as new
			$bloc = $app->dbQuery('INSERT INTO `'. $table .'` (`contents`, `blocName`, `position`) VALUES ("'. addslashes($_POST['contents']) .'", "'. $_POST['name'] .'", "'. $_POST['position'] .'") ');
		}

		echo  ($bloc == 1) ? json_encode(array('ok' => true)) : json_encode(array('ok' => false));
	}
