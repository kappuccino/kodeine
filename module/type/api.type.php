<?php

class type extends coreApp {

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function typeGet($opt=array()){

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='typeGet() @='.json_encode($opt));

		$order 		= isset($opt['order']) 		? $opt['order'] 	: 'typePos';
		$direction	= isset($opt['direction'])	? $opt['direction']	: 'ASC';

		if($opt['cp']) $cond[] = "is_cp=1";

		if($opt['profile']){
			if(strlen(trim($this->profile['type'])) == 0) return array();
			$cond[] = "id_type IN(".$this->profile['type'].")";
		}

		if($opt['id_type'] > 0){
			$dbMode = 'dbOne';
			$cond[] = "id_type=".$opt['id_type'];
		}else
			if($opt['typeKey'] != NULL){
				$dbMode = 'dbOne';
				$cond[] = "typeKey='".addslashes($opt['typeKey'])."'";
			}else{
				$dbMode	= 'dbMulti';
			}

		if(sizeof($cond) > 0) $sqlWhere = " WHERE ".implode(" AND ", $cond)." ";

		if($dbMode == 'dbMulti'){
			if($order != NULL && $direction != NULL && $dbMode == 'dbMulti'){
				$sqlOrder = "ORDER BY ".$order." ".$direction;
			}
		}

		$type = $this->$dbMode("SELECT * FROM k_type " . $sqlWhere . $sqlOrder);


		if($dbMode == 'dbOne'){
			$type['typeFormLayout'] = json_decode($type['typeFormLayout'], true);

			if(!is_array($type['typeFormLayout'])){

				$type['typeFormLayout'] = array(
					'tab' => array(
						'view0' => array(
							'label' => 'Défaut',
							'field' => array()
						)
					),
					'bottom' => array()
				);
			}
			$type['typeListLayout'] = json_decode($type['typeListLayout'], true);
			if(!is_array($type['typeListLayout'])) $type['typeListLayout'] = array();
		}

		if($opt['debug']) $this->pre($this->db_query, $this->db_error, $type);

		if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

		return $type;
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function typeSet($id_type, $def){

		if(!$this->formValidation($def)) return false;

		if($id_type > 0){
			$q = $this->dbUpdate($def)." WHERE id_type=".$id_type;
		}else{
			$q = $this->dbInsert($def);
		}

		@$this->dbQuery($q);
		if($this->db_error != NULL) return false;
		$this->id_type = ($id_type > 0) ? $id_type : $this->db_insert_id;

		if($id_type == NULL){

			$pattern = "CREATE TABLE `%s` (
				`id_content` MEDIUMINT(64) NOT NULL ,
				`language`   CHAR(2) NOT NULL
			) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_unicode_ci";

			# Creation de la table pour les ITEMS
			#
			if($def['k_type']['is_gallery']['value']){

				// album
				$this->dbQuery(sprintf($pattern, 'k_contentalbum'.$this->id_type));
				$this->dbQuery("ALTER TABLE `k_contentalbum".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->dbQuery("ALTER TABLE `k_contentalbum".$this->id_type."` ADD INDEX (`language`)");

				// items
				$this->dbQuery(sprintf($pattern, 'k_contentitem'.$this->id_type));
				$this->dbQuery("ALTER TABLE `k_contentitem".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->dbQuery("ALTER TABLE `k_contentitem".$this->id_type."` ADD INDEX (`language`)");
			}

			# Content
			#
			else{
				$this->dbQuery(sprintf($pattern, 'k_content'.$this->id_type));
				$this->dbQuery("ALTER TABLE `k_content".$this->id_type."` ADD PRIMARY KEY (`id_content`, `language`)");
				$this->dbQuery("ALTER TABLE `k_content".$this->id_type."` ADD INDEX (`language`)");
			}
		}

		return true;
	}


}