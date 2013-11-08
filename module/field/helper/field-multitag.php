<?php

	header('Content-type: application/json');

	$limit		= 50;
	$offset		= ($_GET['offset'] == NULL) ? 0 : $_GET['offset'] * $limit;
	$field		= $app->apiLoad('field')->fieldGet(array('id_field' => $_GET['id_field']));
	$param		= json_decode($field['fieldParam'], true);	

	if(intval($_GET['id_type'] > 0)){
		$is_content = true;
	}else
	if($_GET['id_type'] == 'user'){
		$is_user = true;
	}else
	if($_GET['id_type'] == 'dbtable'){
		$is_dbtable	= true;
	}
	
	
	# CONTENT
	#
	if($is_content){
		$api  = $app->apiLoad('content');
		$type = $app->apiLoad('type')->typeGet(array(
			'id_type' => $_GET['id_type']
		));

		$opt = array(
			'id_type'    => $type['id_type'],
			'raw'        => true,
			'debug'      => false,
			'limit'      => $limit,
			'offset'     => $offset,
			'search'     => array(
				array('searchField' => 'contentName', 'searchValue' => $_GET['q'], 'searchMode' => 'CT')
			)
		);

		if($type['is_gallery'] == '1') $opt['is_album'] = true;

		$rez = $api->contentGet($opt);

		$total = $api->total;

		foreach($rez as $e){
			$tmp = array(
				'id_content' 	=> $e['id_content'],
				'contentName'	=> $e['contentName']
			);

			if($type['is_gallery'] == 1 && !empty($e['contentAlbumParent'])){

				$albums  = explode(',', $e['contentAlbumParent']);
				$parents = $api->contentGet(array(
					'id_type'    => $type['id_type'],
					'id_content' => $albums,
					'is_album'   => true,
					'limit'      => 15
				));

				$p = $p_ = array();
				foreach($parents as $ps){
					$p_[$ps['id_content']] = trim($ps['contentName']);
				}

				foreach($albums as $a){
					$p[] = $p_[$a];
				}

				#echo $e['contentAlbumParent'];
				#$app->pre($p);


				$tmp['path'] = implode(' > ', $p);
				unset($p);
			}


			$m[] = $tmp;
		}
	}else

	# USER
	#
	if($is_user){
		$api	= $app->apiLoad('user');
		$opt	= array(
			'debug'		=> false,
			'limit'		=> $limit,
			'search'	=> $_GET['q'],
			'offset'	=> $offset
		);

		if(is_array($param['id_field']) && sizeof($param['id_field']) > 0){
			$opt['search'] = array();
			foreach($param['id_field'] as $e){
				$opt['search'][] = array('searchField' => 'field'.$e, 'searchValue' => $_GET['q'], 'searchMode' => 'CT');
			}
		}

		$rez = $api->userGet($opt);
		$total = $api->total;

		foreach($rez as $e){
			$tmp = array(
				'id_user' 	=> $e['id_user'],
				'userMail'	=> $e['userMail']
			);

			if(sizeof($param['id_field']) > 0){
				foreach($param['id_field'] as $moreField){
					$moreField = $app->apiLoad('field')->fieldGet(array('id_field' => $moreField));

					if($moreField['id_field'] > 0){
						$tmp['more'][] = $e['field'][$moreField['fieldKey']];
					}
				}
			}

			
			$m[] = $tmp;
		}
	}else
	
	# DBTABLE
	#
	if($is_dbtable){
		$param	= json_decode($field['fieldParam'], true);
		$where	= " WHERE ".$param['field']." LIKE '%".addslashes($_GET['q'])."%'";
		$lim	= " LIMIT ".$offset.','.$limit;

		if($param['where'] != '') $where .= " AND ".$param['where'];

		$rez	= $app->dbMulti("
			SELECT SQL_CALC_FOUND_ROWS ".$param['id']." AS dbtable_id, ".$param['field']." AS dbtable_view
			FROM ".$param['table'] . $where . $lim
		);

		foreach($rez as $e){
			$m[] = array(
				'dbtable_id' 	=> $e['dbtable_id'],
				'dbtable_view'	=> $e['dbtable_view']
			);
		}

		$total = $app->db_num_total;
	}

	$m = array(
		'result'	=> (is_array($m) ? $m : array()),
		'more'		=> ($total > ($offset + $limit)),
		'next'		=> intval($offset)+1
	);

	if($is_dbtable){
		$m['table'] = $param['table'];
		$m['id'] 	= $param['id'];
		$m['field']	= $param['field'];
	}


	if(isset($_GET['pre'])){
		$app->pre(var_export($m));
	}else{
		echo json_encode($m);
	}
