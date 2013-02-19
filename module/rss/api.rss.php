<?php
/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Last release		2010.10.18
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */

class rss{

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Function		rssChanelAssign
	DocTag			kRSS.rssChanelAssign-1.0.0
	Description		
	Parameters		var(string), val(string)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function rssChanelAssign($var, $val){
	$this->chanel .= "\t<".$var."><![CDATA[".$val."]]></".$var.">\n";
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Function		rssChanelAssign
	DocTag			kRSS.rssChanelAssign-1.0.0
	Description		
	Parameters		none
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function rssItemOpen(){
	$this->item .= "<item>\n";
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Function		rssItemClose
	DocTag			kRSS.rssItemClose-1.0.0
	Description		
	Parameters		none
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function rssItemClose(){
	$this->item .= "</item>\n";
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Function		rssItemAssign
	DocTag			kRSS.rssItemAssign-1.0.0
	Description		
	Parameters		var(string), val(string)
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function rssItemAssign($key, $val){
	$this->item .= "<".$key."><![CDATA[".$val."]]></".$key.">\n";
}

/* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
	Function		rssBuild
	DocTag			kRSS.rssBuild-1.0.0
	Description		
	Parameters		none
+ - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
function rssBuild(){

	$this->flux  = "<?phpxml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$this->flux .= "<rss version=\"2.0\">\n";
	$this->flux .= "<channel>\n";
	$this->flux .= $this->chanel;
	$this->flux .= $this->item;
	$this->flux .= "</channel>\n";
	$this->flux .= "</rss>\n";

	return $this->flux;
}

}
?>