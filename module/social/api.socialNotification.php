<?php

class socialNotification extends socialActivity{

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialNotificationGet($opt){
if($opt['debug']) $this->pre("OPTION", $opt);

	$id_user	= $opt['id_user']; if(intval($id_user) <= 0) return array();
	$dbMode		= 'dbMulti';
	
	// GET: socialActivityKey
	if(array_key_exists('socialActivityKey', $opt)){
		if(is_string($opt['socialActivityKey'])){
			$cond[] = "socialActivityKey = '".$opt['socialActivityKey']."'";
		}else
		if(is_array($opt['socialActivityKey'])){
			$cond[] = "socialActivityKey IN ('".implode("', '", $opt['socialActivityKey'])."')";
		}else{
			if($opt['debug']) $this->pre("ERROR: socialActivityKey STRING, ARRAY", "GIVEN", var_export($opt['socialActivityKey'], true));
			return array();
		}	
	}

	// GET: socialActivityKey
	if(array_key_exists('socialActivityId', $opt)){
		if(is_string($opt['socialActivityId'])){
			$cond[] = "socialActivityId = '".$opt['socialActivityId']."'";
		}else
		if(is_array($opt['socialActivityId'])){
			$cond[] = "socialActivityId IN ('".implode("', '", $opt['socialActivityId'])."')";
		}else{
			if($opt['debug']) $this->pre("ERROR: socialActivityId STRING, ARRAY", "GIVEN", var_export($opt['socialActivityId'], true));
			return array();
		}
	}

	// GET: Period
	if(array_key_exists('period', $opt)){
		if(isset($opt['period']['from']) && isset($opt['period']['to'])){
			$from	= date("Y-m-d", strtotime($opt['period']['from'])).	' 00:00:00';
			$to		= date("Y-m-d", strtotime($opt['period']['to'])).	' 23:59:59';

			$cond[]	= "(socialActivityDate >= '".$from."' AND socialActivityDate <= '".$to."')";
		}else{
			if($opt['debug']) $this->pre("ERROR: period (ARRAY(from,to)", "GIVEN", var_export($opt['period'], true));
			return array();
		}	
	}

	// GET: socialNotificationView
	if(array_key_exists('socialNotificationView', $opt)){
		if(is_bool($opt['socialNotificationView'])){
			$cond[] = "socialNotificationView = ".(($opt['socialNotificationView']) ? 1 : 0);
		}else{
			if($opt['debug']) $this->pre("ERROR: socialNotificationView (BOOLEAN)", "GIVEN", var_export($opt['socialNotificationView'], true));
			return array();
		}	
	}


	# Search (version simplifie)
	#
	if(is_array($opt['search'])){

		$field = $this->apiLoad('field')->fieldGet(array(
			'socialActivity' => true
		));
		
		foreach($field as $f){
			$fieldKey[$f['fieldKey']]= $f;
		}

		foreach($opt['search'] as $e){
			if($e['searchField'] > 0){
				$tmp[] = $this->dbMatch("k_socialactivity.field".$e['searchField'],	$e['searchValue'], $e['searchMode']);
			}else
			if($fieldKey[$e['searchField']]['id_field'] != NULL){
				$tmp[] = $this->dbMatch("k_socialactivity.field".$fieldKey[$e['searchField']]['id_field'], 	$e['searchValue'], $e['searchMode']);
			}
		}
		if(sizeof($tmp) > 0) $cond[] = "(".implode(' '.$searchLink.' ', $tmp).")";
	}


	# LIMITATIONS & ORDER
	#
	if($dbMode == 'dbMulti'){
		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "socialActivityDate DESC")."\n";

		$limit = "\nLIMIT ".(($opt['offset'] >= 0 && $opt['limit'] > 0)
			? $opt['offset'].",".$opt['limit']
			: "0,50")."\n";

		if($opt['noLimit']) unset($limit);
		if($opt['noOrder']) unset($order);
	}else{
		$flip = true;
	}


	# EXECUTION
	#
	$cond[]	= "k_socialnotification.id_user=".$id_user;
	$where 	= "\t".implode("\n\tAND ", $cond);
	$data	= $this->$dbMode(
		"SELECT * FROM k_socialnotification\n".
		"INNER JOIN k_socialactivity ON k_socialnotification.id_socialactivity = k_socialactivity.id_socialactivity\n\n".
		"WHERE ".$where . $order . $limit
	);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if(sizeof($data) == 0) return array();


	# WITH USER
	#
	if($opt['withUser']){
		foreach($data as $n => $e){
			if(intval($e['id_user']) > 0){
				$id_users[] = $e['id_user'];
				$data[$n]['user'] = array();
			}
		}

		// Simplifier
		$id_users = array_values(array_unique($id_users));

		if(sizeof($id_users) > 0){
			$users = $this->apiLoad('user')->userGet(array(
				'debug'		=> false,
				'useMedia'	=> true,
				'noLimit'	=> true,
				'id_user'	=> $id_users,
			));

			foreach($users as $u){
				$uids[$u['id_user']] = $u;
			}
			foreach($data as $n => $e){
				$data[$n]['user'] = $uids[$e['id_user']];
			}
		}
	}
	
	# WITH POST
	#
	if($opt['withPost']){
		foreach($data as $n => $e){
			if($e['socialActivityKey'] == 'id_socialpost' && intval($e['socialActivityId']) > 0){
				$id_socialpost[]		= $e['socialActivityId'];
				$data[$n]['socialPost'] = NULL;
			}
		}

		if(sizeof($id_socialpost) > 0){
			$posts = $this->apiLoad('socialPost')->socialPostGet(array(
				'id_socialpost' => array_unique($id_socialpost),
				'withUser'      => $opt['withUser'],
				'noLimit'       => true
			));
			foreach($posts as $p){
				$spids[$p['id_socialpost']] = $p;
			}
			foreach($data as $n => $e){
				$data[$n]['socialPost'] = $spids[$e['socialActivityId']];
			}
		}
	}
	
	# WITH CIRCLE
	#
	if($opt['withCircle']){
		foreach($data as $n => $e){
			if($e['socialActivityKey'] == 'id_socialcircle' && intval($e['socialActivityId']) > 0){
				$id_socialcircle[] = $e['socialActivityId'];
				$data[$n]['socialCircle'] = NULL;
			}
		}

		if(sizeof($id_socialcircle) > 0){
			$circles = $this->apiLoad('socialCircle')->socialCircleGet(array(
				'id_socialcircle' => $id_socialcircle
			));
			foreach($circles as $c){
				$scids[$c['id_socialcircle']] = $c;
			}
			foreach($data as $n => $e){
				$data[$n]['socialCircle'] = $scids[$e['socialActivityId']];
			}
		}
	}

	# WITH CIRCLE
	#
	if($opt['withEvent']){
		foreach($data as $n => $e){
			if($e['socialActivityKey'] == 'id_socialevent' && intval($e['socialActivityId']) > 0){
				$id_socialevent[] = $e['socialActivityId'];
				$data[$n]['socialEvent'] = NULL;
			}
		}

		if(sizeof($id_socialevent) > 0){
			$events = $this->apiLoad('socialEvent')->socialEventGet(array(
				'id_socialcircle' => $id_socialevent
			));
			foreach($events as $c){
				$seids[$c['id_socialevent']] = $c;
			}
			foreach($data as $n => $e){
				$data[$n]['socialEvent'] = $seids[$e['socialActivityId']];
			}
		}
	}

	return $data;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialNotificationRemove($opt){
if($opt['debug']) $this->pre("OPTION", $opt);

	// Security
	$idu = $opt['id_user'];				if(intval($idu) <= 0) return false;
	$idn = $opt['id_socialactivity'];	if(intval($idn) <= 0) return false;

	// Remove
	$this->dbQuery("DELETE FROM k_socialnotification WHERE id_socialactivity=".$idn." AND id_user=".$idu);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialNotificationView($opt){

	if(!is_array($opt['socialActivityId']) OR sizeof($opt['socialActivityId']) == 0) return false;

	$data = $this->dbMulti(
		"SELECT * FROM k_socialactivity
		INNER JOIN k_socialnotification ON k_socialactivity.id_socialactivity = k_socialnotification.id_socialactivity
		WHERE	
			k_socialnotification.id_user	= ".$opt['id_user']." AND
			socialNotificationView			= 0 AND 
			socialActivityKey 				= 'id_socialpost' AND
			socialActivityId				IN(".implode(', ', $opt['socialActivityId']).")"
	);

	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
	if(sizeof($data) > 0){
		$this->dbQuery("UPDATE k_socialnotification SET socialNotificationView=1 WHERE
			id_user= ".$opt['id_user']." AND id_socialactivity IN(".implode(',', $this->dbKey($data, 'id_socialactivity', true)).")"
		);

		if($opt['debug']) $this->pre($this->db_query, $this->db_error);	
		return true;
	}
	
	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialNotificationViewAll($opt){
if($opt['debug']) $this->pre("OPTION", $opt);

	// Security
	$idu = $opt['id_user'];				if(intval($idu) <= 0) return false;
	$idn = $opt['id_socialactivity'];

	$cond[]	= "id_user=".$idu;

	if($opt['fullClean']){
		$d = true; // ne pas supprimer
	}else
	if(is_array($idn) && sizeof($idn) > 0){
		$cond[] = 'id_socialactivity IN('.implode(', ', $idn).')';
	}else
	if(is_integer($idn)){
		$cond[] = 'id_socialactivity='.$idn;
	}else{
		return array();
	}

	$where = "WHERE ".implode(' AND ', $cond);

	// Remove
	$this->dbQuery("UPDATE k_socialnotification SET socialNotificationView=1 ".$where);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}

}