<?php
	/*if (isset($_POST['id_type']) && isset($_POST['field']) && isset($_POST['order']) && !isset($_POST['save'])) {

		## GET CONTENT
		$content = $app->apiLoad('content')->contentGet(array(
			'id_type' => intval($_POST['id_type']),
			'limit' => 1,
			'debug' => false,
			'order' => 'contentDateCreation',
			'useGroup' => false,
			'direction' => $_POST['order']
		));
		$content = $content[0];
		if (empty($content)) {
			echo json_encode(array('ok' => false, 'data' => "nodata"));
			exit(0);
		}

		## FIND FIELD
		foreach($content as $k=>$c) {
			if ($k == $_POST['field']) {
				echo (is_array($c)) ? json_encode(array('ok' => true, 'data' => $c))
					: json_encode(array('ok' => true, 'data' => utf8_encode($c)));
				exit(0);
			}
		}
		foreach($content['field'] as $k=>$c) { // not found in content, check fields
			if ($k == $_POST['field']) {
				echo (is_array($c)) ? json_encode(array('ok' => true, 'data' => $c))
					: json_encode(array('ok' => true, 'data' => utf8_encode($c)));
				exit(0);
			}
		}

		## NO DATA
		echo json_encode(array('ok' => false, 'data' => "nodata"));
		exit(0);

	}

	if ((bool)$_POST['save'] === true) {

		$table = '@nlblocs';
		$continue = false;

		## GET db cell and replace OR add connector data
		$bloc = $app->dbOne('SELECT * FROM `'. $table .'` WHERE id_bloc='.intval($_POST['bloc']));

		if (empty($bloc['connectors'])) { // no connectors registered, create from scratch
			$connectors = array($_POST['nodekey'] => $_POST['connector']);
			$continue = true;
		} else
		if (is_array(json_decode($bloc['connectors'], true))) {

			$con = json_decode($bloc['connectors'], true);
			foreach ($con as $k=>$c) { // check for our key
				if ($k == $_POST['nodekey']) { // if our nodekey exists
					$con[$k] = $_POST['connector'];
					$continue = true;
				}
			}

			if (!$continue) { // no nodekey found, add it
				$con[$_POST['nodekey']] = $_POST['connector'];
				$continue = true;
			}

			$connectors = $con;
		}

		if ($continue) {
			$bloc = $app->dbQuery('UPDATE `'. $table .'` SET `connectors` = "'. addslashes(json_encode($connectors)) .'" WHERE `'. $table .'`.`id_bloc`='. intval($_POST['bloc']) );

			if ($bloc > 0) {
				echo json_encode(array('ok' => true));
				exit(0);
			}

			echo json_encode(array('ok' => false));
			exit(0);
		}

	}*/


	if (isset($_POST['id_category'])) {

	}

	echo json_encode(array('ok' => false));
	exit(0);


