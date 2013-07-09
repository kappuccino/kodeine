<?php

namespace Kodeine;

class appConfig{

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function set($module, $name, $value){

		// Protection
		$value	= addslashes(trim($value));
		$module	= addslashes(trim($module));
		$name	= addslashes(trim($name));

		$exists = $this->mysql->one("SELECT 1 FROM k_config WHERE configModule='".$module."' AND configName='".$name."'");
		$query	= ($exists[1])
				? "UPDATE k_config SET configValue='".$value."' WHERE configModule='".$module."' AND configName='".$name."'"
				: "INSERT INTO k_config (configModule, configName, configValue) VALUES ('".$module."', '".$name."', '".$value."')";

		$this->mysql->query($query);
	}

	/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
	+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
	public function get($module, $key=NULL){

		if(@array_key_exists($module, $this->apiConfig)){
			return is_array($this->apiConfig[$module]) ? $this->apiConfig[$module] : array();
		}else{
			$config = $this->mysql->multi("SELECT * FROM k_config WHERE configModule='".$module."'");
			foreach($config as $v){
				if($module == 'bootExt'){
					$part	= explode(':', $v['configName']);
					$field	= $this->apiLoad('field')->fieldGet(array('id_field' => $part[2]));
					$cv		= $v['configValue'];

					if(substr($cv, 0, 2) == $this->splitter && substr($cv, -2) == $this->splitter && $cv != $this->splitter){
						$out[$field['fieldKey']] = explode($this->splitter, substr($cv, 2, -2));
					}else{
						$out[$field['fieldKey']] = $v['configValue'];
					}
				}else{
					if($v['configName'] == $key) return $v['configValue'];
					$out[$v['configName']] = $v['configValue'];
				}

			}
			unset($cv, $field, $id, $config);
			return $out;
		}
	}


}
