<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.09.22
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class template extends coreApp{

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function templateSelector($opt=array()){

	$templates = $this->templateGet();
	
	$html = "<select name=\"".$opt['name']."\" style=\"".$opt['style']."\">";
	if($opt['empty']) $html .= "<option value=\"\">".$opt['emptyText']."</option>";

	foreach($templates as $e){
		$html .= "<option value=\"".$e['name']."\"".(($e['name'] == $opt['value']) ? ' selected' : NULL).">".$e['view']."</option>";
	}
	$html .= "</select>";
	
	return $html;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function templateGet(){

	$folders = $this->fsFolder(TEMPLATE, NULL, 'FLAT');
	if(sizeof($folders) == 0) return array();
	sort($folders);
	
	foreach($folders as $e){
		$name = basename($e);
		$info = $this->templateInfoGet($name);
		$view = ($info['name'] != NULL) ? $info['name'] : $name;
		$key  = @array_key_exists($view, $tpl) ? $name : $view;
		
		$tpl[$key] = array(
			'name' 	=> $name,
			'view'	=> $key
		);
	}
	
	ksort($tpl);
	$tpl = array_values($tpl);
	
	return $tpl;
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function templateInfoGet($name){
	
	$file = TEMPLATE.'/'.$name.'/info.xml';
	if(!file_exists($file)) return false;
	
	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;
	$doc->load($file);
	$xpath = new DOMXPath($doc);
	$element  = $xpath->query('//element');
	$data = array('template' => $name);

	# Info
	$data['name'] = $element->item(0)->getElementsByTagName('name')->item(0)->nodeValue;

	# Options
	$options	= $xpath->query('//element/options/item');
	
	if($options->length > 0){
		foreach($options as $option){
			
			$tmp = array(
				'key' 		=> $option->getAttributeNode('key')->nodeValue,
				'type'		=> $option->getAttributeNode('type')->nodeValue,
				'name' 		=> $option->getElementsByTagName('name')->item(0)->nodeValue,
				'style' 	=> $option->getElementsByTagName('style')->item(0)->nodeValue,
				'default'	=> $option->getElementsByTagName('default')->item(0)->nodeValue
			);

			$xpath		= new DOMXPath($doc);
			$choices	= $option->getElementsByTagName('choice');

			if($choices->length > 0){
				foreach($choices as $choice){			
					$tmp['choice'][] = array(
						'choiceKey'		=> $choice->getAttributeNode('key')->nodeValue,
					#	'choiceName' 	=> $choice->getElementsByTagName('name')->item(0)->nodeValue,
						'choiceValue' 	=> $choice->getElementsByTagName('value')->item(0)->nodeValue,
						'choiceDefault'	=> ((strtolower($choice->getAttributeNode('default')->nodeValue) == 'yes') ? true : false)
					);
				}
			}

			#die($this->pre($tmp));
			$data['options'][] = $tmp;
			unset($tmp);
		}
	}

	return $data;
}



}
?>