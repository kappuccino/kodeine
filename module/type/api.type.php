<?php

class type extends coreApp {

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function typeGet($opt=array()){

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

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function typeSet($id_type, $def){

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

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function typeSetProfile($id_type){

		$id_pro  = intval($this->user['id_profile']);
		$profile = $this->dbOne("SELECT profileRule FROM k_userprofile WHERE id_profile=".$id_pro);
		$rules	 = unserialize($profile['profileRule']);

		$ids   = $rules['id_type'];
		$ids[] = $id_type;
		$ids   = array_values(array_unique($ids));

		$rules['id_type'] = $ids;

		$this->apiLoad('user')->userProfileSet($this->user['id_profile'], array(
			'k_userprofile' => array(
				'profileRule' => array('value' => serialize($rules))
			)
		));
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function typeRemove($id_type){

		if(intval($id_type) == 0) return false;

		# Remove CONTENTS
		#
		$ids = $this->dbMulti("SELECT id_content FROM k_content WHERE id_type=".$id_type);
		if(sizeof($ids) > 0){
			$ids_ = implode(',', $this->dbKey($ids, 'id_content', true));

			// Supprimer tous les contents de ce type			
			$this->dbQuery("DELETE FROM k_content			    WHERE id_type=".$id_type);

			// Supprimer les valeurs des content
			$this->dbQuery("DELETE FROM k_content			    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentdata 		    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentcomment 	    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentgroup		    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentversion        WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentitem		    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentalbum		    WHERE id_content IN(".$ids_.")");

			// Supprimer les associations
			$this->dbQuery("DELETE FROM k_contentsearch		    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentgroup		    WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentgroupbusiness  WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentchapter        WHERE id_content IN(".$ids_.")");
			$this->dbQuery("DELETE FROM k_contentcategory       WHERE id_content IN(".$ids_.")");
		}

		# Remome TYPE
		#
		$this->dbQuery("DELETE FROM k_type			WHERE id_type=".$id_type);
		$this->dbQuery("DELETE FROM k_fieldaffect	WHERE id=".$id_type); // ID equivaut a ID_TYPE

		# Update TABLES
		#
		$this->dbQuery("DROP TABLE IF EXISTS k_content".$id_type);
		$this->dbQuery("DROP TABLE IF EXISTS k_contentitem".$id_type);
		$this->dbQuery("DROP TABLE IF EXISTS k_contentalbum".$id_type);

		# Update CACHE
		#
		$this->apiLoad('field')->fieldCacheBuild();

		return true;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function typeRemoveProfile($id_type){

		$id_pro  = intval($this->user['id_profile']);
		$profile = $this->dbOne("SELECT profileRule FROM k_userprofile WHERE id_profile=".$id_pro);
		$rules	 = unserialize($profile['profileRule']);

		$ids = $rules['id_type'];
		foreach($ids as $n => $id){
			if($id == $id_type) unset($ids[$n]);
		}
		$ids = array_values(array_unique($ids));
		$rules['id_type'] = $ids;

		$this->apiLoad('user')->userProfileSet($id_pro, array(
			'k_userprofile' => array(
				'profileRule' => array('value' => serialize($rules))
			)
		));
	}
}