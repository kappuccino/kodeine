<?php

class coreI18n extends coreApp{

public function __construct(){

	$lang ='fr_FR.UTF8';

	putenv("LC_ALL=$lang");
	setlocale(LC_ALL, $lang);

	$folder = MODULE.'/core/locale';

	bindtextdomain('default', $folder);
	textdomain('default');

}

}