<?php

class socialSandbox extends social{

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialSandboxGet($opt){
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialSandboxSet($opt){

	if($opt['debug']) $this->pre($opt);

	# NEW !
	#
	if($opt['id_socialsandbox'] == NULL){
		$this->dbQuery("INSERT INTO k_socialsandbox (socialSandboxType) VALUES ('')");
		if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	
		$id_socialsandbox  = $this->db_insert_id;
	}else{
		$id_socialsandbox  = $opt['id_socialsandbox'];
	}
	$this->id_socialsandbox	= $id_socialsandbox;


	# CORE
	#
	$query = $this->dbUpdate(array('k_socialsandbox' => $opt['core']))." WHERE id_socialsandbox=".$id_socialsandbox;
	$this->dbQuery($query);
	if($opt['debug']) $this->pre("QUERY", $this->db_query, "ERROR", $this->db_error);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialSandboxPush($opt){

	$root 	= $this->socialSandboxRoot($opt['socialSandboxType'], $opt['socialSandboxId']);
	$exists = $this->socialSandboxExists($root['type'], $root['id']);

	if(!$exists){
		$this->socialSandboxSet(array(
			'debug'	=> $opt['debug'],
			'core'	=> array(
				'socialSandboxType'	=> array('value' => $root['type']),
				'socialSandboxId'	=> array('value' => $root['id'])
			)
		));
	}

	return true;
}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function socialSandboxRoot($type, $id){

		$hook = $this->hookAction('socialSandboxRoot', $type, $id);
		if(is_array($hook)) return $hook;

		// Dans le cas ou je n'ai pas de hook alors on retourne simplement les valeurs
		return array('type' => $type, 'id' => $id);
	}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialSandboxExists($type, $id){
	if($type == '' OR intval($id) == 0)	return false;
	$ext = $this->dbOne("SELECT 1 FROM k_socialsandbox WHERE del=0 AND socialSandboxType='".$type."' AND socialSandboxId=".$id);
	return ($ext[1] === '1');
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function socialSandboxRemove($opt){

}

}