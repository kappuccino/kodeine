<?php

namespace Kodeine;

class user extends appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userSearchCache($opt){

		# update pour 1 USER
		#
		if(intval($opt['id_user']) > 0){

			$id_user	= $opt['id_user'];
			$id_search	= $opt['id_search'];
			$in 		= array();

			$search 	= ($id_search == NULL)
				? $this->mysql->multi("SELECT * FROM k_search WHERE searchType='user'")
				: $this->mysql->multi("SELECT * FROM k_search WHERE id_search=".$id_search);

			if(sizeof($search) == 0) return false;

			foreach($search as $s){
				$s['searchParam'] = unserialize($s['searchParam']);
				if($s['searchParam'] != ''){
					$query	= "SELECT k_user.id_user FROM k_user INNER JOIN k_userdata on k_user.id_user = k_userdata.id_user WHERE\n1 AND ".$this->userSearchSQL($s);
					$data	= @$this->mysql->multi($query);
					$data	= $this->dbKey($data, 'id_user');

					if(in_array($id_user, $data)) $in[] = intval($s['id_search']);
				}
			}

			$this->mysql->query("UPDATE k_user SET userSearchCache='".json_encode($in)."' WHERE id_user=".$id_user);
			if($opt['debug']) $this->pre($this->db_query, $this->db_error);

		}else

		# update pour 1 SEARCH
		#
		if(intval($opt['id_search']) > 0){

			$search = $this->mysql->one("SELECT * FROM k_search WHERE id_search=".$opt['id_search']);
			if(intval($search['id_search']) == 0) return false;

			$search['searchParam'] = unserialize($search['searchParam']);

			$query	= "SELECT k_user.id_user FROM k_user INNER JOIN k_userdata WHERE\n1 AND ".$this->userSearchSQL($search);
			$data	= @$this->mysql->multi($query);
			$data	= $this->dbKey($data, 'id_user');

			if(sizeof($data) == 0) return false;

			$all = $this->mysql->multi("SELECT id_user, userSearchCache FROM k_user WHERE userSearchCache != '' AND id_user IN(".implode(',', $data).")");

			foreach($all as $u){

				$userSearchCache = json_decode($u['userSearchCache'], true);
				$userSearchCache = is_array($userSearchCache) ? $userSearchCache : array();

				if($opt['clean']){

					$tmp = array();
					if(in_array($opt['id_search'], $userSearchCache)){
						foreach($userSearchCache as $s){
							if($s != $opt['id_search']) $tmp[] = $s;
						}

						$this->mysql->query("UPDATE k_user SET userSearchCache='".json_encode($tmp)."' WHERE id_user=".$u['id_user']);
						if($opt['debug']) $this->pre($this->db_query, $this->db_error);
					}

				}else{
					$userSearchCache[]	= intval($opt['id_search']);
					$userSearchCache	= array_unique($userSearchCache);

					$this->mysql->query("UPDATE k_user SET userSearchCache='".json_encode($userSearchCache)."' WHERE id_user=".$u['id_user']);
					if($opt['debug']) $this->pre($this->db_query, $this->db_error);
				}
			}
		}

		return false;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userSearch($opt){

		$search = $this->mysql->one("SELECT * FROM k_search WHERE id_search=".$opt['id_search']);
		$search['searchParam'] = unserialize($search['searchParam']);

		# Gérer les options
		#
		$limit		= ($opt['limit'] != '') 	? $opt['limit']		: 30;
		$offset		= ($opt['offset'] != '') 	? $opt['offset']	: 0;

		# Former les LIMITATIONS et ORDRE
		#
		if(isset($opt['order']) && isset($opt['direction'])){
			$sqlOrder = "\nORDER BY ".$opt['order']." ".$opt['direction'];
		}else{
			$sqlOrder = "\nORDER BY k_user.id_user ASC";
		}
		$c = array();
		$this->total = 0;
		if(is_array($search['searchParam']) && sizeof($search['searchParam']) > 0){
			$q = "SELECT SQL_CALC_FOUND_ROWS * FROM k_user \n".
				 "INNER JOIN k_userdata ON k_user.id_user = k_userdata.id_user\n".
				 "WHERE \n".$this->userSearchSQL($search)."\n".
				 $sqlOrder . " LIMIT ".$offset.",".$limit;

			$c = $this->mysql->multi($q);
			$this->total = $this->db_num_total;
		}
		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $c);


		return $c;
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function userSearchSQL($param, $level=0){
	#$this->pre($param);

		$prompt  = str_repeat("\t", $level);
		$prompt_ = $prompt."\t";

		$q .= $prompt."(\n";

		if(is_array($param['searchParam']) && sizeof($param['searchParam']) > 0){
			foreach($param['searchParam'] as $i => $e){
				$last 	= ($i == sizeof($param['searchParam'])-1);
				$field	= preg_match("#[a-z]#", $e['searchField']) ? $e['searchField'] : 'field'.$e['searchField'];

				if(is_array($e['searchValue'])){
					unset($tmp);
					foreach($e['searchValue'] as $n){
						$tmp[] = "`".$field."` = '".$n."'";
					}
					$q .= $prompt_."(".implode(" AND ", $tmp).")\n";
				}else{
					$q .= $prompt_.$this->dbMatch($field, $e['searchValue'], $e['searchMode'])."\n";
				}

				if(sizeof($e['searchParam']) > 0){
					if($last) $q .= $prompt_.$param['searchChain']."\n";
					$q .= $this->userSearchSQL($e, ($level+1));
				}

				if(!$last) $q .= $prompt_.$param['searchChain']."\n";
			}
		}

		#if(sizeof($c) > 0) $q .= implode($prompt_.$param['searchChain']."\n", $c);

		$q .= $prompt.")\n";

		return $q;

	}


}