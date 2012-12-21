<?php

class socialMessage extends social{

function __clone(){}
function socialMessage(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gerer les options
	#

	$dbMode	= 'dbMulti';
	$sel[] 	= 'k_socialmessage.*';
	
	// GET id_socialmessage
	if(array_key_exists('id_socialmessage', $opt)){

		if(is_array($opt['id_socialmessage']) && sizeof($opt['id_socialmessage']) > 0){
			$cond[] = "k_socialmessage.id_socialmessage IN(".implode(', ', $opt['id_socialmessage']).")";
			
			if(!array_key_exists('order', $opt) && !array_key_exists('direction', $opt)){
				$opt['order']		= "FIND_IN_SET(k_socialmessage.id_socialmessage, '".implode(', ', $opt['id_socialmessage'])."')";
				$opt['direction']	= "DESC";
				
			}
		}else
		if(intval($opt['id_socialmessage']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_socialmessage.id_socialmessage=".$opt['id_socialmessage'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALMESSAGE (NUMERIC, ARRAY)", "GIVEN", var_export($opt['id_socialmessage'], true));
			return array();
		}

	}

	// GET mid_socialmessage
	if(array_key_exists('mid_socialmessage', $opt)){

		if(intval($opt['mid_socialmessage']) > 0){
			$cond[] = "k_socialmessage.mid_socialmessage=".$opt['mid_socialmessage'];
		}else{
			if($opt['debug']) $this->pre("ERROR: MID_SOCIALMESSAGE (NUMERIC > 0)", "GIVEN", var_export($opt['id_socialmessage'], true));
			return array();
		}

	}

	// GET: is_read
	if(array_key_exists('is_read', $opt)){
		if(is_bool($opt['is_read'])){
			$cond[] = "is_read = ".(($opt['is_read']) ? 1 : 0);
		}else{
			if($opt['debug']) $this->pre("ERROR: is_read (BOOLEAN)", "GIVEN", var_export($opt['is_read'], true));
			return array();
		}	
	}


	// GET id_user	
	if($opt['id_user'] > 0){

		$noID = $opt['id_user'];

		if($opt['writer']){
			$cond[] = "k_socialmessage.id_user=".$opt['id_user'];
			$cond[] = "k_socialmessage.mid_socialmessage=0";
		}else
		if($opt['reader']){
			$cond[] = "k_socialmessageuser.id_user=".$opt['id_user'];
			$join[] = "INNER JOIN k_socialmessageuser ON k_socialmessage.id_socialmessage = k_socialmessageuser.id_socialmessage";
			$sel[]  = "k_socialmessageuser.is_read";
		}else{
			$cond[] = "k_socialmessage.id_user=".$opt['id_user'];
		}
	}


	# Former les CONDITIONS
	#		
	if(sizeof($cond) > 0) 	$where	= "WHERE ".implode(" AND ", $cond);
	if(sizeof($join) > 0) 	$join	= "\n".implode("\n", $join)."\n";
							$select	= implode(', ', $sel);

	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		$order = ($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "k_socialmessage.id_socialmessage DESC";

		$order = "\nORDER BY ".$order;

		if($opt['offset'] != '' && $opt['limit']) $limit = "\nLIMIT ".$opt['offset'].",".$opt['limit'];
	}else{
		$flip = true;
	}


	# MESSAGE
	#
	$messages		= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS ".$select." FROM k_socialmessage\n" . $join . $where . $order . $limit);
	$this->total	= $this->db_num_total;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $messages);

	if(sizeof($messages) > 0){
		if($flip) $messages = array($messages);

		# JSON
		#		
		foreach($messages as $n => $e){
			$messages[$n]['socialMessageFlat']		= ($e['socialMessageFlat'] != '')		? json_decode($e['socialMessageFlat'], true)		: array();
			$messages[$n]['socialMessageThread']	= ($e['socialMessageThread'] != '')		? json_decode($e['socialMessageThread'], true)		: array();
			$messages[$n]['socialMessageOpenGraph']	= ($e['socialMessageOpenGraph'] != '')	? json_decode($e['socialMessageOpenGraph'], true)	: array();
			$messages[$n]['socialMessageRecipient']	= ($e['socialMessageRecipient'] != '')	? json_decode($e['socialMessageRecipient'], true)	: array();
		}

		# WITH USER
		if($opt['withAuthor']){
			foreach($messages as $n => $e){
				$id_users[] = $e['id_user'];
				$messages[$n]['user'] = NULL;
			}
			if(sizeof($id_users) > 0){
				$users = $this->apiLoad('user')->userGet(array(
					'id_user'	=> $id_users,
					'useMedia'	=> true
				));
				foreach($users as $u){
					$uids[$u['id_user']] = $u;
				}
				foreach($messages as $n => $e){
					$messages[$n]['author'] = $uids[$e['id_user']];
				}
			}
		}

		
		# RECIPIENT
		if($opt['withRecipient'] == true){
			foreach($messages as $n => $e){
				if(sizeof($e['socialMessageRecipient']) > 0){
					$users = $this->apiLoad('user')->userGet(array(
						'debug'		=> false,
						'id_user'	=> $e['socialMessageRecipient'],
						'useMedia'	=> true,
						'useField'	=> true,
					));

					$messages[$n]['socialMessageRecipient'] = $users;
				}
			}
		}

		# MEDIA TRANSLATION
		if($opt['human']){
			foreach($messages as $n => $m){
				$messageMedia = json_decode(stripslashes($p['socialMessageMedia']), true);
				if(sizeof($messageMedia) > 0){
					foreach($messageMedia as $e){
						$media[$e['type']][] = $this->mediaInfos($e['url']);
					}
					$messages[$n]['socialPostMedia'] = $media;
				}else{
					$messages[$n]['socialPostMedia'] = array();
				}
				unset($media);
			}		
		}

		if($flip) $messages = $messages[0];
	}

	return $messages;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageSet($opt){

	# NEW !
	#
	if($opt['id_socialmessage'] == NULL){
		$this->dbQuery("INSERT INTO k_socialmessage (socialMessageDate) VALUES (NOW())");
		$id_socialmessage = $this->db_insert_id;
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}else{
		$id_socialmessage = $opt['id_socialmessage'];
	}
	$this->id_socialmessage = $id_socialmessage;


	# REPLY or MY-SELF AS A THREAD
	#
	if(intval($opt['replyTo']) > 0 && intval($opt['thread']) > 0){
		$opt['core']['mid_socialmessage'] 	   = array('value' => $opt['replyTo']);
		$opt['core']['id_socialmessagethread'] = array('value' => $opt['thread']);
	}else{
		$opt['core']['id_socialmessagethread'] = array('value' => $id_socialmessage);
	}


	# Set VIEW = DATA (VIEW will be altered later if needed, do the job easily first :-)
	#
	if($opt['core']['socialMessageData']['value'] != ''){
		$opt['core']['socialMessageDataView'] = array('value' => $opt['core']['socialMessageData']['value']);
	}


	# CORE
	#
	$query = $this->dbUpdate(array('k_socialmessage' => $opt['core']))." WHERE id_socialmessage=".$id_socialmessage;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);


	# BUILD CACHE
	#
#	if(intval($opt['replyTo']) > 0 && intval($opt['thread']) > 0){
	$this->socialMessageBuild(array(
		'debug'						=> $opt['debug'],
		'id_socialmessagethread'	=> $opt['core']['id_socialmessagethread']['value']
	));
#	}


	# SEND TO ...
	#
	if(is_array($opt['sendTo']) && sizeof($opt['sendTo']) > 0){
		$addu[] = "(".$id_socialmessage.", ".$opt['core']['id_user']['value'].", 1)";

		foreach($opt['sendTo'] as $idu){
			$addu[] = "(".$id_socialmessage.", ".$idu.", 0)";
			$rcpt[]	= intval($idu);
		}

		$this->dbQuery("INSERT INTO k_socialmessageuser (id_socialmessage, id_user, is_read) VALUES ".implode(', ', $addu));
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery("UPDATE k_socialmessage SET socialMessageRecipient='".json_encode($rcpt)."' WHERE id_socialmessage=".$id_socialmessage);
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	# VIEW + OPENGRAPH
	#
	$this->apiLoad('socialTool')->socialToolExternal(array(
		'type' 	=> 'message',
		'id' 	=> $id_socialmessage
	));
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageRemove($opt){

	$id_socialmessage = $opt['id_socialmessage'];

	$message = $this->socialMessageGet(array(
		'id_socialmessage' => $id_socialmessage
	));

	if($message['id_socialmessage'] == NULL) return false;

	$del[]	= $message['id_socialmessage'];
	$del	= array_merge($del, $message['socialMessageFlat']);

	if(sizeof($del) > 0){
		$this->dbQuery("DELETE FROM k_socialmessage WHERE id_socialmessage IN(".implode(',', $del).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$this->dbQuery("DELETE FROM k_socialmessageuser WHERE id_socialmessage IN(".implode(',', $del).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	if(sizeof($message['socialMessageFlat']) == 0){
		$this->socialMessageBuild(array(
			'debug'						=> false,
			'id_socialmessagethread'	=> $message['id_socialmessagethread']
		));
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageBuild($opt){

	$id_socialmessagethread = $opt['id_socialmessagethread'];

	$flat 	= $this->socialMessageBuildFlat($id_socialmessagethread);
	$thread	= $this->socialMessageBuildThread($id_socialmessagethread, $id_socialmessagethread);

	$def	= array('k_socialmessage' => array(
		'socialMessageFlat'		=> array('value' => json_encode($flat)),
		'socialMessageThread'	=> array('value' => json_encode($thread))
	));

	$this->dbQuery($this->dbUpdate($def)." WHERE id_socialmessage=".$id_socialmessagethread);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageBuildFlat($starter){

	$all = $this->dbMulti("
		SELECT * FROM k_socialmessage
		WHERE id_socialmessagethread=".$starter." AND id_socialmessage != ".$starter."
		ORDER BY id_socialmessage"
	);

	return $this->dbKey($all, 'id_socialmessage', true);
	
	/*if(sizeof($all) == 0) return array();

	foreach($all as $e){
		$tmp[] = $e['id_socialmessage'];
	}

	return is_array($tmp) ? $tmp : array();*/
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageBuildThread($thread, $mid_socialmessage){

	$children = $this->dbMulti("
		SELECT id_socialmessage FROM k_socialmessage
		WHERE id_socialmessagethread=".$thread." AND mid_socialmessage=".$mid_socialmessage."
		ORDER BY id_socialmessage
	");
	
	if(sizeof($children) > 0){
		foreach($children as $c){
			$tmp[] = array(
				'i' => $c['id_socialmessage'],
				's' => $this->socialMessageBuildThread($thread, $c['id_socialmessage'])
			);
		}
		return $tmp;
	}else{
		return array();
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialMessageMarkRead($opt){

	if(intval($opt['id_socialmessage']) == 0)	return false;
	if(intval($opt['id_user']) == 0)			return false;

	$this->dbQuery(
		" UPDATE k_socialmessageuser".
		" SET is_read=".intval($opt['is_read']).
		" WHERE id_user=".$opt['id_user']." AND id_socialmessage=".$opt['id_socialmessage']
	);

#	$this->pre($this->db_query, $this->db_error);
}







































} ?>