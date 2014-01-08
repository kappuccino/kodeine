<?php

class socialPost extends social{

function __clone(){}
function socialPost(){}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='socialPostGet() @='.json_encode($opt));
	$dbMode = 'dbMulti';
	$cond[] = "k_socialpost.socialPostHide=0";

	// GET mid_socialpost
	if(array_key_exists('mid_socialpost', $opt) && $opt['mid_socialpost'] != '*'){
		if(intval($opt['mid_socialpost']) >= 0){
			$cond[] = "k_socialpost.mid_socialpost=".$opt['mid_socialpost'];
		}else{
			if($opt['debug']) $this->pre("ERROR: MID_SOCIALPOST (NUMERIC)", "GIVEN", var_export($opt['mid_socialpost'], true));
			return array();
		}
	}else
	if(!array_key_exists('id_socialpost', $opt) && $opt['mid_socialpost'] != '*'){
		$cond[] = "k_socialpost.mid_socialpost=0";
	}

	// GET id_user
	if(array_key_exists('id_user', $opt)){
		if(is_array($opt['id_user'])){
			$cond[] = "k_socialpost.id_user IN(".implode(',', $opt['id_user']).")";
		}else
		if(intval($opt['id_user']) > 0){
			$cond[] = "k_socialpost.id_user=".$opt['id_user'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_USER (ARRAY, NUMERIC)", "GIVEN", var_export($opt['id_user'], true));
			return array();
		}
	}

	// GET id_content
	if(array_key_exists('id_content', $opt)){
		if(intval($opt['id_content']) > 0){
			$cond[] = "k_socialpost.id_content=".$opt['id_content'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_CONTENT (NUMERIC)", "GIVEN", var_export($opt['id_content'], true));
			return array();
		}
	}

	// GET id_socialpost
	if(array_key_exists('id_socialpost', $opt)){
		if(is_array($opt['id_socialpost'])){
			$cond[] = "k_socialpost.id_socialpost IN(".implode(',', $opt['id_socialpost']).")";
		}else
		if(intval($opt['id_socialpost']) > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_socialpost.id_socialpost=".$opt['id_socialpost'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALPOST (ARRAY, NUMERIC)", "GIVEN", var_export($opt['id_socialpost'], true));
			return array();
		}
	}

	// GET id_socialforum
	if(array_key_exists('id_socialforum', $opt)){
		$join[] = "INNER JOIN k_socialpostforum ON k_socialpost.id_socialpost = k_socialpostforum.id_socialpost";

		if(is_array($opt['id_socialforum'])){
			$cond[] = "k_socialpostforum.id_socialforum IN(".implode(',', $opt['id_socialforum']).")";
		}else
		if($opt['id_socialforum'] > 0){
			$cond[] = "k_socialpostforum.id_socialforum=".$opt['id_socialforum'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALFORUM (ARRAY,NUMERIC)", "GIVEN", var_export($opt['id_socialforum'], true));
			return array();
		}
	}

	// GET id_socialevent
	if(array_key_exists('id_socialevent', $opt)){
		$join[] = "INNER JOIN k_socialpostevent ON k_socialpost.id_socialpost = k_socialpostevent.id_socialpost";

		if(is_array($opt['id_socialevent'])){
			$cond[] = "k_socialpostevent.id_socialevent IN(".implode(',', $opt['id_socialevent']).")";
		}else
		if($opt['id_socialevent'] > 0){
			$cond[] = "k_socialpostevent.id_socialevent=".$opt['id_socialevent'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALEVENT (ARRAY,NUMERIC)", "GIVEN", var_export($opt['id_socialevent'], true));
			return array();
		}
	}

	// GET id_socialcircle
	if(array_key_exists('id_socialcircle', $opt)){
		$join[] = "INNER JOIN k_socialpostcircle ON k_socialpost.id_socialpost = k_socialpostcircle.id_socialpost";

		if(is_array($opt['id_socialcircle'])){
			$cond[] = "k_socialpostcircle.id_socialcircle IN(".implode(',', $opt['id_socialcircle']).")";
		}else
		if($opt['id_socialcircle'] > 0){
			$cond[] = "k_socialpostcircle.id_socialcircle=".$opt['id_socialcircle'];
		}else{
			if($opt['debug']) $this->pre("ERROR: ID_SOCIALCIRCLE (ARRAY,NUMERIC)", "GIVEN", var_export($opt['id_socialcircle'], true));
			return array();
		}
	}

	// GET is_activity
	if(array_key_exists('is_activity', $opt)){
		if(intval($opt['is_activity']) == 1 OR intval($opt['is_activity']) == 0){
			$cond[] = "k_socialpost.is_activity=".intval($opt['is_activity']);
		}else{
			if($opt['debug']) $this->pre("ERROR: is_activity (NUMERIC:0|1)", "GIVEN", var_export($opt['is_activity'], true));
			return array();
		}
	}
	
	// GET related
	if(array_key_exists('related', $opt)){
		$join[] = "INNER JOIN k_socialpostuser ON k_socialpost.id_socialpost = k_socialpostuser.id_socialpost";
	#	$cond[] = "k_socialpostuser.id_user=".$opt['id_user'];

	#	$opt['groupby'] = 'k_socialpostuser.id_socialpost';
	}

	# Former les CONDITIONS
	#
//	if(sizeof($or)	 > 0) $or	 = " OR (\n\t".implode("\n\tAND\n\t", $or)."\n)";
	if(sizeof($cond) > 0) $where = "\nWHERE\n(\n\t".implode("\n\tAND\n\t", $cond)."\n)".$or."\n";
	if(sizeof($join) > 0) $join	 = "\n".implode("\n", $join)."\n";

	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){

		$group = "\nGROUP BY ".(($opt['groupby'] != NULL)
			? $opt['groupby']
			: "k_socialpost.id_socialpost");

		$order = "\nORDER BY ".(($opt['order'] != '' && $opt['direction'] != '')
			? $opt['order']." ".$opt['direction']
			: "k_socialpost.id_socialpost DESC");

		$limit = "\nLIMIT ".(($opt['offset'] >= 0 && $opt['limit'] > 0)
			? $opt['offset'].",".$opt['limit']
			: "0,50");

		if($opt['noLimit'] == true) unset($limit);
	}else{
		$flip = true;
	}

	# POST
	#
	$select			= ($select == '')	? 'k_socialpost.*' : $select;
	$from			= ($from == '')		? 'k_socialpost'   : $from; 
	$posts 			= $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS ".$select." FROM ".$from."\n" . $join . $where . $group . $order . $limit);
	$this->total	= $this->db_num_total;

	if($opt['debug']) $this->pre("[QUERY]", $this->db_query, "ERROR", $this->db_error, "POSTS", $posts);

	if(sizeof($posts) > 0){
		if($dbMode == 'dbOne') $posts = array($posts);

		# JSON
		foreach($posts as $n => $e){
			$posts[$n]['socialPostFlat']			= ($e['socialPostFlat'] != '')			? json_decode($e['socialPostFlat'], true)			: array();
			$posts[$n]['socialPostThread']			= ($e['socialPostThread'] != '')		? json_decode($e['socialPostThread'], true)			: array();
			$posts[$n]['socialPostSubscribed']		= ($e['socialPostSubscribed'] != '') 	? json_decode($e['socialPostSubscribed'], true)		: array();
			$posts[$n]['socialPostOpenGraph']		= ($e['socialPostOpenGraph'] != '') 	? json_decode($e['socialPostOpenGraph'], true)		: array();
			$posts[$n]['socialPostCircle']			= ($e['socialPostCircle'] != '') 		? json_decode($e['socialPostCircle'], true)			: array();
			$posts[$n]['socialPostForum']			= ($e['socialPostForum'] != '') 		? json_decode($e['socialPostForum'], true)			: array();
			$posts[$n]['socialPostEvent']			= ($e['socialPostEvent'] != '') 		? json_decode($e['socialPostEvent'], true)			: array();

			$posts[$n]['socialPostRatePlusUser']	= ($e['socialPostRatePlusUser'] != '') 	? json_decode($e['socialPostRatePlusUser'], true)	: array();
			$posts[$n]['socialPostRateMinusUser']	= ($e['socialPostRateMinusUser'] != '') ? json_decode($e['socialPostRateMinusUser'], true)	: array();

			$posts[$n]['socialPostDataParam']		= ($e['socialPostDataParam'] != '') 	? json_decode($e['socialPostDataParam'], true)		: array();

			$posts[$n]['socialPostDataVal']		    = (strpos($e['socialPostDataVal'], '[') !== false)
					? json_decode($e['socialPostDataVal'], true)
					: $e['socialPostDataVal'];

		}


		# WITH USER
		if($opt['withUser']){
			foreach($posts as $n => $e){
				if(intval($e['id_user']) > 0){
					$id_users[] = $e['id_user'];
					$posts[$n]['user'] = NULL;
				}
			}
			if(sizeof($id_users) > 0){
				$users = $this->apiLoad('user')->userGet(array(
					'id_user'	=> $id_users,
					'useMedia'	=> true
				));
				foreach($users as $u){
					$uids[$u['id_user']] = $u;
				}
				foreach($posts as $n => $e){
					if(intval($e['id_user']) > 0) $posts[$n]['user'] = $uids[$e['id_user']];
				}
			}
		}


		# MEDIA TRANSLATION
		if($opt['withMedia']){
			foreach($posts as $n => $p){
				$postMedia = json_decode(stripslashes($p['socialPostMedia']), true);
				if(sizeof($postMedia) > 0){
					foreach($postMedia as $e){
						$media[$e['type']][] = $this->mediaInfos($e['url']);
					}
					$posts[$n]['socialPostMedia'] = $media;
				}else{
					$posts[$n]['socialPostMedia'] = array();
				}
				unset($postMedia, $media);
			}
		}

		if($flip) $posts = $posts[0];

		if($opt['debug']) $this->pre("[FORMAT]", $posts);
	}

	if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);
	
	return $posts;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostSet($opt){

	if($opt['debug']) $this->pre($opt);

	# NEW !
	#
	if($opt['id_socialpost'] == NULL){
		$this->dbQuery("INSERT INTO k_socialpost (socialPostDate, socialPostDateLast) VALUES (NOW(), NOW())");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		$id_socialpost = $this->db_insert_id;
	}else{
		$id_socialpost = $opt['id_socialpost'];
	}
	$this->id_socialpost	= $id_socialpost;
	$id_user				= $opt['core']['id_user']['value'];

	# REPLY or MY-SELF "AS A THREAD"
	#
	if(intval($opt['replyTo']) > 0 && intval($opt['thread']) > 0){
		$opt['core']['mid_socialpost'] 		= array('value' => $opt['replyTo']);
		$opt['core']['id_socialpostthread'] = array('value' => $opt['thread']);
		
		$flag = 'REPLY';
	}else{
		$flag = 'POST';

		$opt['core']['id_socialpostthread'] = array('value' => $id_socialpost);
	}

	# Set VIEW = DATA (VIEW will be altered later if needed, do the job easily first :-)
	#
	if($opt['core']['socialPostData']['value'] != ''){
		$opt['core']['socialPostDataView'] = array('value' => $opt['core']['socialPostData']['value']);
	}

	# CORE
	#
	$query = $this->dbUpdate(array('k_socialpost' => $opt['core']))." WHERE id_socialpost=".$id_socialpost;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	# BUILD CACHE
	#
	$this->socialPostBuild($opt['core']['id_socialpostthread']['value']);

	# FORUM
	#
	$this->dbQuery("DELETE FROM k_socialpostforum WHERE id_socialpost=".$id_socialpost);
	if(is_array($opt['forum']) && sizeof($opt['forum']) > 0){
		$forums = $this->apiLoad('socialForum')->socialForumGet(array(
			'id_socialforum'	=> $opt['forum']
		));

		$chain = array();	
		foreach($forums as $e){
			$chain = array_merge($chain, $e['socialForumParent'], array($e['id_socialforum']));
		}

		if(sizeof($chain) > 0){
			foreach(array_values(array_flip(array_flip($chain))) as $id_socialforum){
				$addf[] = "(".$id_socialforum.",".$id_socialpost.")";
				$idfs[] = intval($id_socialforum);
			}

			$this->dbQuery("INSERT IGNORE INTO k_socialpostforum (id_socialforum,id_socialpost) VALUES ".implode(',', $addf));
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			$this->dbQuery("UPDATE k_socialpost SET socialPostForum='".json_encode($idfs)."' WHERE id_socialpost=".$id_socialpost);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			if($flag == 'POST'){
				$this->dbQuery("UPDATE k_socialforum SET socialForumPostCount=socialForumPostCount+1 WHERE id_socialforum IN(".implode(',', $idfs).")");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}
	}

	# CIRCLE
	#
	$this->dbQuery("DELETE FROM k_socialpostcircle WHERE id_socialpost=".$id_socialpost);
	if(is_array($opt['circle']) && sizeof($opt['circle']) > 0){
		foreach($opt['circle'] as $idc){
			if($idc > 0){
				$addc[] = "(".$idc.",".$id_socialpost.")";
				$idcs[] = intval($idc);
			}
		}
		if(sizeof($addc) > 0){
			$this->dbQuery("INSERT INTO k_socialpostcircle (id_socialcircle,id_socialpost) VALUES ".implode(',', $addc));
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			$this->dbQuery("UPDATE k_socialpost SET socialPostCircle='".json_encode($idcs)."' WHERE id_socialpost=".$id_socialpost);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			if($flag == 'POST'){
				$this->dbQuery("UPDATE k_socialcircle SET socialCirclePostCount=socialCirclePostCount+1 WHERE id_socialcircle IN(".implode(',', $idcs).")");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}
	}

	# EVENT
	#
	$this->dbQuery("DELETE FROM k_socialpostevent WHERE id_socialpost=".$id_socialpost);
	if(is_array($opt['event']) && sizeof($opt['event']) > 0){
		foreach($opt['event'] as $idc){
			if($idc > 0){
				$addc[] = "(".$idc.",".$id_socialpost.")";
				$idcs[] = intval($idc);
			}
		}
		if(sizeof($addc) > 0){
			$this->dbQuery("INSERT INTO k_socialpostevent (id_socialevent,id_socialpost) VALUES ".implode(',', $addc));
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			
			$this->dbQuery("UPDATE k_socialpost SET socialPostEvent='".json_encode($idcs)."' WHERE id_socialpost=".$id_socialpost);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

			if($flag == 'POST'){
				$this->dbQuery("UPDATE k_socialevent SET socialEventPostCount=socialEventPostCount+1 WHERE id_socialevent IN(".implode(',', $idcs).")");
				if($opt['debug']) $this->pre($this->db_query, $this->db_error);
			}
		}
	}

	# VIEW + OPENGRAPH
	#
	$this->apiLoad('socialTool')->socialToolExternal(array(
		'type' 	=> 'post',
		'id' 	=> $id_socialpost
	));

	# ACTIVITY + NOTIFICATION
	#
	$this->apiLoad('socialActivity')->socialActivitySet(array(
		'debug'					=> false,
		'id_user'				=> $id_user,
		'notification'			=> (($flag == 'POST') ? false : true),

		'socialActivityKey'		=> 'id_socialpost',
		'socialActivityId'		=> $this->id_socialpost,
		'socialActivityThread'	=> $opt['core']['id_socialpostthread']['value'],
		'socialActivityFlag'	=> strtoupper($flag)
	));
	
	# SANDBOXING
	#
	$this->apiLoad('socialSandbox')->socialSandboxPush(array(
		'debug'				=> false,
		'socialSandboxType'	=> 'id_socialpost',
		'socialSandboxId'	=> $opt['core']['id_socialpostthread']['value']
	));
	
	# UPDATE USER-CACHE
	#
	$this->socialPostUserUpdate(array(
		'debug'			=> false,
		'id_socialpost'	=> $id_socialpost,
		'id_user'		=> $id_user,
		'reply'			=> (($flag == 'POST') ? false : true)
	));
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostHide($opt){

	$id_socialpost = $opt['id_socialpost'];

	$post = $this->socialPostGet(array(
		'id_socialpost' => $id_socialpost
	));

	if($post['id_socialpost'] == NULL) return false;

	$del[] = $post['id_socialpost'];
	$sub[] = $post['id_user'];

	if($post['id_socialpostthread'] > 0){
		$thread = $this->socialPostGet(array(
			'id_socialpost' => $post['id_socialpostthread']
		));

		$sub = array_values(array_unique(array_merge($sub, $thread['socialPostSubscribed'])));
	}

	// Decrementer les CERCLES ou je me trouvais
	if(count($post['socialPostCircle']) > 0){
		$this->dbQuery("UPDATE k_socialcircle SET socialCirclePostCount=socialCirclePostCount-1 WHERE id_socialcircle IN(".implode(',', $post['socialPostCircle']).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	// Decrementer les FORUMS ou je me trouvais
	if(count($post['socialPostForum']) > 0){
		$this->dbQuery("UPDATE k_socialforum SET socialForumPostCount=socialForumPostCount-1 WHERE id_socialforum IN(".implode(',', $post['socialPostForum']).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	// Decrementer les EVENTS ou je me trouvais
	if(count($post['socialPostEvent']) > 0){
		$this->dbQuery("UPDATE k_socialforum SET socialEventPostCount=socialEventPostCount-1 WHERE id_socialforum IN(".implode(',', $post['socialPostEvent']).")");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	// Supprimer les ACTIVITY y faisant référence
	$activities = $this->apiLoad('socialActivity')->socialActivityGet(array(
		'socialActivityKey' => 'id_socialpost',
		'socialActivityId'  => $opt['id_socialpost']
	));
	if(count($activities) > 0){
		foreach($activities as $e){
			$this->apiLoad('socialActivity')->socialActivityRemove(array(
				'id_socialactivity' => $e['id_socialactivity']
			));
		}
	}

	// Killer les ENFANTS
	if($post['socialPostFlat'] != '' && $opt['delete']){
		$del = array_merge($del, $post['socialPostFlat']);
	}

	if($opt['delete']){
		$this->dbQuery("DELETE FROM k_socialpost 		WHERE id_socialpost IN(".implode(',', $del).")");
		$this->dbQuery("DELETE FROM k_socialpostcircle 	WHERE id_socialpost IN(".implode(',', $del).")");
		$this->dbQuery("DELETE FROM k_socialpostforum 	WHERE id_socialpost IN(".implode(',', $del).")");
		$this->dbQuery("DELETE FROM k_socialpostevent 	WHERE id_socialpost IN(".implode(',', $del).")");
		$this->dbQuery("DELETE FROM k_socialactivity 	WHERE socialActivityThread IN(".implode(',', $del).") AND socialActivityKey='id_socialpost'");
		$this->dbQuery("DELETE FROM k_socialsandbox 	WHERE socialSandboxId IN(".implode(',', $del).") AND socialSandboxType='id_socialpost'");
	}else{
		$this->dbQuery("UPDATE k_socialpost SET socialPostHide=1 WHERE id_socialpost IN(".implode(',', $del).")");
	}

	// Reconstruire le CACHE pour ce post (flat + thread)
	$this->socialPostBuild($post['id_socialpostthread']);

	// Updater les USER-CACHE pour tous les SUBSCRIBER pour tous les POST (POST + REPLY)
	foreach($sub as $u){
		foreach($del as $p){
			// ... post
			$this->socialPostUserUpdate(array(
				'id_socialpost'	=> $p,
				'id_user'		=> $u,
				'undo'			=> true,
			));
			// ... reply
			$this->socialPostUserUpdate(array(
				'id_socialpost'	=> $p,
				'id_user'		=> $u,
				'reply'			=> true,
				'undo'			=> true,
			));
		}
	}

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostBuild($id_socialpostthread){

	$flat 		= $this->socialPostBuildFlat($id_socialpostthread);
	$thread		= $this->socialPostBuildThread($id_socialpostthread, $id_socialpostthread);

	$def = array('k_socialpost' => array(
		'socialPostFlat'		=> array('value' => json_encode($flat)),
		'socialPostThread'		=> array('value' => json_encode($thread)),
	));

	$this->dbQuery($this->dbUpdate($def)." WHERE id_socialpost=".$id_socialpostthread);
	#$this->pre($this->db_query, $this->db_error);
	
	// Doit etre independant, et a la fin
	$this->socialPostBuildSubscribed($id_socialpostthread);
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostBuildFlat($starter){

	$all = $this->dbMulti("
		SELECT * FROM k_socialpost
		WHERE id_socialpostthread=".$starter." AND id_socialpost != ".$starter." AND socialPostHide=0
		ORDER BY id_socialpost"
	);

	if(sizeof($all) == 0) return array();

	foreach($all as $e){
		$tmp[] = intval($e['id_socialpost']);
	}
	
	return $tmp;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostBuildThread($thread, $mid_socialpost){

	$children = $this->dbMulti("
		SELECT id_socialpost FROM k_socialpost
		WHERE id_socialpostthread=".$thread." AND mid_socialpost=".$mid_socialpost." AND socialPostHide=0
		ORDER BY id_socialpost
	");

	if(sizeof($children) > 0){
		foreach($children as $c){
			$tmp[] = array(
				'i' => intval($c['id_socialpost']),
				's' => $this->socialPostBuildThread($thread, $c['id_socialpost'])
			);
		}
		return $tmp;
	}else{
		return array();
	}
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostBuildSubscribed($id_socialpost){

	$all = $this->dbMulti("SELECT * FROM k_socialpost WHERE id_socialpostthread=".$id_socialpost." AND socialPostHide=0");
	$usr = array();

	foreach($all as $u){
		$usr[]	= $u['id_user'];

		$plus	= json_decode($u['socialPostRatePlusUser'],  true);
		$plus	= is_array($plus) ? $plus : array();

		$minus	= json_decode($u['socialPostRateMinusUser'], true);
		$minus	= is_array($minus) ? $minus : array();

		$usr	= array_merge($usr, $plus, $minus);
	}

	$usr = array_flip(array_flip($usr));
	$usr = array_map('intval', $usr);
	$usr = array_values($usr);

	$def = array('k_socialpost' => array(
		'socialPostSubscribed'	=> array('value' => json_encode($usr))
	));

	$this->dbQuery($this->dbUpdate($def)." WHERE id_socialpost=".$id_socialpost);
#	$this->pre($this->db_query, $this->db_error);
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
function socialPostRateForUser($id_user){

	$empty = array('plus' => array(), 'minus' => array());
	if(intval($id_user) <= 0) return $empty;

	$u = $this->dbOne("SELECT * FROM k_usersocial WHERE id_user=".$id_user);
	if($u['id_user'] != $id_user) return $empty;

	$p = json_decode($u['userSocialPostRatePlus'],  true);
	$m = json_decode($u['userSocialPostRateMinus'], true);

	return array(
		'plus'	=> (is_array($p) ? $p : array()),
		'minus'	=> (is_array($m) ? $m : array())
	);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostRateUserUpdate($id_user, $id_socialpost, $field, $action){

	if(intval($id_user) == 0)		return false;
	if(intval($id_socialpost) == 0)	return false;
	if(!in_array(strtolower($field),  array('plus', 'minus')))	return false;
	if(!in_array(strtolower($action), array('add',  'del')))	return false;

	$rates = $this->socialPostRateForUser($id_user);
	$rates = $rates[strtolower($field)];

	if($action == 'DEL'){
		foreach($rates as $n => $r){
			if($r == $id_socialpost) unset($rates[$n]);
		}
	}else
	if($action == 'ADD'){
		if(!in_array($id_socialpost, $rates)) $rates[] = $id_socialpost;
	}

	$fld	= 'userSocialPostRate'.$field;
	$rates  = array_map('intval', $rates);
	$json	= json_encode($rates);

	$query	= $this->dbInsert(array('k_usersocial' => array(
		$fld 		=> array('value' => $json),
		'id_user'	=> array('value' => $id_user)
	)));
	$query .= "\nON DUPLICATE KEY UPDATE ".$fld."='".$json."';";
	
	$this->dbQuery($query);
	#$this->pre("UPDATE USER", $query, $this->db_error);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostRateSet($opt=array()){
if($opt['debug']) $this->pre("OPTION", $opt);

	// Security
	$id_user		= $opt['id_user'];			if(intval($id_user) <= 0)		return false;
	$id_socialpost	= $opt['id_socialpost'];	if(intval($id_socialpost) <= 0) return false;
	if($opt['plus'] == false && $opt['minus'] == false)							return false;

	$post = $this->dbOne("SELECT * FROM k_socialpost WHERE id_socialpost=".$id_socialpost);
	$pVal = intval($post['socialPostRatePlus']);
	$mVal = intval($post['socialPostRateMinus']);

	if($opt['plus']){
		$users = json_decode($post['socialPostRatePlusUser'], true);
		$users = is_array($users) ? $users : array();

		if(!in_array($id_user, $users)){
			$users[] = intval($opt['id_user']);
			$field	 = 'Plus';
			$pVal++;
		}
	}else
	if($opt['minus']){
		$users = json_decode($post['socialPostRateMinusUser'], true);
		$users = is_array($users) ? $users : array();

		if(!in_array($id_user, $users)){
			$users[] = intval($opt['id_user']);
			$field	 = 'Minus';
			$mVal++;
		}
	}

	if(isset($field)){
		// Mettre a jour LES USERS + AVG
		$query	= $this->dbUpdate(array('k_socialpost' => array(
			'socialPostRate'.$field			=> array('function' => 'socialPostRate'.$field.'+1'),
			'socialPostRate'.$field.'User'	=> array('value'	=> json_encode(array_values($users))),
			'socialPostRateAverage'			=> array('value'	=> $pVal + ($mVal * -1))
		)))." WHERE id_socialpost=".$id_socialpost;

		$this->dbQuery($query);
		if($opt['debug']) $this->pre("UPDATE POST", $query, $this->db_error);

		// Mettre a jour LE USER
		$this->socialPostRateUserUpdate($id_user, $id_socialpost, $field, 'ADD');

		// Mettre a jour les SUBSCRIBED du THREAD
		$this->socialPostBuildSubscribed($post['id_socialpostthread']);

		// ACTIVITY + NOTIFICATION
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'id_user'				=> $id_user,
			'notification'			=> true,

			'socialActivityKey'		=> 'id_socialpost',
			'socialActivityId'		=> $id_socialpost,
			'socialActivityThread'	=> $post['id_socialpostthread'],
			'socialActivityFlag'	=> strtoupper($field)
		));
	}

}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostRateUndo($opt){
if($opt['debug']) $this->pre("OPTION", $opt);

	// Security
	$id_user		= $opt['id_user'];			if(intval($id_user) <= 0)		return false;
	$id_socialpost	= $opt['id_socialpost'];	if(intval($id_socialpost) <= 0) return false;
	if($opt['plus'] == false && $opt['minus'] == false) return false;

	$post = $this->dbOne("SELECT * FROM k_socialpost WHERE id_socialpost=".$id_socialpost);
	$pVal = intval($post['socialPostRatePlus']);
	$mVal = intval($post['socialPostRateMinus']);

	if($opt['plus']){
		$users = json_decode($post['socialPostRatePlusUser'], true);
		$users = is_array($users) ? $users : array();

		if(in_array($id_user, $users)){
			foreach($users as $n => $u){
				if($u == $id_user) unset($users[$n]);
			}
			$field = 'Plus';
			$pVal--;
		}
	}else
	if($opt['minus']){
		$users = json_decode($post['socialPostRateMinusUser'], true);
		$users = is_array($users) ? $users : array();

		if(in_array($id_user, $users)){
			foreach($users as $n => $u){
				if($u == $id_user) unset($users[$n]);
			}
			$field = 'Minus';
			$mVal--;
		}
	}

	// Mettre a jour USERS + AVG
	if(isset($field)){
		if(is_array($users)) array_map('intval', $users);

		$query	= $this->dbUpdate(array('k_socialpost' => array(
			'socialPostRate'.$field			=> array('function' => 'socialPostRate'.$field.'-1'),
			'socialPostRate'.$field.'User'	=> array('value'	=> json_encode(array_values($users))),
			'socialPostRateAverage'			=> array('value'	=> $pVal + ($mVal * -1))
		)))." WHERE id_socialpost=".$id_socialpost;

		$this->dbQuery($query);
		if($opt['debug']) $this->pre("UPDATE POST", $this->db_query, $this->db_error);

		// Mettre a jour LE USER
		$this->socialPostRateUserUpdate($id_user, $id_socialpost, $field, 'DEL');

		// Mettre a jour les SUBSCRIBED du THREAD
		$this->socialPostBuildSubscribed($post['id_socialpostthread']);

		// ACTIVITY + NOTIFICATION
		$this->apiLoad('socialActivity')->socialActivitySet(array(
			'debug'					=> false,
			'id_user'				=> $id_user,
			'remove'				=> true,

			'socialActivityKey'		=> 'id_socialpost',
			'socialActivityId'		=> $id_socialpost,
			'socialActivityFlag'	=> strtoupper($field),
		));
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialPostUserUpdate($opt){
if($opt['debug']) $this->pre("[OPT]", $opt);

	if(intval($opt['id_user']) == 0)		return false;
	if(intval($opt['id_socialpost']) == 0)	return false;

	$fd = ($opt['reply']) ? 'userSocialPostReply' : 'userSocialPostOwner';
	$me = $this->dbOne("SELECT ".$fd."Count, ".$fd." FROM k_usersocial WHERE id_user=".$opt['id_user']);
	$db = json_decode($me[$fd], true);
	$db = is_array($db) ? $db : array();
	
	if($opt['debug']) $this->pre("[ME]", $me);

	if($opt['undo']){
		foreach($db as $n => $e){
			if($e == $opt['id_socialpost']) unset($db[$n]);
		}
	}else{
		if(!in_array($opt['id_socialpost'], $db)){
			$db[] = $opt['id_socialpost'];
			
			$this->dbQuery("INSERT IGNORE INTO k_socialpostuser (id_user, id_socialpost) VALUES (".$opt['id_user'].",".$opt['id_socialpost'].")");
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);
		}
	}

	$db		= array_values($db);
	$db		= array_map('intval', $db);
	$count	= sizeof($db);

	$json	= json_encode($db);
	$query	= $this->dbInsert(array('k_usersocial' => array(
		$fd.'Count' => array('value' => $count),
		$fd 		=> array('value' => $json),
		'id_user'	=> array('value' => $opt['id_user'])
	)));
	$query .= "\nON DUPLICATE KEY UPDATE ".$fd."Count=".$count.", ".$fd."='".$json."';";

	$this->dbQuery($query);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);

	if($opt['undo']){
		$me = $this->apiLoad('socialUser')->socialUserGet(array(
			'debug'		=> false,
			'useMedia'	=> false,
			'useField'	=> false,
			'id_user'	=> $opt['id_user'],
		));
	
		$all = array_merge(
			$me['userSocialPostOwner'],
			$me['userSocialPostReply'],
			$me['userSocialPostRatePlus']
		);
		
		if(sizeof($all) == 0) return true;
	
		$all = array_unique($all);
		$all = array_values($all);
	
		$this->dbQuery("DELETE FROM k_socialpostuser WHERE id_user=".$opt['id_user']);
		foreach($all as $a){
			$tmp[] = '('.$opt['id_user'].','.$a.')';
		}
		
		$this->dbQuery("INSERT IGNORE INTO k_socialpostuser (id_user, id_socialpost) VALUES ".implode(',', $tmp));
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	}

	return true;	
}

































} ?>