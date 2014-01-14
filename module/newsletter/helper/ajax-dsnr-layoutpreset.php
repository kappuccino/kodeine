<?php

	if (!empty($_POST['preset']) && !empty($_POST['id'])) {
		$table = '@nlwrap';
		$bloc = $app->dbQuery('UPDATE `'. $table .'` SET `preset` = "'. addslashes(json_encode($_POST['preset'])) .'" WHERE `'. $table .'`.`id_wrap`='. $_POST['id'] );
	}

?>