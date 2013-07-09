<?php

namespace Kodeine;

class appModule{

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	function __invoke($name){
		echo "__invoke ".$name."\n";
	}

	function __get($name){
	#	$allowed = array('app', 'hook', 'me', 'helper', 'media', 'mongo', 'mysql', 'bench');
	#	if(in_array($name, $allowed))
		return app::register($name);
	}

	function __call($name, $arguments){
	#	print_r(func_get_args());

			//	$allowed = array('load');
	//	if(in_array($name, $allowed))
		return call_user_func_array(array($this->app, $name), $arguments);

	#	echo $name."\n";
	#	print_r($arguments);
	}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	public function listing($opt=array()){

		$core = @$this->fsFolder(APP.'/module', '', 'NOROOT_FLAT');
		$user = @$this->fsFolder(USER.'/module', '', 'NOROOT_FLAT');

		$core = is_array($core) ? $core : array();
		$user = is_array($user) ? $user : array();
		$mods = array_merge($core, $user);
		ksort($mods);

		foreach($mods as $mod){

			$core	= (strpos($mod, "/user/module") === false) ? true : false;
			$mod	= basename($mod);
			$module = $this->moduleData($mod, $core);

			if(is_array($module)){
				if($module['hidden'] != 'YES' OR $opt['all']){
					if($opt['profile']){
						if($this->userCan($module['key'].'.index')) $out[$module['name']] = $module;
					}else{
						$out[$module['name']]	= $module;
						$pkg[$module['key']]	= $module;
					}
				}
			}
		}

		ksort($out); ksort($pkg);

		if($opt['dependencies']){
			foreach($pkg as $key => $mod){
				if(is_array($mod['dependencies']) && sizeof($mod['dependencies']) > 0){
					foreach($mod['dependencies'] as $i => $dep){
						if(array_key_exists($dep, $pkg)){
							$pkg[$key]['dependencies'][$dep] = $pkg[$dep];
							$un[] = $dep;

							#	$this->pre('@'.$dep, $un);

							unset($pkg[$key]['dependencies'][$i]);

						}
					}
					ksort($mod['dependencies']);
				}
			}

			if(sizeof($un) > 0){
				#	$this->pre($un, $pkg);
				foreach($un as $unk){
					unset($pkg[$unk]);
				}
			}

			return $pkg;
		}

		#$this->pre($un);


		return $out;
	}

	public function infos($mod, $core=true){

		$folder = ($core) ? '/app/module' : '/user/module';
		$config = KROOT.$folder.'/'.$mod.'/config/config.xml';

		if(!file_exists($config)) return false;

		$doc = new DOMDocument();
		$doc->preserveWhiteSpace = false;
		$doc->load($config);

		$module	= array('folder' => $mod);
		$xpath 	= new DOMXPath($doc);

		$data = $xpath->query('/element/data')->item(0)->childNodes;
		if($data->length > 0){
			foreach($data as $e){
				$module[$e->nodeName] = $e->nodeValue;
			}
		}

		list($myLang) = explode('.', $GLOBALS['language']);
		$locale = $xpath->query('/element/locale');
		if($locale->length > 0){
			foreach($locale as $e){
				if($e->getAttributeNode('language')->nodeValue == $myLang){
					if($e->childNodes->length > 0){
						foreach($e->childNodes as $e){
							$module[$e->nodeName] = $e->nodeValue;
						}
					}
				}
			}
		}

		$i18= $xpath->query('/element/i18n')->item(0)->childNodes;
		if($i18->length > 0){
			foreach($i18 as $e){
				$module['i18n'][] = $e->nodeValue;
			}
		}

		$module['menu'] = ($module['menu'] != '') ? $module['menu'] : 'YES';

		$settings = $xpath->query('/element/profile/item');
		if($settings->length > 0){
			foreach($settings as $set){
				$module['profile'][] = array(
					'code' => $set->getAttributeNode('code')->nodeValue,
					'type' => $set->getAttributeNode('type')->nodeValue,
					'name' => $set->getAttributeNode('name')->nodeValue
				);
			}
		}

		$dependencies = $xpath->query('//element/dependencies/module');
		if($dependencies->length > 0){
			foreach($dependencies as $dep){
				$module['dependencies'][] = $dep->nodeValue;
			}
		}

		$module['needPatch']	= file_exists(KROOT.$folder.'/'.$mod.'/config/patch-todo.xml');
		$module['rePatch']		= file_exists(KROOT.$folder.'/'.$mod.'/config/patch-done.xml');
		$module['isCore']		= $core;

		if($module['panelIcon'] == ''){
			$module['panelIcon'] = NULL;
		}else{
			$module['panelIcon'] = $this->helperReplace($module['panelIcon'], array(
				'moduleFolder' => $mod,
			));
		}

		$config = $this->mysql->multi("
		SELECT configName, configValue
		FROM k_config
		WHERE configName NOT LIKE 'jsonCache%' AND configModule='".basename($mod)."'"
		);

		$module['config'] = array();
		if(sizeof($config) > 0){
			foreach($config as $e){
				$v = $e['configValue'];
				$module['config'][$e['configName']] = $v;
			}
		}


		return  $module;
	}

}