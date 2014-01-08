<?php

class socialActivity extends social{

function __clone(){}
function socialActivity(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialActivityGet($opt){

	if($opt['debug']) $this->pre("OPTION", $opt);

	$dbMode = 'dbMulti';

	// GET notification
	if($opt['notification']){
		$join[] = "INNER JOIN k_socialnotification ON k_socialactivity.id_socialactivity = k_socialnotification.id_socialactivity";
		$cond[] = "k_socialnotification.id_user=".$opt['id_user'];
	}

	// GET id_user
	if(array_key_exists('id_user', $opt)){

		if(is_array($opt['id_user'])){
			$cond[] = "id_user IN(".implode(',', $opt['id_user']).")";
		}else
		if($opt['id_user'] > 0){
			$cond[] = "id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (ARRAY,NUMERIC)", "GIVEN", var_export($opt['user'], true));
			return array();
		}		

	}

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

	if($dbMode == 'dbMulti'){

		$group = "\nGROUP BY ".(($opt['groupby'] != NULL)
			? $opt['groupby']
			: "k_socialpost.id_socialpost");
		
		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "socialActivityDate DESC");

		$limit = "\nLIMIT ".(($opt['offset']  != '' && $opt['limit'] != '')
			? $opt['offset'].",".$opt['limit']
			: "0,50");

		if($opt['noLimit'] == true) unset($limit);
	}

	$field		= "k_socialactivity.*";
	$where		= is_array($cond) ? "\nWHERE\n".implode(" AND ", $cond) : NULL;
	$inner		= is_array($join) ? "\n".implode("\n", $join)."\n" : NULL;

	$activity	= $this->dbMulti("SELECT ".$field." FROM k_socialactivity ".$inner. $where . $__group__ . $order . $limit);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $activity);

	return $activity;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialActivitySet($opt){
if($opt['debug']) $this->pre("OPTION", $opt);

	$id_user	= $opt['id_user'];				if(intval($id_user) <= 0)				return false;
	$key		= $opt['socialActivityKey'];	if($key == NULL)						return false;
	$flag		= $opt['socialActivityFlag'];	if($flag == NULL && !$opt['remove'])	return false;
	$id			= $opt['socialActivityId'];
	$thread		= $opt['socialActivityThread'];


	# REMOVE
	#
	if($opt['remove']){
		$act = $this->dbOne("SELECT * FROM k_socialactivity WHERE socialActivityKey='".$key."' AND socialActivityId=".$id);

		if(intval($act['id_socialactivity']) > 0){
			$this->dbQuery("DELETE FROM k_socialactivity		WHERE id_socialactivity=".$act['id_socialactivity']);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			$this->dbQuery("DELETE FROM k_socialnotification	WHERE id_socialactivity=".$act['id_socialactivity']);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}
	}

	# ADD
	#
	else{

		$query = $this->dbInsert(array('k_socialactivity' => array(
			'id_user'			   => array('value' => $id_user),
			'socialActivityKey'	   => array('value' => $key),
			'socialActivityId'	   => array('value' => $id, 	 'null' => true),
			'socialActivityThread' => array('value' => $thread, 'null' => true),
			'socialActivityFlag'   => array('value' => strtoupper($flag)),
			'socialActivityDate'   => array('value' => date("Y-m-d H:i:s")),
		)));

		$this->dbQuery($query);
		$id_act = $this->db_insert_id;
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		// Hook
		$this->hookAction('socialActivitySet', $id_act);

		# Create NOTIFICATION
		#
		if($id_act > 0 && $opt['notification']){

			// Je donne dans les OPT la liste des personne qui recoivent la notification
			if(isset($opt['notificationUser'])){
				$usrs = is_array($opt['notificationUser']) ? $opt['notificationUser'] : array($opt['notificationUser']);
			}else

			// Utiliser les player de l'EVENT pour trouver les personnes a notifier
			if($opt['socialActivityKey'] == 'id_socialevent'){
				$usrs = $this->dbMulti("SELECT id_user FROM k_socialeventuser WHERE id_socialevent='".$id."' AND id_user != '".$id_user."'");
				$usrs = $this->dbKey($usrs, 'id_user');
			}else

			// Utiliser les player du CIRCLE pour trouver les personnes a notifier
			if($opt['socialActivityKey'] == 'id_socialcircle'){
				$usrs = $this->dbMulti("SELECT id_user FROM k_socialcircleuser WHERE id_socialcircle='".$id."' AND id_user != '".$id_user."'");
				$usrs = $this->dbKey($usrs, 'id_user');
			}else

			// Si non j'utilise les tables pour trouver les personne qui auront la notification
			{
				if($opt['socialActivityKey'] == 'id_socialpost'){
					$noti  = 'socialPostSubscribed';
					$table = 'k_socialpost';
					$thrd  = 'id_socialpostthread';
				}else
				if($opt['socialActivityKey'] == 'id_socialmessage'){
					$noti  = 'socialMessageSubscribed';
					$table = 'k_socialmessage';
					$thrd  = 'id_socialmessagethread';
				}

				// ?
				$item = $this->dbOne("SELECT ".$noti." FROM ".$table." WHERE ".$thrd."=".$thread." AND ".$noti." != ''");
				//$item = $this->dbOne("SELECT ".$noti." FROM ".$table." WHERE ".$thrd."=".$thread);
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);

				$usrs = json_decode($item[$noti], true);
				$usrs = is_array($usrs) ? $usrs : array(); // useless ?
			}

			// secu
			if(!is_array($usrs)) $usrs = array();

			// Lever une notification pour tous les SUBSCRIBED USER sauf MOI
			foreach($usrs as $u){
				if($id_user != $u) $tmp[] = "(".$id_act.",".$u.")";
			}

			if(sizeof($tmp) > 0){
				$this->dbQuery("INSERT INTO k_socialnotification (id_socialactivity, id_user) VALUES\n".implode(",\n", $tmp));
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialActivityRemove($opt){
	if($opt['debug']) $this->pre("OPTION", $opt);

	// Security
	$idn = $opt['id_socialactivity']; if(intval($idn) <= 0) return false;

	// Remove
	$this->dbQuery("DELETE FROM k_socialactivity     WHERE id_socialactivity=".$idn);
	$this->dbQuery("DELETE FROM k_socialnotification WHERE id_socialactivity=".$idn);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}

}