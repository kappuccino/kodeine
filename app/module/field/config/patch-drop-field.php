<?php

	if(!defined('ALLOW_PATCH')) die('Direct access deny');


	$tables_ = array(
		'k_userfield'		=> array('key' => 'user'),
		'k_groupfield'		=> array('key' => 'usergroup',	'id' => 'id_group'),

		'k_categoryfield'	=> array('key' => 'category'),
		'k_chapterfield'	=> array('key' => 'chapter'),
	
		'k_typefield'		=> array('key' => 'type',		'id' => 'id_type'),
		'k_typealbumfield'	=> array('key' => 'typealbum', 	'id' => 'id_type'),
		'k_typeitemfield'	=> array('key' => 'typeitem',	'id' => 'id_type'),
	);

	# On sauve les AFFECTATION des CHAMPS et on RENOMME les @TABLES
	#
	foreach($tables_ as $table_ => $opt){
		if($this->tableExists(array('table' => $table_))){

			$raw = $this->dbMulti("SELECT * FROM ".$table_." ORDER by `order` ASC ");
			$tmp = array();

			if($opt['id'] != ''){
				foreach($raw as $e){
					$tmp[$e[$opt['id']]][] = intval($e['id_field']);
				}
				
				if(sizeof($tmp) > 0){
					foreach($tmp as $id => $ids){
						$this->apiLoad('field')->fieldAffectSet($opt['key'], $ids, $id);
					}
				}

			}else{
				foreach($raw as $e){
					$tmp[] = intval($e['id_field']);
				}

				$this->apiLoad('field')->fieldAffectSet($opt['key'], $tmp);
			}

			$this->dbQuery("RENAME TABLE ".$table_." TO  `@".$table_."`");
		}
	}

	# 
	#

	$script = true;

?>