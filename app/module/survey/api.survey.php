<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.07.27
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class survey extends coreApp {

public function __construct(){
	$this->formErrorLog = array();
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 		? $opt['limit']			: 30;
	$offset		= ($opt['offset'] != '') 		? $opt['offset']		: 0;

	if(is_array($opt['id_survey'])){
		$dbMode = 'dbMulti';
		$cond[] = "k_survey.id_survey IN(".@implode(',', $opt['id_survey']).")";
	}else
	if($opt['id_survey'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_survey.id_survey=".$opt['id_survey'];
	}else{
		$dbMode = 'dbMulti';
	}


	# Search
	#
	if(isset($opt['search'])){
		$cond[] = "surveyName LIKE '%".addslashes($opt['search'])."%'";
	}


	# Former les CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);


	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		if(isset($opt['order']) && isset($opt['direction'])){
			$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
		}else{
			$sqlOrder = "\nORDER BY k_survey.id_survey ASC";
		}

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}


	# SURVEY
	#
	$surveys = $this->$dbMode(
		"SELECT SQL_CALC_FOUND_ROWS * FROM k_survey\n".
		(is_array($join) ? implode ("\n", $join)."\n" : NULL).
		$where . $sqlOrder . $sqlLimit
	);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $surveys);

	return $surveys;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveySet($opt=array()){

	if($opt['debug']) $this->pre("[opt]", $opt);

	$id_survey		= $opt['id_survey'];
	$def			= $opt['def'];

	if($id_survey > 0){
		$def['k_survey']['surveyDateUpdate']	= array('value' => date("Y-m-d H:i:s"));
		$q = $this->dbUpdate($def)." WHERE id_survey=".$id_survey;
	}else{
		$def['k_survey']['surveyDateCreate']	= array('value' => date("Y-m-d H:i:s"));
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if($this->db_error != NULL) return false;

	$this->id_survey = ($id_survey > 0) ? $id_survey : $this->db_insert_id;


	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyRemove($id_survey){
	if(intval($id_survey) <= 0) return false;

	// On supprime tous le GROUP
	$groups = $this->surveyGroupGet(array(
		'id_survey' => $id_survey
	));
	foreach($goups as $e){
		$this->surveyGroupRemove($e);
	}
	
	// On supprime toute les QUERY
	$queries = $this->surveyQueryGet(array(
		'id_survey' => $id_survey
	));
	foreach($goups as $e){
		$this->surveyQueryRemove($e);
	}
	
	// Finallement on KILL le survey
	$this->dbQuery("DELETE FROM k_survey WHERE id_survey=".$id_survey);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveySlotInit($opt){

	if(intval($opt['id_survey']) == 0) return false;

	# Verifier si un SLOT existe deja
	#
	if($opt['id_user'] > 0){
		$ext = "SELECT id_surveyslot FROM k_surveyslot WHERE id_survey=".$opt['id_survey']." AND id_user=".$opt['id_user'];

		// Recuperer le MAIL du USER
		$myu = $this->dbOne("SELECT userMail FROM k_user WHERE id_user=".$opt['id_user']);
		$opt['surveySlotEmail'] = $myu['userMail'];

	}else
	if($opt['surveySlotEmail'] != ''){
		$ext = "SELECT id_surveyslot FROM k_surveyslot WHERE id_survey=".$opt['id_survey']." AND surveySlotEmail='".$opt['surveySlotEmail']."'";
		
		// Verifier si un USER existe avec cette EMAIL, sauver l'id_user
		$uxt = $this->dbOne("SELECT id_user FROM k_user WHERE userMail='".$opt['surveySlotEmail']."'");
		if($uxt['id_user'] != NULL) $opt['id_user'] = $uxt['id_user'];

	}else{
		die(' ? ');
		return false;
	}
	$ext = $this->dbOne($ext);

	# Recuperer l'identifiant du slot, ou en creer un nouveau
	#
	if($ext['id_surveyslot']){
		return $ext['id_surveyslot'];
	}else{

		$def = array('k_surveyslot' => array(
			'id_survey'			=> array('value' => $opt['id_survey']),
			'id_content'		=> array('value' => $opt['id_content'],			'null' => true),
			'id_user'			=> array('value' => $opt['id_user'],			'null' => true),
			'surveySlotEmail'	=> array('value' => $opt['surveySlotEmail'])
		));

		$query = $this->dbInsert($def);
		$this->dbQuery($query);

		return $this->db_insert_id;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveySlotGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Gérer les options
	#
	$limit		= ($opt['limit'] != '') 		? $opt['limit']			: 30;
	$offset		= ($opt['offset'] != '') 		? $opt['offset']		: 0;

	if(is_array($opt['id_surveyslot'])){
		$dbMode = 'dbMulti';
		$cond[] = "k_surveyslot.id_surveyslot IN(".@implode(',', $opt['id_surveyslot']).")";
	}else
	if($opt['id_surveyslot'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_surveyslot.id_surveyslot=".$opt['id_surveyslot'];
	}else{
		$dbMode = 'dbMulti';
	}


	# Former les CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);
	

	# Former les LIMITATIONS et ORDRE
	#
	if($dbMode == 'dbMulti'){
		if(isset($opt['order']) && isset($opt['direction'])){
			$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
		}else{
			$sqlOrder = "\nORDER BY k_surveyslot.id_surveyslot ASC";
		}

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}


	# SURVEY
	#
	$slots = $this->$dbMode(
		"SELECT SQL_CALC_FOUND_ROWS * FROM k_surveyslot\n".
		(is_array($join) ? implode ("\n", $join)."\n" : NULL).
		$where . $sqlOrder . $sqlLimit
	);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $slots);

	return $slots;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	Retourne TRUE si le SURVEY est complet, ou INTEGER pour le prochain GROUP
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveySlotNext($id_surveyslot){

	$mySlot = $this->surveySlotGet(array(
		'id_surveyslot'		=> $id_surveyslot
	));

	$groups = $this->surveyGroupGet(array(
		'id_survey'	=> $mySlot['id_survey'],
		'order'		=> 'surveyGroupOrder',
		'direction'	=> 'ASC'
	));
	
	if($mySlot['surveySlotGroup'] == NULL){
		return intval($groups[0]['id_surveygroup']);
	}else
	if($mySlot['surveySlotGroup'] != NULL){
		foreach($groups as $ng => $g){
			if($g['id_surveygroup'] == $mySlot['surveySlotGroup']){
				return ($ng + 1 < sizeof($groups)) ? intval($groups[($ng + 1)]['id_surveygroup']) : true;
			}
		}

		return intval($groups[0]['id_surveygroup']);
	}
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveySlotFinished($id_surveyslot){
	$this->dbQuery("UPDATE k_surveyslot SET is_finished=1 WHERE id_surveyslot=".$id_surveyslot);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyGroupGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	if(is_array($opt['id_surveygroup'])){
		$dbMode = 'dbMulti';
		$cond[] = "id_surveygroup IN(".@implode(',', $opt['id_surveygroup']).")";
	}else
	if($opt['id_surveygroup'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "id_surveygroup=".$opt['id_surveygroup'];
	}else
	if($opt['id_survey'] > 0){
		$dbMode = 'dbMulti';
		$cond[] = "id_survey=".$opt['id_survey'];
	}else{
		$dbMode = 'dbMulti';
	}

	# CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# LIMIT + ORDER
	#
	if($dbMode == 'dbMulti'){
		$sqlOrder = "\nORDER BY ".((isset($opt['order']) && isset($opt['direction']))
			? $opt['order']." ".$opt['direction']
			: "surveyGroupOrder ASC");

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}

	# QUERY
	#
	$group = $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_surveygroup ".$where . $sqlOrder);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $group);

	return $group;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyGroupSet($opt=array()){

	if($opt['debug']) $this->pre("[opt]", $opt);

	$id_surveygroup	= intval($opt['id_surveygroup']);
	$def			= $opt['def'];

	if($id_surveygroup > 0){
		$q	  = $this->dbUpdate($def)." WHERE id_surveygroup=".$id_surveygroup;
	}else{
		$last = $this->dbOne("SELECT MAX(surveyGroupOrder) AS m FROM k_surveygroup");
		$def['k_surveygroup']['surveyGroupOrder'] = array('value' => $last['m'] + 1);
		$q	  = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if($this->db_error != NULL) return false;

	$this->id_surveygroup = ($id_surveygroup > 0) ? $id_surveygroup : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyGroupRemove($id_surveygroup){
	$this->dbQuery("DELETE FROM k_surveygroup WHERE id_surveygroup=".$id_surveygroup);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQueryGet($opt=array()){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	# Manage OPTION
	#
	if(is_array($opt['id_surveyquery'])){
		$dbMode = 'dbMulti';
		$cond[] = "k_surveyquery.id_surveyquery IN(".@implode(',', $opt['id_surveyquery']).")";
	}else
	if($opt['id_surveyquery'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "k_surveyquery.id_surveyquery=".$opt['id_surveyquery'];
	}else
	if($opt['id_survey'] > 0){
		$dbMode = 'dbMulti';
		$cond[] = "k_surveyquery.id_survey=".$opt['id_survey'];
	}else
	if($opt['id_surveygroup'] > 0){
		$dbMode = 'dbMulti';
		$cond[] = "k_surveyquery.id_surveygroup=".$opt['id_surveygroup'];
	}else{
		$dbMode = 'dbMulti';
	}

	# CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "\nWHERE ".implode(" AND ", $cond);

	# LIMIT + ORDER
	#
	if($dbMode == 'dbMulti'){
		$sqlOrder = "\nORDER BY ".((isset($opt['order']) && isset($opt['direction']))
			? $opt['order']." ".$opt['direction']
			: "surveyQueryOrder ASC");

		if(!$opt['noLimit']){
				$limit	= ($opt['limit'] != '') 	? $opt['limit']	 : 30;
				$offset	= ($opt['offset'] != '') 	? $opt['offset'] : 0;
			$sqlLimit	= "\nLIMIT ".$offset.",".$limit;
		}
	}

	# QUERY
	#
	$query = $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_surveyquery ".$where . $sqlOrder . $sqlLimit);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $query);

	return $query;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQuerySet($opt=array()){

	if($opt['debug']) $this->pre("[opt]", $opt);

	$id_surveyquery	= $opt['id_surveyquery'];
	$def			= $opt['def'];

	if($id_surveyquery > 0){
		$q = $this->dbUpdate($def)." WHERE id_surveyquery=".$id_surveyquery;
	}else{
		$q = $this->dbInsert($def);
	}
	
	@$this->dbQuery($q);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if($this->db_error != NULL) return false;

	$this->id_surveyquery = ($id_surveyquery > 0) ? $id_surveyquery : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQueryRemove($id_surveyquery){
	$this->dbQuery("DELETE FROM k_surveyquery	  WHERE id_surveyquery = ".$id_surveyquery);
	$this->dbQuery("DELETE FROM k_surveyqueryitem WHERE id_surveyquery = ".$id_surveyquery);
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQueryItemGet($opt){

	if($opt['debug']) $this->pre("[OPT]", $opt);

	if(is_array($opt['id_surveyqueryitem'])){
		$dbMode = 'dbMulti';
		$cond[] = "id_surveyqueryitem IN(".@implode(',', $opt['id_surveyqueryitem']).")";
	}else
	if($opt['id_surveyqueryitem'] > 0){
		$dbMode = 'dbOne';
		$cond[] = "id_surveyqueryitem=".$opt['id_surveyqueryitem'];
	}else
	if($opt['id_surveyquery'] > 0){
		$dbMode = 'dbMulti';
		$cond[] = "id_surveyquery=".$opt['id_surveyquery'];
	}else{
		$dbMode = 'dbMulti';
	}

	# CONDITIONS
	#
	if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

	# LIMIT + ORDER
	#
	if($dbMode == 'dbMulti'){
		$sqlOrder = "\nORDER BY ".((isset($opt['order']) && isset($opt['direction']))
			? $opt['order']." ".$opt['direction']
			: "surveyrQueryItemOrder ASC");

		if(!$opt['noLimit']) $sqlLimit = "\nLIMIT ".$offset.",".$limit;
	}

	# ITEM
	#
	$items = $this->$dbMode("SELECT SQL_CALC_FOUND_ROWS * FROM k_surveyqueryitem ".$where . $sqlOrder);

	$this->total	= $this->db_num_total;
	$this->limit	= $limit;

	if($opt['debug']) $this->pre($this->db_query, $this->db_error, $items);

	return $items;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQueryItemSet($opt){

	if($opt['debug']) $this->pre("[opt]", $opt);

	$id_surveyqueryitem	= $opt['id_surveyqueryitem'];
	$def				= $opt['def'];

	if($id_surveyqueryitem > 0){
		$q = $this->dbUpdate($def)." WHERE id_surveyqueryitem=".$id_surveyqueryitem;
	}else{
		$q = $this->dbInsert($def);
	}

	@$this->dbQuery($q);
	if($opt['debug']) $this->pre($this->db_query, $this->db_error);
	if($this->db_error != NULL) return false;

	$this->id_surveyqueryitem = ($id_surveyqueryitem > 0) ? $id_surveyqueryitem : $this->db_insert_id;

	return true;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyQueryItemRemove($id_surveyqueryitem){
	$this->dbQuery("DELETE FROM k_surveyqueryitem WHERE id_surveyqueryitem = ".$id_surveyqueryitem);
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyFormElement($me, $query, $item=array(), $opt=array()){

	$name	= ($opt['name']  == NULL)
		? "query[".$query['id_surveyquery']."]"
		: $opt['name'];
	
	$value = $item['id_surveyqueryitem'];

	if($opt['style'] != NULL) 	$style	= " style=\"".$opt['style']."\"";
	if($opt['id'] != NULL) 		$id		= " id=\"".$opt['id']."\"";

	if($query['surveyQueryType'] == 'RADIO'){
		$sel  = ($me == $value) ? ' checked' : NULL;
		$form = "<input type=\"radio\" name=\"".$name."\" value=\"".$value."\"".$style.$id." ".$sel." />";
	}else
	if($query['surveyQueryType'] == 'CHECKBOX'){
		if(is_array($me)){
			$sel  = in_array($value, $me) ? ' checked' : NULL;
		}
		$form = "<input type=\"checkbox\" name=\"".$name."[]\" value=\"".$value."\"".$style.$id." ".$sel." />";
	}else
	if($query['surveyQueryType'] == 'GRADUATION'){
		$form = "<input type=\"hidden\" name=\"".$name."\" value=\"".$value."\"".$style.$id." />";
	}else
	if($query['surveyQueryType'] == 'FREE'){
		$name	= ($opt['name']  == NULL)
			? "query[".$query['id_surveyquery']."]"
			: $opt['name'];

		$form = "<textarea name=\"".$name."\"".$style.$id.">".$me."</textarea>";
	}else{
		return array();
	}

	return $form;
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyFormValue($form){
	return ($this->formError) ? $form : NULL;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyFormSubmit($opt=array()){
	
	$id_survey		= $opt['id_survey'];
	$id_surveyslot	= $opt['id_surveyslot'];
	$query			= $opt['query'];
	$error			= false;

	# Parcourir les reponses
	#
	#$this->pre($query);
	foreach($query as $id_surveyquery => $item){

		// Le QUERY dans la BDD
		$dbQuery = $this->surveyQueryGet(array(
			'id_surveyquery' => $id_surveyquery
		));

		if(($item == '' && $dbQuery['allow_empty'] == '1') OR $item != ''){

			// Verifier si un SLOTITEM existe
			$ext = $this->dbOne("SELECT id_surveyslot FROM k_surveyslotitem WHERE id_surveyslot=".$id_surveyslot." AND id_surveyquery=".$id_surveyquery);
			if($ext['id_surveyslot'] == ''){

				if(!is_array($item)) $item = array($item);

				foreach($item as $key => $itm){

					// Ajuster les valeurs possibles
					if($dbQuery['surveyQueryType'] == 'FREE'){
						$surveySlotItemText = $itm;
					}else
					if($dbQuery['surveyQueryType'] == 'GRADE'){
						$surveySlotItemRate = $itm;
					}else
					if($dbQuery['surveyQueryType'] == 'RADIO' OR $dbQuery['surveyQueryType'] == 'CHECKBOX'){
						if($key === '@'){ // Choix "autre"
							$surveySlotItemText = $itm;
							// Cas "autre" et vide
							if(trim($itm) == ''){
								$error = true;
								$this->formErrorLog[$dbQuery['id_surveyquery']] = true;
							}
						}else{
							$surveyQueryItem	= $itm;
						}
					}

					// Structure de la reponse
					$def = array('k_surveyslotitem' => array(
						'id_surveyslot'			=> array('value' => $id_surveyslot),
						'id_surveyquery'		=> array('value' => $id_surveyquery),
						'id_surveyqueryitem'	=> array('value' => $surveyQueryItem, 		'null' => true),
						'surveySlotItemText'	=> array('value' => $surveySlotItemText, 	'null' => true),
						'surveySlotItemRate'	=> array('value' => $surveySlotItemRate, 	'null' => true),
					));

					$sql[] = $this->dbInsert($def);
					unset($surveyQueryItem, $surveySlotItemText, $surveySlotItemRate);
				}
			}
		}else{
			$error = true;
			$this->formErrorLog[$dbQuery['id_surveyquery']] = true;
		}
	}

	if(!$error){
		$sql[] = "UPDATE k_surveyslot SET surveySlotGroup=".$dbQuery['id_surveygroup']." WHERE id_surveyslot=".$id_surveyslot;
		foreach($sql as $e){
			$this->dbQuery($e);
		}
	}else{
		$this->formError = true;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function surveyStat($id_survey){

	$query = $this->surveyQueryGet(array(
		'id_survey'	=> id_survey
	));

	foreach($query as $q){	
		$items = $this->surveyQueryItemGet(array(
			'id_surveyquery' => $q['id_surveyquery']
		));

		$people = $this->dbOne("SELECT COUNT(*) AS H FROM k_surveyslotitem WHERE id_surveyquery=".$q['id_surveyquery']." GROUP BY id_surveyqueryitem");
		$merge['player'] = $people['H'];

		foreach($items as $i){
			$slotItem 	= $this->dbOne("SELECT COUNT(*) AS H FROM k_surveyslotitem WHERE id_surveyqueryitem=".$i['id_surveyqueryitem']);
			$merge['item'][$i['id_surveyqueryitem']] = $slotItem['H'];
		}

		# Verifier s'il existe des AUTRE		
		$other = $this->dbOne("SELECT COUNT(*) AS H FROM k_surveyslotitem WHERE id_surveyquery=".$q['id_surveyquery']." AND id_surveyqueryitem IS NULL AND surveySlotItemText != ''");
		if($other['H'] > 0) $merge['item']['@'] = $other['H'];

		$merge['total'] = array_sum($merge['item']);

		foreach($merge['item'] as $idx => $count){
			$percent = round(($count / $merge['total']) * 100);

			$merge['item'][$idx] = array(
				'count'		=> $count,
				'percent'	=> $percent
			);
		}

		$end[$q['id_surveyquery']] = $merge;
		unset($merge);
	}

	#$this->pre($end);

	#die();

	return $end;
}








} ?>
