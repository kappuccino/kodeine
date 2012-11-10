<?php

	if(preg_match("#^tag-([0-9]*)-(.*)?#", $this->kodeine['get']['urlFile'], $r)){
		$v = array('moduleFolder' =>'content', 'moduleFile' => 'index', 'categoryUrl' => $r[2], 'categoryOffset' => $r[1]);
	}else
	if(strtolower($this->kodeine['get']['urlExtension']) == 'rss'){
		$v = array('moduleFile' => 'feed');
	}else
	if($this->kodeine['moduleFolder'] == 'content' && preg_match("#cat-(.*)#", $this->kodeine['get']['urlFile'], $r)){
		$v = array('moduleFolder' =>'content', 'moduleFile' => 'category', 'categoryUrl' => $r[1]);
	}else
	if($this->kodeine['moduleFolder'] == 'content' && preg_match("#(.*)#", $this->kodeine['get']['urlFile'], $r)){
		$v = array('moduleFolder' =>'content', 'moduleFile' => 'detail');
	}


	# On sauve les changement sur KODEINE globale
	if(is_array($v)){
		foreach($v as $ke => $va){
			$this->kodeine[$ke] = $va;
		}
	}

?>