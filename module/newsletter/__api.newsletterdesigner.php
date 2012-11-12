<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2011.11.30
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class newsletterdesigner extends newsletter {

function __clone(){}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function newsletterdesigner(){
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function extractBody($file){
	$html = file_get_contents($file);

	if(preg_match_all("#<body(.*)>(.*)</body>#msU", $html, $body, PREG_SET_ORDER)){
		return $body[0][2];
	}else{
		return $html;
	}
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function indent($json) {

    $result      = '';
    $pos         = 0;
    $strLen      = strlen($json);
    $indentStr   = "\t";
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i=0; $i<=$strLen; $i++) {

        // Grab the next character in the string.
        $char = substr($json, $i, 1);

        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
        
        // If this character is the end of an element, 
        // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        // Add the character to the result string.
        $result .= $char;

        // If the last character was the beginning of an element, 
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
            
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        
        $prevChar = $char;
    }
    return str_replace("\/", "/", $result);
}


/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
public function extract($file){

	$html	= file_get_contents($file);
	$save 	= dirname($file).'/index.json';
	$json	= array();

	# Repeater
	#
	if(preg_match_all("#<repeater>(.*)</repeater>#msU", $html, $repeaters, PREG_SET_ORDER)){
		
		foreach($repeaters as $nr => $repeater){
			$r = array();

			// LAYOUT pour ce REPEATER
			if(preg_match_all("#<layout(.*)>(.*)</layout>#msU", $repeater[1], $layouts, PREG_SET_ORDER)){
				foreach($layouts as $layout){
					$l = array();

					// Extract properties
					if(preg_match_all('#(label)=\"([^"]*)\"#msU', $layout[1], $properties, PREG_SET_ORDER)){
						foreach($properties as $property){
							$l[$property[1]] = $property[2];
						}
					}
					
					// Extract singleline
					if(preg_match_all("#<(singleline|multiline)(.*)>(.*)</(singleline|multiline)>#msU", $layout[2], $items, PREG_SET_ORDER)){
						foreach($items as $item){
							$sl = array('type' => $item[1]);
							if(preg_match_all('#(label)=\"([^"]*)\"#msU', $item[2], $properties, PREG_SET_ORDER)){
								foreach($properties as $property){
									$sl[$property[1]] = $property[2];
								}
							}
							$l['form'][] = $sl;
						}
					}

					$l['html'] = trim($layout[2]);
					$r[] = $l;
				}
			}

			$json[$nr][] = $r;
		}
	}
	
	return json_encode($json);

}




























































} ?>