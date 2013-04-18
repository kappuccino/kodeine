<?php

class coreSearch extends coreApp {

	public function searchGet($opt=array()){

		# Gérer les options
		#
		$limit		= isset($opt['limit']) 		? $opt['limit']			: 30;
		$offset		= isset($opt['offet']) 		? $opt['offset']		: 0;

		if($opt['id_search'] > 0){
			$dbMode = 'dbOne';
			$cond[] = "k_search.id_search=".$opt['id_search'];
		}else{
			$dbMode = 'dbMulti';
		}

		if($opt['searchType'] != '') $cond[] = "searchType='".$opt['searchType']."'";

		# Former les conditions
		#
		if($opt['type'] == 'user') 		$cond[] = "searchType ='user'";
		if($opt['type'] == 'content')	$cond[] = "searchType!='user'";
		if(sizeof($cond) > 0) $where = "WHERE ".implode(" AND ", $cond);

		# SEARCH
		#
		$search = $this->$dbMode("SELECT * FROM k_search\n". $where);

		#  PARAM
		#
		if($dbMode == 'dbMulti'){
			foreach($search as $idx => $c){
				$search[$idx]['searchParam'] = unserialize($search[$idx]['searchParam']);
				if(!is_array($search[$idx]['searchParam'])) $search[$idx]['searchParam'] = array();
			}
		}else
			if($dbMode == 'dbOne'){
				$search['searchParam'] = unserialize($search['searchParam']);
				if(!is_array($search['searchParam'])) $search['searchParam'] = array();
			}

		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $search);

		return $search;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function searchSet($id_search, $def){

		if($id_search > 0){
			$q = $this->dbUpdate($def)." WHERE id_search=".$id_search;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->dbQuery($q);
		if($this->db_error != NULL) return false;

		$this->id_search = ($id_search > 0) ? $id_search : $this->db_insert_id;

		return true;
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function searchSelector($opt){

		$search = $this->searchGet(array(
			'searchType'	=> $opt['searchType']
		));

		if($opt['multi']){
			$value = is_array($opt['value']) ? $opt['value'] : array();

			$form = "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" size=\"".$opt['size']."\" multiple style=\"".$opt['style']."\">";
			foreach($search as $e){
				$selected = in_array($e['id_search'], $value) ? ' selected' : NULL;
				$form .= "<option value=\"".$e['id_search']."\"".$selected.">".$e['searchName']."</option>";
			}
			$form .= "</select>";
		}else
			if($opt['one']){
				$value = is_array($opt['value']) ? $opt['value'][0] : $opt['value'];

				$form  = "<select name=\"".$opt['name']."\" id=\"".$opt['id']."\" style=\"".$opt['style']."\">";
				foreach($search as $e){
					$selected = ($e['id_search'] == $value) ? ' selected' : NULL;
					$form .= "<option value=\"".$e['id_search']."\"".$selected.">".$e['searchName']."</option>";
				}
				$form .= "</select>";
			}

		return $form;
	}

}