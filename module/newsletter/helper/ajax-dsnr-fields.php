<?php

	if ($_GET['item'] == 'content') {
		$fields = $app->apiLoad('field')->fieldGet(array(
			'id_type' => intval($_GET['id_type'])
		));

		$authorizedFields = array( // validation array
			'texte-line',
			'texte'
		);

		foreach ($fields as $k=>$f) {
			if (!in_array($f['fieldType'], $authorizedFields)) {
				unset($fields[$k]);
			}
			$fields[$k]['fieldName'] = utf8_encode($fields[$k]['fieldName']);
		}
		$fields = array_values($fields);

		echo json_encode($fields);
	}
