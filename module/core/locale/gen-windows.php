#!/usr/bin/php

<?php

echo "
  _  __         _      _              _                     _
 | |/ /        | |    (_)            | |                   | |
 | ' / ___   __| | ___ _ _ __   ___  | |     ___   ___ __ _| | ___
 |  < / _ \ / _` |/ _ \ | '_ \ / _ \ | |    / _ \ / __/ _` | |/ _ \
 | . \ (_) | (_| |  __/ | | | |  __/ | |___| (_) | (_| (_| | |  __/
 |_|\_\___/ \__,_|\___|_|_| |_|\___| |______\___/ \___\__,_|_|\___|

--------------------------------------------------------------------
\n\n";

$root = dirname(dirname(dirname(__DIR__)));
require $root.'/module/core/helper/app.php';

$app = new coreAdmin();


echo "> Recherche des fichiers a traduire... ";

$modules = $app->moduleList(array('all' => true));
$files = array();

foreach($modules as $mod){
    if(count($mod['i18n']) > 0 && is_array($mod['i18n'])){
        foreach($mod['i18n'] as $f){
            $files[] = '../../'.$mod['key'].'/'.$f;
        }
    }
}

echo "OK\n";

echo "> Generation du .pot ...\n";

$dest   = 'kodeine.pot';
$source = 'kodeine.source';
file_put_contents($source, implode("\n", $files));

echo "OK\n";


echo "> Fix UTF-8... \n";

$cmd = 'C:\Perl64\bin\perl.exe xgettext.pl -f "'.$source.'" -o "'.$dest.'"';

echo "Command : $cmd\n";
$out = $app->helperPipeExec($cmd);

if(is_array($out)) {
    foreach($out as $k=>$v) {
        echo $k." : ".$v."\n";
    }
}
$raw = file_get_contents($dest);
$raw = str_replace('charset=CHARSET', 'charset=UTF-8', $raw);
file_put_contents($dest, $raw);

echo "OK\n";


unlink($source);











echo "\n\n\n\n";
