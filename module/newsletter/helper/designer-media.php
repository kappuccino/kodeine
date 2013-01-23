<?php
    if(!$app->userIsAdmin) header("Location: ./");

    //$app->pre($_SERVER);

    $w = $_REQUEST['w'];
    $h = $_REQUEST['h'];
    $src = $_REQUEST['src'];
    $do = true;

    $root = "http://".$_SERVER["HTTP_HOST"];

    if(strpos($src, "http") === false && strpos($src, "https") === false) {
        if(strpos($src, $root) !== false) $src = str_replace($root, "", $src);
        $opt["url"] = $src;
        if($w > 0 && $h > 0) {
            $opt["mode"] = "crop";
            $opt["value"] = $w;
            $opt["second"] = $h;
        }elseif($w > 0 && $h == "") {
            $opt["mode"] = "width";
            $opt["value"] = $w;
        }elseif($w == "" && $h > 0) {
            $opt["mode"] = "height";
            $opt["value"] = $h;
        }else {
            $do = false;
        }
        if($do) {
            $img = @$app->mediaUrlData($opt);
            if($img["img"] != "") $src = $img["img"];
            //$app->pre($_REQUEST, $opt, $img);
        }

    }
    if(strpos($src, "http") === false && strpos($src, "https") === false) {
        $src = $root.$src;
    }
    die($src);
?>