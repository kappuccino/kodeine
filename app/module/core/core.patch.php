<?php

class corePatch extends coreApp{


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function tableExists($d){	

	$tables = $this->dbMulti("SHOW TABLES");
	$me		= ($d['prefix'] == 'NO') ? $d['table'] : $this->dbPrefixe.$d['table'];

	foreach($tables as $table){
		$appey   = array_keys($table);
		$table = $table[$appey[0]];
		if($table == $me) return true;
	}
	
	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function indexExists($d){

	$me		= ($d['prefix'] == 'NO') ? $d['table'] : $this->dbPrefixe.$d['table'];
	$index	= $this->dbMulti("SHOW INDEX FROM ".$me);

	foreach($index as $idx){
		if($idx['Column_name'] == $d['index']) return true;
	}

	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function fieldNeedChange($d){

	if(preg_match("#index#", strtolower($d['action']))){
		return false;
	}

	$me		= ($d['prefix'] == 'NO') ? $d['table'] : $this->dbPrefixe.$d['table'];
	$fields = $this->dbMulti("SHOW COLUMNS FROM ".$me);

	$sqlNull		= ($d['null'] == 'YES') 	? 'NULL' : 'NOT NULL';
	$sqlAfter		= ($d['after'] != '') 		? "AFTER `".$d['after']."`" : NULL;
	$sqlFirst		= ($d['first'] == 'YES') 	? "FIRST" : NULL;
	$sqlDefault		= ($d['default'] != '') 	? "DEFAULT '".$d['default']."'" : NULL;
	$sqlUnsigned	= ($d['unsigned'] == 'YES') ? "UNSIGNED" : NULL;
	$table			= ($d['prefix'] == 'NO') 	? $d['table'] : $this->dbPrefixe.$d['table'];

	if($sqlDefault == 'NULL' && $d['null'] == 'YES'){
		$sqlDefault = "DEFAULT NULL";
	}

	list($fielda, $fieldb) = explode(',', $d['field']);
	if($fieldb == '') $fieldb = $fielda;

	foreach($fields as $field){
		if($field['Field'] == $fielda){

			if(strtolower($d['action']) == 'dropfield'){
				return "ALTER TABLE `".$table."` DROP `".$fielda."`";
			}else
			if($d['type'] != '' && strtolower($field['Type']) == strtolower($d['type']) && $fielda == $fieldb){
				return false;
			}else{
				return "ALTER TABLE `".$table."` CHANGE `".$fielda."` `".$fieldb."` ".strtoupper($d['type'])." ".$sqlUnsigned." ".$sqlNull;
			}
		}
	}

	if(strtolower($d['action']) == 'createfield'){
		return "ALTER TABLE `".$table."` ADD `".$fielda."` ".strtoupper($d['type'])." ".$sqlUnsigned." ".$sqlNull." ".$sqlDefault." ".$sqlFirst." ".$sqlAfter;
	}else{
		return false;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function alterTable($d){

	$exists = $this->tableExists($d);

	# RAW SQL query
	#
	if(strtolower($d['action']) == 'sql'){
		return $d['sql'];
	}else


	# Ajouter un INDEX
	#
	if(strtolower($d['action']) == 'createindex' && $exists){
		if(!$this->indexExists($d)){
			foreach(explode(',', $d['field']) as $i){
				if(preg_match("#([a-zA-Z\_]*)" . "(\(([0-9])\))?#", $i, $m)){
					$tmp[] = ($m[3] != NULL) ? '`'.$m[1].'` ('.$m[3].')' : '`'.$m[1].'`';
				}
			}
			return "ALTER TABLE `".$this->dbPrefixe.$d['table']."` ADD INDEX `".$d['index']."` (".implode(',', $tmp).")";
		}
	}else


	# Supprimer un INDEX
	#
	if(strtolower($d['action']) == 'dropindex' && $exists){
		if($this->indexExists($d)){
			return "ALTER TABLE `".$this->dbPrefixe.$d['table']."` DROP INDEX `".$d['index']."`";
		}
	}else


	# Supprimer la table
	#
	if(strtolower($d['action']) == 'droptable' && $exists){
		return "DROP TABLE `".$this->dbPrefixe.$d['table']."`";
	}

	return false;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function patchIt($file){

	$debug = false;
	if(!file_exists($file)) return false;

	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->load($file);
	$xpath = new DOMXPath($doc);
	
	if($debug) $this->pre("<b>File : ".$file."</b>");

	$jobs = $xpath->query('/patch/job');
	if($jobs->length > 0){
		foreach($jobs as $job){

			$def = array(
				'file'		=> $job->getAttributeNode('file')->nodeValue,
				'action'	=> $job->getAttributeNode('action')->nodeValue,
				'table' 	=> $job->getAttributeNode('table')->nodeValue,
				'field' 	=> $job->getAttributeNode('field')->nodeValue,
				'type' 		=> $job->getAttributeNode('type')->nodeValue,
				'null' 		=> $job->getAttributeNode('null')->nodeValue,
				'after' 	=> $job->getAttributeNode('after')->nodeValue,
				'default'	=> $job->getAttributeNode('default')->nodeValue,
				'first'		=> $job->getAttributeNode('first')->nodeValue,
				'unsigned'	=> $job->getAttributeNode('unsigned')->nodeValue,
				'prefix'	=> $job->getAttributeNode('prefix')->nodeValue,
				'ai'		=> $job->getAttributeNode('ai')->nodeValue,
				'primary'	=> $job->getAttributeNode('primary')->nodeValue,
				'index'		=> $job->getAttributeNode('index')->nodeValue,
				'engine'	=> $job->getAttributeNode('engine')->nodeValue,
				'character'	=> $job->getAttributeNode('character')->nodeValue,
				'collate'	=> $job->getAttributeNode('collate')->nodeValue,
				'comment'	=> $job->getAttributeNode('comment')->nodeValue,
				'sql'		=> trim($job->nodeValue) // RAW SQL QUERY
			);
			
			if($def['file'] != ''){
				define('ALLOW_PATCH', true); // Autoriser l'execution du patch

				$def['file'] = str_replace('{thisFolder}', str_replace(KROOT, '', dirname($file)), $def['file']);
				$def['file'] = KROOT.$def['file'];

				if(file_exists($def['file'])){
					include($def['file']);
					if($script !== true) $out[] = $def['file'].' [NO] : '.var_export($script, true);
					unset($script);
				}

			}else	
			if($this->tableExists($def)){

				# Patcher la table
				#
				$sql = $this->alterTable($def);
				if($sql !== false){
					($debug) ? $this->pre($sql) : $this->dbQuery($sql);
					if($this->db_error != NULL) $out[] = $this->db_query."<br />".$this->db_error;
				}

				# Patcher un champ
				#
				else{
					$sql = $this->fieldNeedChange($def);
					if($sql !== false){
						($debug) ? $this->pre($sql) : $this->dbQuery($sql);
						if($this->db_error != NULL) $out[] = $this->db_query."<br />".$this->db_error;
					}
				}
			}
			
			# Patcher une table qui n'existe pas (SQL pur)
			#
			else{
				$sql = $this->alterTable($def);
				if($sql !== false){
					($debug) ? $this->pre($sql) : $this->dbQuery($sql);
					if($this->db_error != NULL) $out[] = $this->db_query."<br />".$this->db_error;
				}
			}
		}
		
		if(sizeof($out) == 0) $out = true;

	}else{
		$out = true;
	}

	return $out;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function installIt($file){

	$debug = false;
	if(!file_exists($file)) return true;
	
	$log = dirname($file).'/install.log';
	$err = false;
	
	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->load($file);
	$xpath = new DOMXPath($doc);
	
	if($debug) $this->pre("<b>File : ".$file."</b>");

	$sql = $xpath->query('/install/sql');
	if($sql->length > 0){
		foreach($sql as $e){			
			foreach(explode(";\n\n", $e->nodeValue) as $q){
				$q = trim($q);
				if(!$err){
					@$this->dbQuery($q);
					
					#$this->pre($q);

					if($this->db_error != ''){
					#	$this->pre($this->db_query, $this->db_error);

						$err = true;
						file_put_contents($log, date("Y-m-d H:i:s")."\n".$q."\n".$this->db_error, FILE_APPEND);
					}
				}
			}
		}
	}

	//Gerer les script de post et pre installation	
	//<preSript></preSript>
	//<postScript></postScript>

	return ($err) ? false : true;	
}









} ?>