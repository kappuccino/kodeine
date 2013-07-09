<?php

namespace Kodeine;

class appKodeine extends  appModule{

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public function init($get){

		# CONFIG
		# Charge les parametre de CONFIG BOOT + CUSTOM et memorise les autres APIsCONFIG
		#
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(k_config)');
		$config = $this->mysql->multi("SELECT * FROM k_config");
		foreach($config as $e){
			if($e['configModule'] == 'boot' OR $e['configModule'] == 'custom'){
				if(substr($e['configName'], 0, 7) == 'domain:' && empty($domainConfig)){
					$v = json_decode($e['configValue'], true);
					if(preg_match("#".$v['domain']."#", $_SERVER['HTTP_HOST'])) $domainConfig = $v;
				}else
				if(substr($e['configName'], 0, 9) == 'jsonCache'){
					$this->apisConfig[$e['configModule']][$e['configName']] = json_decode($e['configValue'], true);
				}else{
					$this->kodeine[$e['configName']] = $e['configValue'];
				}
			}else{
				if(substr($e['configName'], 0, 9) == 'jsonCache'){
					$e['configValue'] = json_decode($e['configValue'], true);
				}
				$this->apisConfig[$e['configModule']][$e['configName']] = $e['configValue'];
			}
		}
		unset($config, $e, $v);
		#die($this->pre("*", $this->apisConfig));
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		# LANGUAGE
		# Determine si on utilise la langue de l'URL GET ou celui par DEFAUT (FR)
		#
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(languages)');
		$language = ($get['urlLanguage'] == NULL) ? $this->kodeine['defaultLanguage'] : $get['urlLanguage'];
		if(empty($this->apisConfig['boot']['jsonCacheCountry'])){
			$language = $this->countryGet(array('iso' => $language));
		}else{
			foreach($this->apisConfig['boot']['jsonCacheCountry'] as $tmp){
				if($tmp['iso'] == $language){ $language = $tmp; break; }
			}
		}
		$locale   = ($language['countryLocale'] == NULL) ? 'fr_FR' : $language['countryLocale'];
		$language = $language['iso_ref'];
		$this->kodeine['language']	= $language;
		$this->kodeine['locale'] 	= $locale;
		setlocale(LC_ALL, $locale.'.UTF8');
		unset($locale, $language, $tmp);
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		# CHAPTERS
		# La liste de tous les CHAPTER utilise
		#
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(chapter)');

		$chapters = $this->app->load('chapter')->get(array('language' => $this->kodeine['language']));

		var_dump($chapters);

		foreach($chapters as $e){
			$chaptersDbUi[$e['chapterUrl']] = $e['id_chapter'];
			$chaptersDbId[$e['id_chapter']] = $e;
		} unset($chapters, $e);
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		# URL
		# Decortique l'URL pour determiner le CHAPTER, MODULE, FICHIER
		#
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(url_parser)');
		$query = $get['urlRequest'];
		$parts = explode('/', $get['urlRequest']);
		if(sizeof($parts) > 0){
			$file = $parts[sizeof($parts)-1];
			if($file != NULL){
				$split = explode('.', $file);
				if(sizeof($split) > 1){
					$get['urlExtension']	= $split[sizeof($split)-1];
					$get['urlFile'] 		= substr($file, 0, strlen($file)-strlen($get['urlExtension'])-1);
				}else{
					$get['urlFile'] 		= $file;
				}
			}

			$get['urlChapter']	= $parts[sizeof($parts)-2];
			$get['urlModule'] 	= '';
			if(!@array_key_exists($get['urlChapter'], $chaptersDbUi)){
				$get['urlChapter'] = $parts[sizeof($parts)-3];
				$get['urlModule']  = $parts[sizeof($parts)-2];
			}
		}
		if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);


		# CHECK DOMAIN
		# Verifit les regles en fonction du domaine
		#
		if(!empty($domainConfig)){
			if($domainConfig['id_chapter'] != NULL)	$this->kodeine['defaultIdChapter']	= $domainConfig['id_chapter'];
			if($domainConfig['id_theme']   != NULL)	$this->kodeine['defaultIdTheme']	= $domainConfig['id_theme'];
			if($domainConfig['language']   != NULL)	$this->kodeine['defaultLanguage']	= $domainConfig['language'];
		}

		# CHAPTER
		# Recupere le CHAPTER en fonction de l'URL GET ou celui par DEFAUT
		#
		$chapter = @array_key_exists($get['urlChapter'], $chaptersDbUi)
				? $chaptersDbUi[$get['urlChapter']]
				: $chaptersDbId[$this->kodeine['defaultIdChapter']];

		$this->kodeine['id_chapter']		= $chapter['id_chapter'];
		$this->kodeine['chapterName']		= $chapter['chapterName'];
		$this->kodeine['chapterUrl']		= $chapter['chapterUrl'];
		$this->kodeine['chapterModule']		= $chapter['chapterModule'];
		$this->kodeine['chapterIdTheme']	= $chapter['id_theme'];
#	$this->kodeine['chaptersUrl']		= $chaptersDbUi;
		$this->kodeine['chaptersIds']		= $chaptersDbId;
		unset($chapter, $chaptersDbUi, $chaptersDbId);


		# MODULE
		# Determine si on utilise le module/file de l'URL ou la valeur du CHAPTER/INDEX
		# Si le chapitre n'a pas de module de configure, utiliser content
		#
		$this->kodeine['chapterModule'] = ($this->kodeine['chapterModule'] != NULL) ? $this->kodeine['chapterModule'] : 'content';
		$this->kodeine['moduleFolder']	= ($get['urlModule'] != NULL) ? $get['urlModule'] : $this->kodeine['chapterModule'];
		$this->kodeine['moduleFile']	= ($get['urlFile']   != NULL) ? $get['urlFile']   : 'index';


		# THEME
		# Determine si on utilise le theme par DEFAUT ou le theme du CHAPTER
		#
		$id_theme	= ($this->kodeine['chapterIdTheme'] != NULL) ? $this->kodeine['chapterIdTheme'] : $this->kodeine['defaultIdTheme'];
		if(empty($this->apisConfig['boot']['jsonCacheTheme'])){
			$theme = $this->mysql->one("SELECT * FROM k_theme WHERE id_theme=".$id_theme);
		}else{
			foreach($this->apisConfig['boot']['jsonCacheTheme'] as $e){
				if($e['id_theme'] == $id_theme){
					$theme = $e;
					break;
				}
			}
		}
		$this->kodeine['id_theme'] 		= $theme['id_theme'];
		$this->kodeine['themeName']		= $theme['themeName'];
		$this->kodeine['themeFolder']	= $theme['themeFolder'];
		unset($theme, $id_theme, $e);


		# GROUP
		# Memorise le groupe du USER ou bien celui par DEFAUT (-1)
		#
		$this->kodeine['id_group'] = $this->user['id_group'] ?: -1;


		# SAVE GET
		# Memorise les parametres GET modifies de l'URL
		#
		$this->kodeine['get'] = $get;


		# LOCALISATION
		# Definit en global les traductions
		# 
		if(!isset($_GET['noLabel'])){
			if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(localisation)');

			foreach($this->mysql->multi("SELECT * FROM k_localisation WHERE language = '".$this->kodeine['language']."'") as $e){
				define($e['label'], $e['translation']);
			}

			if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);
		}

		# TYPE
		#
		#if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep='kodeineInit(type)');
		#foreach($this->mysql->multi("SELECT * FROM k_type") as $e){
		#	unset($e['typeFormLayout']);
		#	$this->kodeine['typesIds'][$e['id_type']] = $e;
		#}
		#if(BENCHME) $GLOBALS['bench']->benchmarkMarker($bmStep);

		$this->kodeine = $this->hook->filter('kodeineInit', $this->kodeine);

		# DEBUG
		# Construit la zone de debugage par l'URL url?debug
		#
		if(constant('DEBUGME') === true){
			unset($this->user['groupFormLayout'], $this->user['userPasswd']);
			echo "<pre style=\"background-color:#333333; color:#FFFFFF; width:800px; padding:5px; margin:5px; font-family:courier; font-size:12px;\"> <h1>Debug Data</h1>";
			echo "[GET] ";		print_r($_GET);
			echo "[URL] ";		print_r($get);
			echo "[KODEINE] ";	print_r($this->kodeine);
			echo "[USER] ";		print_r($this->user);
			echo "[SERVER] "; 	print_r($_SERVER);
			echo "</pre>";
		}


		# FATAL Error
		# Verifier un certain nombre de point qui prend du temps a verifier a la main 
		#
		$fatal = array();
		if($this->kodeine['language'] == ''){
			$fatal[] = "Language is not defined";
		}
		if(!file_exists(USER.'/theme/'.$this->kodeine['themeFolder'])){
			$fatal[] = "Theme folder \"".$this->kodeine['themeFolder']."\" is missing";
		}

		if(count($fatal) > 0){
			$this->pre(implode("\n", $fatal));
			exit();
		}

	}

}
