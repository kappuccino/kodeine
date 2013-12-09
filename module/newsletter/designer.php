<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
    $id_newsletter  = $_REQUEST['id_newsletter'];

    if($id_newsletter != NULL && $id_newsletter > 0 ){
        $data = $app->apiLoad('newsletter')->newsletterGet(array(
            'id_newsletter' 	=> $id_newsletter
        ));
        if($data['id_newsletter'] != $id_newsletter) die('Newsletter '.$id_newsletter.' inexistante');
        if($data['id_newsletter'] != $id_newsletter) die('Newsletter '.$id_newsletter.' inexistante');

    }else {
        die('Erreur ID');
    }
    if($data['newsletterSendDate'] != NULL) {
        die('Newsletter déjà été envoyée');
    }

    $html = $data['newsletterHtmlDesigner'];
    //die($app->pre($html));
    $head    = '
        <link rel="stylesheet" type="text/css" media="all" href="ui/css/designer.css" />
    <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.0/themes/base/jquery-ui.css" />
    ';
    if(trim($html) == '') $init = '<script type="text/javascript">var init = true;</script>';
    else $init = '<script type="text/javascript">var init = false;</script>';

    $end     = '

        <!--TEMPLATE-->
        <div class="edit ui-widget-content">
        <div class="edit-header ui-widget-header">
            <a class="btn save">Enregistrer</a><a class="btn close">Annuler</a>
        </div>
            <div class="top">
                <div class="in">
                    Ajouter un &eacute;l&eacute;ment : <select id="layoutAdd"></select>
                </div>
            </div>
        </div>
        <div id="overlay"></div>
        '.$init.'
        <script type="text/javascript">var id_newsletter = '.$id_newsletter.';</script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.js"></script>
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
        <script type="text/javascript" src="/admin/core/vendor/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        <script src="/admin/core/ui/js/common.js" type="text/javascript"></script>

        <script type="text/javascript" src="ui/js/designer.js"></script>

        <!--/TEMPLATE-->
    ';

    if(trim($html) == '') {
        $html = $data['newsletterTemplateSource'];
        $html = str_replace("</head>", $head."</head>", $html);
        $html = str_replace("</body>", $end."</body>", $html);
    }else {
        $html = str_replace("</body>", $init."</body>", $html);
    }

    die($html);
?>