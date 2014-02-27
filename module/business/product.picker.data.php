<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$language  = ($_REQUEST['language'] != NULL) ? $_REQUEST['language'] :  'fr';
    $languages = $app->countryGet(array('is_used' => 1));

    if($_POST['action']){
        $do = true;

        if(!$_POST['contentDateStartDo'])   $_POST['contentDateStart'] == NULL;
        if(!$_POST['contentDateEndDo'])     $_POST['contentDateEnd'] == NULL;

        $_POST['contentDateCreation']   = implode(' ', $_POST['contentDateCreation']);
        $_POST['contentDateUpdate']     = implode(' ', $_POST['contentDateUpdate']);
        $_POST['contentMedia']          = addslashes($_POST['contentMedia']);   

        $def['k_content'] = array(
            'is_version'            => array('value' => $_POST['is_version'],               'zero' => true),
            'contentSee'            => array('value' => $_POST['contentSee'],               'zero' => true),
            'contentTemplate'       => array('value' => $_POST['contentTemplate']),
            'contentTemplateEnv'    => array('value' => serialize($_POST['templateEnv'])),
            'contentComment'        => array('value' => $_POST['contentComment']),
            'contentRate'           => array('value' => $_POST['contentRate']),
            'contentDateCreation'   => array('value' => $_POST['contentDateCreation']),
            'contentDateUpdate'     => array('value' => $_POST['contentDateUpdate']),
            'contentDateStart'      => array('value' => $_POST['contentDateStart'],         'null' => true),
            'contentDateEnd'        => array('value' => $_POST['contentDateEnd'],           'null' => true),
            'contentMedia'          => array('value' => $_POST['contentMedia'])
        );

        if(!$app->formValidation($def)) $do = false;

        $dat['k_contentdata'] = array(
            'contentUrl'                => array('value' => $_POST['contentUrl'],               'check' => '.'),
            'contentName'               => array('value' => $_POST['contentName'],              'check' => '.'),
            'contentHeadTitle'          => array('value' => $_POST['contentHeadTitle'],         'null' => true),
            'contentMetaKeywords'       => array('value' => $_POST['contentMetaKeywords'],      'null' => true),
            'contentMetaDescription'    => array('value' => $_POST['contentMetaDescription'],   'null' => true)
        );
        if(!$app->formValidation($dat)) $do = false;

        if($_POST['useBusiness']){
            $def['k_content']['id_carriage']        = array('value' => $_POST['id_carriage']);
            $def['k_content']['contentStock']       = array('value' => $_POST['contentStock'],      'check' => '[0-9]{0,}');
            $def['k_content']['contentStockNeg']    = array('value' => $_POST['contentStockNeg'],   'zero'  => true);
            $def['k_content']['contentRef']         = array('value' => $_POST['contentRef'],        'check' => '.');
            $def['k_content']['contentWeight']      = array('value' => $_POST['contentWeight'],     'zero'  => true);

            if(!$app->formValidation($def)) $do = false;
        }

        if(!$app->apiLoad('field')->fieldValidation($_POST['field'])) $do = false;

        if($do){

            $result = $app->apiLoad('content')->contentSet(array(
                'id_type'           => $_POST['id_type'],
                'language'          => $_POST['language'],
                'id_content'        => $_POST['id_content'],
                'def'               => $def,
                'data'              => $dat,
                'field'             => $_POST['field'],
                'group'             => $_POST['group'],         // Business
                'id_group'          => $_POST['id_group'],      // Content
                'id_chapter'        => $_POST['id_chapter'],
                'id_category'       => $_POST['id_category'],
                'id_search'         => $_POST['id_search'],
                'id_shop'           => $_POST['id_shop'],
                'id_socialforum'    => $_POST['id_socialforum'],
                'debug'             => false
            ));

            $message = ($result) ? 'OK: Enregistrement' : 'KO: Erreur APP:<br />'.$app->apiLoad('content')->db_error;
            
            if($result && $_POST['is_version']){
                $app->apiLoad('content')->contentVersionSet(array(
                    'id_content'    => $app->apiLoad('content')->id_content,
                    'language'      => $_POST['language']
                ));
            }
            
            if($result && $_POST['resetRating']){
                $app->dbQuery("DELETE FROM k_contentrate WHERE id_content = ".$app->apiLoad('content')->id_content);
            }

            if($result) header("Location: product.picker.data.php?id_content=".$app->apiLoad('content')->id_content.'&language='.$_POST['language']);

        }else{
            $message = 'WA: Validation failed';
        }
    }

    if($_REQUEST['id_content'] != NULL){
    
        if($_REQUEST['reloadFromVersion'] != NULL){
            $data = $app->apiLoad('content')->contentVersionGet(array(
                'id_version' => $_REQUEST['reloadFromVersion']
            ));
        }else{
            $data = $app->apiLoad('content')->contentGet(array(
                'id_content'    => $_REQUEST['id_content'],
                'language'      => $language,
                'debug'         => false,
                'raw'           => true
            ));
        }

        if($data['id_content'] == $_REQUEST['id_content']){
            $type   = $app->apiLoad('type')->typeGet(array('id_type' => $data['id_type'], 'debug' => false));
            $title  = $data['contentName'];
            $tpl    = ($data['contentTemplate'] != NULL) ? $data['contentTemplate'] : $type['typeTemplate'];
            $opt    = $app->apiLoad('template')->templateInfoGet($tpl);
        }else{
            $nFound = true;
            $title  = "Document inconnu";
        }

    }else{
        $type       = $app->apiLoad('type')->typeGet(array('id_type' => $_REQUEST['id_type']));
        $title      = "Nouveau ".$type['typeName'];
    }

    if($type['id_type'] == NULL) $nFound = true;

    if($type['id_type']){
        $fields = $app->apiLoad('field')->fieldGet(array(
            'id_type'       => $type['id_type'],
            'fieldShowForm' => true
        ));
    }
    
    if(sizeof($fields) > 0){
        foreach($fields as $e){
            $fieldId['field'.$e['id_field']] = $e;
            $unAffected[] = 'field'.$e['id_field'];
        }
    }

    $unAffected[] = 'contentName';
    $unAffected[] = 'contentSee';
    $unAffected[] = 'contentHeadMeta';
    $unAffected[] = 'contentTemplate';
    $unAffected[] = 'contentMediaBox';
    $unAffected[] = 'contentAssociation';
    $unAffected[] = 'contentCategory';
    $unAffected[] = 'contentComment';
    $unAffected[] = 'contentDate';

    if($type['is_business'] == '1'){
        $unAffected[] = 'contentStock';
        $unAffected[] = 'contentRef';
        $unAffected[] = 'contentWeight';
        $unAffected[] = 'contentCarriage';
        $unAffected[] = 'contentGroup';
    }

    foreach($unAffected as $idxOFF => $f){
        if(sizeof($type['typeFormLayout']['tab']) > 0){
            foreach($type['typeFormLayout']['tab'] as $e){
                foreach($e['field'] as $fu){
                    if($fu['field'] == $f) unset($unAffected[$idxOFF]);
                }
            }
        }
    }

    foreach($unAffected as $e){
        $type['typeFormLayout']['tab']['view0']['field'][] = array('field' => $e);
    }
    
    $useCount   = 0;
    $usePercent = 100;
    foreach(array('use_group', 'use_search', 'use_chapter', 'use_category', 'use_socialforum') as $use){
        if($type[$use] == '1') $useCount++;
    }
    if($useCount > 0) $usePercent = round(100 / $useCount);

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    function fieldTrace($app, $data, $e, $f){
    
        $closed = ($f['close']) ? 'closed' : '';

        $field  = $app->apiLoad('field')->fieldForm(
            $e['id_field'],
            $app->formValue($data['field'.$e['id_field']], $_POST['field'][$e['id_field']]),
            array(
                'style' => 'width:100%; '.$e['fieldStyle']
            )
        );

        if(preg_match("#richtext#", $field))    $GLOBALS['textarea'][]  = 'form-field-'.$e['id_field'];
        if(preg_match("#media\-list#", $field)) $GLOBALS['mediaList'][] = "'form-field-".$e['id_field']."'";
    #   if(preg_match("#datePicker#", $field))  $GLOBALS['datePick'][]  = "'form-field-".$e['id_field']."'";

        echo "<li class=\"clearfix ".$closed." ".$app->formError('field'.$e['id_field'], 'needToBeFilled')." form-item\" id=\"field".$e['id_field']."\">";
        echo "<div class=\"hand\">&nbsp;</div>";
        echo "<div class=\"toggle\">&nbsp;</div>";

            echo "<label>".$e['fieldName'];
                if($e['is_needed']) echo ' *';
                if(preg_match("#richtext#", $field)){
                    echo "<br /><a href=\"javascript:toggleEditor('form-field-".$e['id_field']."');\">Activer/Désactiver l'éditeur</a>";
                }
            echo "</label>";

            echo "<div class=\"form\">".$field."</div>";

            if($e['fieldInstruction']){
                echo "<div class=\"instruction off\">".$e['fieldInstruction']."</div>";
            }

        echo "</li>";
    }

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(ADMINUI.'/head.php'); ?>

    <!-- CONTENT -->
    <link rel="stylesheet" type="text/css" media="all" href="ressource/css/form.css" />
    <script type="text/javascript" src="ressource/js/content.js"></script>

    <!-- Tiny MCE -->
    <script type="text/javascript" src="ressource/plugin/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>

    <!-- Calendar -->
    <script src="ressource/plugin/calendar-eightysix/js/calendar-eightysix-v1.0.1.js"></script>
    <link type="text/css" media="screen" href="ressource/plugin/calendar-eightysix/css/calendar-eightysix-default.css" rel="stylesheet" />
</head>
<body>

<ul class="menu-icon clearfix">
    <li class=""><a href="product.picker.php?id_type=<?php echo $type['id_type'] ?>"><img src="ressource/img/ico-list.png" height="32" width="32" /><br />Liste</a></li>
</ul>

<div class="app">
<?php

    if($message != NULL){
        list($class, $message) = $app->helperMessage($message);
        echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
    }

    if($nFound){ ?>

        <div class="message messageNotFound"><?php 
        
            $more = $app->dbMulti("SELECT * FROM k_contentdata WHERE id_content='".$_REQUEST['id_content']."'");
    
            if(sizeof($more)){
                echo "Aucun document ne correspond dans cette langue<br />";
                echo "Autre langue disponible : ";
                foreach($more as $e){
                    $iso = $app->countryGet(array('iso' => $e['language'], 'debug' => false));

                    echo "<a href=\"product.picker.data.php?id_content=".$e['id_content']."&language=".$e['language']."\">".$iso['countryLanguage']."</a>";
                }
                echo "</p>";
            }else{
                echo "Aucun document ne correspond";
            }
        ?></div>

<?php }else{ ?>

<form action="product.picker.data.php" method="post" id="data">

<input type="hidden" name="action" value="1" />
<input type="hidden" name="id_type" id="id_type" value="<?php echo $type['id_type'] ?>" />
<input type="hidden" name="id_content" id="id_content" value="<?php echo $data['id_content'] ?>" />
<input type="hidden" name="typeFormLayout" id="typeFormLayout" />
<input type="hidden" name="is_social" value="<?php $data['is_social'] ?>" />

<div style=" margin:5px 0px 0px 0px;">
    <a href="javascript:$('data').submit()" class="button rButton">Enregistrer</a>
    <a href="product.picker.data.php?id_type=<?php echo $type['id_type'] ?>" class="button rButton">Nouveau</a>
    <?php if($data['id_content'] > 0){ ?>
    <a href="product.picker.data.php?id_content=<?php echo $data['id_content'] ?>" class="button rButton">Recharger la page</a>
    <a href="content.language.php?id_content=<?php echo $data['id_content'] ?>&language=<?php echo $data['language'] ?>" class="button rButton">Traduction</a>
    <a href="content.comment.php?id_content=<?php echo $data['id_content'] ?>" class="button rButton">Commentaire</a>
    <a href="content.parent.php?id_content=<?php echo $data['id_content'] ?>" class="button rButton">Gérer le sous-contenu</a>
        <?php if($data['contentSee']){ ?>
        <a href="" class="button rButton" onclick="insertProduct(<?php echo $data['id_content']; ?>);return false;">Séléctionner</a>
        <?php } ?>
    <?php } ?>
</div>

<div class="tabset">
    <ul class="tab clearfix">

        <ul class="do-viewer">
        <?php foreach($type['typeFormLayout']['tab'] as $e){ ?>
        <li class="is-tab do-view"><span class="text"><?php echo utf8_decode($e['label']) ?></span><span class="edit"></span><span class="remove"></span><span class="handle"></span></li>
        <?php } ?>
        </ul>

        <li class="light do-wiew view-all"><span class="text">Tout afficher</span></li>
        <li class="light" id="action-add-tab" style="display:none;"><a href="#" onclick="addTab($$('.tabset')[0])">Ajouter un onglet</a></li>
        <li id="action-move-on"><a href="#" onclick="enableMove()">Modifier les onglets</a></li>
        <li id="action-move-off" style="display:none;"><a href="#" onclick="disableMove()">Fin de modifications</a></li>
        <li class="right right-select">
            Archiver
            <input type="checkbox" name="is_version" value="1" <?php if($app->formValue($data['is_version'], $_POST['is_version'])) echo "checked" ?> />
    
            <select onChange="version(this)"><?php
            if($data['id_content'] != NULL){
                echo "<option value=\"\">".sizeof($versions)." version(s) disponible(s)</option>";
                $versions = $app->apiLoad('content')->contentVersionGet(array(
                    'id_content'    => $data['id_content'],
                    'language'      => $data['language'],
                    'debug'         => false
                ));
                if(sizeof($versions) > 0){
                    foreach($versions as $vrs){
                        $sel = ($_REQUEST['reloadFromVersion'] == $vrs['id_version']) ? ' selected' : NULL;
                        echo "<option value=\"".$vrs['id_version']."\"".$sel.">Afficher la version du : ".$app->helperDate($vrs['versionDate'], '%e %b %Y à %Hh %Mm %S')."</option>";
                    }
                }
            }else{
                echo "<option value=\"\">Aucune version disponible</option>";   
            }
        ?></select>
        </li>
    </ul>

    <?php if($_REQUEST['id_content'] == NULL && sizeof($languages) > 1){ ?>
    <div class="view-label view-label-colored">
        Langue du document <select name="language" id="language" onchange="urlCheck();"><?php
            foreach($languages as $l){
                $sel = ($language == $l['iso']) ? ' selected' : NULL;
                echo "<option value=\"".$l['iso']."\"".$sel.">".$l['countryLanguage']."</option>\n";
            }
        ?></select>
    </div><?php }else{ echo "<input type=\"hidden\" name=\"language\" id=\"language\" value=\"".$language."\" />"; } ?>

    <?php foreach($type['typeFormLayout']['tab'] as $id => $tab){ ?>
    <div class="view view-tab" id="<?php echo $id ?>">
        <div class="view-label view-label-toggle">
            <span><?php echo utf8_decode($tab['label']) ?></span>
        </div>
        <ul class="is-sortable field-list"><?php
            foreach($tab['field'] as $f){

                $name   = $f['field'];
                $e      = $fieldId[$name];

                if(is_array($e)){
                    fieldTrace($app, $data, $e, $f);
                }else{
                    echo "<div id=\"replace-".$name."\" class=\"replace".(($f['close']) ? ' closed' : '')."\"></div>";
                    $replace[] = $name;
                }

            }
        ?></ul>
    </div>
    <?php } ?>

    <div class="view">
        <div class="view-label">
            <span>Toujours visible</span>
        </div>
        <ul class="is-sortable field-list field-list-bottom"><?php
            foreach($type['typeFormLayout']['bottom'] as $f){

                $name   = $f['field'];
                $e      = $fieldId[$name];

                if(is_array($e)){
                    fieldTrace($app, $data, $e, $f);
                }else{
                    echo "<div id=\"replace-".$name."\" class=\"replace".(($f['close']) ? ' closed' : '')."\"></div>";
                    $replace[] = $name;
                }

            }
        ?></ul>
    </div>


</div>



<!-- ## ELEMENT DEPLACE AU BON ENDROIT A LA VOLEE ## -->
<ul style="display:none">

    <li id="contentName" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>

        <span class="<?php echo $app->formError('contentName', 'needToBeFilled'); ?> clearfix">
            <label>Nom</label>
            <div class="form"><input type="text" name="contentName" id="contentNameField" value="<?php echo $app->formValue($data['contentName'], $_POST['contentName']); ?>" size="100" autocomplete="off" style="width:99%;" /></div>
        </span>

        <div class="spacer">&nbsp;</div>

        <span class="<?php echo $app->formError('contentUrl', 'needToBeFilled') ?>">
            <label class="off">Url</label>
            <div class="form clearfix">
                <input type="text" name="contentUrl" id="urlField" value="<?php echo $app->formValue($data['contentUrl'], $_POST['contentUrl']); ?>" size="100" style="width:75%; float:left;" />
                <div style="float:left; margin-top:2px;">
                    <input type="checkbox" id="autogen" checked="checked" onclick="if(this.checked)urlCheck();" />
                    Générer automatiquent
                </div>
            </div>
        </span>
    </li>

    <li id="contentHeadMeta" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>

        <span class="clearfix">
            <label>Titre réf.</label>
            <div class="form"><input type="text" name="contentHeadTitle" value="<?php echo $app->formValue($data['contentHeadTitle'], $_POST['contentHeadTitle']); ?>" size="100" style="width:99%;" /></div>
        </span>
        <div class="spacer">&nbsp;</div>
        <span>
            <label class="off">Mots-clés réf.</label>
            <div class="form"><input type="text" name="contentMetaKeywords" value="<?php echo $app->formValue($data['contentMetaKeywords'], $_POST['contentMetaKeywords']); ?>" size="100" style="width:99%;" /></div>
        </span>
        <div class="spacer">&nbsp;</div>
        <span>
            <label class="off">Description réf.</label>
            <div class="form"><input type="text" name="contentMetaDescription" value="<?php echo $app->formValue($data['contentMetaDescription'], $_POST['contentMetaDescription']); ?>" size="100" style="width:99%;" /></div>
        </span>
    </li>

    <li id="contentSee" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label for="contentSeeBx">Visibilité</label>
        <div class="form" style="padding-top:3px;">
            <input type="checkbox" name="contentSee" id="contentSeeBx" value="1" <?php if($app->formValue($data['contentSee'], $_POST['contentSee'])) echo "checked"; ?> />
            Indique que ce document est visible sur le site
        </div>
    </li>

    <li id="contentRef" class="clearfix form-item <?php echo $app->formError('contentRef', 'needToBeFilled') ?>">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Référence</label>
        <div class="form"><input type="text" name="contentRef" value="<?php echo $app->formValue($data['contentRef'], $_POST['contentRef']); ?>" size="100" style="width:99%;" /></div>
    </li>

    <li id="contentWeight" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Poids</label>
        <div class="form"><input type="text" name="contentWeight" value="<?php echo $app->formValue($data['contentWeight'], $_POST['contentWeight']); ?>" size="8" /> en gramme</div>
    </li>

    <li id="contentCarriage" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Livraison</label>
        <div class="form"><select name="id_carriage"><?php
            $carriage = $app->apiLoad('business')->businessCarriageGet();
            foreach($carriage as $e){
                $sel = ($e['id_carriage'] == $app->formValue($data['id_carriage'], $_POST['id_carriage'])) ? ' selected' : NULL;
                echo "<option value=\"".$e['id_carriage']."\"".$sel.">".$e['carriageName']."</option>";
            }
        ?></select></div>
    </li>

    <li id="contentStock" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Stock</label>
        <div class="form">
            <input type="text" name="contentStock" value="<?php echo $app->formValue($data['contentStock'], $_POST['contentStock']); ?>" size="8" />
            <input type="checkbox" name="contentStockNeg" value="1" <?php if($app->formValue($data['contentStockNeg'], $_POST['contentStockNeg'])) echo "checked" ?> /> Autoriser le stock négatif
        </div>
    </li>

    <li id="contentTemplate" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Mise en page</label>
        <div class="form"><?php
        
            echo $app->apiLoad('template')->templateSelector(array(
                'name'      => 'contentTemplate',
                'value'     => $app->formValue($data['contentTemplate'], $_POST['contentTemplate']),
                'empty'     => true,
                'emptyText' => 'Utiliser la mise en page par défaut'
            ));
            
            if(sizeof($opt['options']) > 0){
            
                echo "<div style=\"margin-top:5px;\">";
                foreach($opt['options'] as $opt_){
                    echo $opt_['name']."<br />";
                    echo $app->apiLoad('field')->fieldForm(
                        // ID
                        $opt_['opt-'.$opt_['key']],
                        // Value
                        $app->formValue($data['contentTemplateEnv'][$opt_['key']], $_POST['templateEnv'][$opt_['key']]),
                        // Opt
                        array(
                            'style' => $opt_['style'],
                            'name'  => 'templateEnv['.$opt_['key'].']',
                            'field' => array(
                                'fieldType'     => $opt_['type'],
                                'fieldChoices'  => $opt_['choice'] 
                            )
                        )
                    );
                    echo "<br />";
                }
                echo "</div>";
            } ?>

        </div>
    </li>

    <li id="contentComment" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Commentaires</label>
        <div class="form">
        <select name="contentComment"><?php
            $str = array(
                ''      => '',
                'ALL'   => 'Tout le monde',
                'USER'  => 'Uniquement les membres'
            );
            foreach($str as $k => $e){
                $sel = ($app->formValue($data['contentComment'], $_POST['contentComment']) == $k) ? ' selected' : NULL;
                echo "<option value=\"".$k."\"".$sel.">".$e."</option>\n";
            }
        ?></select>
        et note
        <select name="contentRate"><?php
            $str = array(
                ''      => '',
                'ALL'   => 'Tout le monde',
                'USER'  => 'Uniquement les membres'
            );
            foreach($str as $k => $e){
                $sel = ($app->formValue($data['contentRate'], $_POST['contentRate']) == $k) ? ' selected' : NULL;
                echo "<option value=\"".$k."\"".$sel.">".$e."</option>\n";
            }
        ?></select>
            <?php if($data['id_content'] > 0) echo "(note actuelle : ".$data['contentRateAvg'].")"; ?>
            <input type="checkbox" name="resetRating" value="1" /> Remettre à zero les notes.
        </div>
    </li>

    <li id="contentDate" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Dates</label>
        <div class="form">
            <table>
                <tr>
                    <td width="80 ">Creation</td>
                    <td width="200">
                        <?php
                            $v = $app->formValue($data['contentDateCreation'], $_POST['contentDateCreation']);
                            if(!is_array($v)) $v = explode(' ', $v);
                        ?>
                        <input type="text" name="contentDateCreation[0]" id="contentDateCreation" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
                        <input type="text" name="contentDateCreation[1]"                          value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
                    </td>
                    <td width="50">Debut</td>
                    <td width="200">
                        <?php $v = $app->formValue($data['contentDateStart'], $_POST['contentDateStart']); ?>
                        <input type="checkbox" name="contentDateStartDo" id="contentDateStartDo" value="1" <?php if($v != '') echo "checked" ?> />
                        <input type="text" name="contentDateStart" id="contentDateStart" value="<?php echo $v ?>" size="12" style="text-align:center;" />
                    </td>
                </tr>
                <tr>
                    <td>Mise a jour</td>
                    <td>
                        <?php
                            $v = $app->formValue($data['contentDateUpdate'], $_POST['contentDateUpdate']);
                            if(!is_array($v)) $v = explode(' ', $v);
                        ?>
                        <input type="text" name="contentDateUpdate[0]" id="contentDateUpdate" value="<?php echo $v[0] ?>" size="12" style="text-align:center;" />
                        <input type="text" name="contentDateUpdate[1]"                        value="<?php echo $v[1] ?>" size="7"  style="text-align:center;" />
                    <td>Fin</td>
                    <td>
                        <?php $v = $app->formValue($data['contentDateEnd'], $_POST['contentDateEnd']); ?>
                        <input type="checkbox" name="contentDateEndDo" id="contentDateEndDo" value="1" <?php if($v != '') echo "checked" ?> />
                        <input type="text" name="contentDateEnd" id="contentDateEnd" value="<?php echo $v ?>" size="12" style="text-align:center;" />
                    </td>
                </tr>
            </table>
        </div>
    </li>

    <?php if($useCount > 0){ ?>
    <li id="contentAssociation" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Liaisons</label>
        <div class="form">

            <?php if($type['use_chapter']){ ?>
            <div style="width:<?php echo $usePercent ?>%;" class="panelItem">
                <span class="panelLabel">
                    Arborescence &nbsp; &nbsp;
                    <a href="javascript:sizer('id_chapter',100,100)">plus</a> / <a href="javascript:sizer('id_chapter',100,-100)">moins</a>
                </span>
                <div class="panelBody" style="width:95%;"><?php echo 
                    $app->apiLoad('chapter')->chapterSelector(array(
                        'name'      => 'id_chapter[]',
                        'id'        => 'id_chapter',
                        'multi'     => true,
                        'style'     => 'width:100%; height:200px',
                        'profile'   => true,
                        'value'     => $app->formValue($data['id_chapter'], $_POST['id_chapter'])
                    ));
                ?></div>
            </div>
            <?php } if($type['use_category']){ ?>
            <div style="width:<?php echo $usePercent ?>%;" class="panelItem">
                <span class="panelLabel">
                    Catégorie &nbsp; &nbsp;
                    <a href="javascript:sizer('id_category',100,100)">plus</a> / <a href="javascript:sizer('id_category',100,-100)">moins</a>
                </span>
                <div class="panelBody" style="width:95%;"><?php echo
                    $app->apiLoad('category')->categorySelector(array(
                        'name'      => 'id_category[]',
                        'id'        => 'id_category',
                        'multi'     => true,
                        'style'     => 'width:100%; height:200px',
                        'profile'   => true,
                        'language'  => 'fr',
                        'value'     => $app->formValue($data['id_category'], $_POST['id_category'])
                    ));
                ?></div>
            </div>
            <?php } if($type['use_group'] && !$type['is_business']){ ?>
            <div style="width:<?php echo $usePercent ?>%;" class="panelItem">
                <span class="panelLabel">
                    Groupes &nbsp; &nbsp;
                    <a href="javascript:sizer('id_group',100,100)">plus</a> / <a href="javascript:sizer('id_group',100,-100)">moins</a>
                </span>
                <div class="panelBody" style="width:95%;"><?php echo 
                    $app->apiLoad('user')->userGroupSelector(array(
                        'name'      => 'id_group[]',
                        'id'        => 'id_group',
                        'multi'     => true,
                        'style'     => 'width:100%; height:200px',
                        'profile'   => true,
                        'value'     => $app->formValue($data['id_group'], $_POST['id_group'])
                    ));
                ?></div>
            </div>
            <?php } if($type['use_search']){ ?>
            <div style="width:<?php echo $usePercent ?>%;" class="panelItem">
                <span class="panelLabel">
                    Groupes intelligents &nbsp; &nbsp;
                    <a href="javascript:sizer('id_search',100,100)">plus</a> / <a href="javascript:sizer('id_search',100,-100)">moins</a>
                </span>
                <div class="panelBody" style="width:95%;"><?php echo 
                    $app->searchSelector(array(
                        'name'      => 'id_search[]',
                        'id'        => 'id_search',
                        'searchType'=> 'user',
                        'multi'     => true,
                        'style'     => 'width:100%; height:200px',
                        'value'     => $app->formValue($data['id_search'], $_POST['id_search'])
                    ));
                ?></div>
            </div>
            <?php } if($type['use_socialforum']){ ?>
            <div style="width:<?php echo $usePercent ?>%;" class="panelItem">
                <span class="panelLabel">
                    Forum (Social) &nbsp; &nbsp;
                    <a href="javascript:sizer('id_socialforum',100,100)">plus</a> / <a href="javascript:sizer('id_socialforum',100,-100)">moins</a>
                </span>
                <div class="panelBody" style="width:95%;"><?php echo 
                    $app->apiLoad('socialForum')->socialForumSelector(array(
                        'name'      => 'id_socialforum[]',
                        'id'        => 'id_socialforum',
                        'multi'     => true,
                        'style'     => 'width:100%; height:200px',
                        'value'     => $app->formValue($data['id_socialforum'], $_POST['id_socialforum'])
                    ));
                ?></div>
            <?php } ?>

        </div>
    </li>
    <?php } ?>

    <?php if($type['is_business']){ ?>
    <input type="hidden" name="useBusiness" value="1" />
    <li id="contentGroup" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Cibles</label>
        <div class="form">
            <?php $groups = $app->apiLoad('content')->contentGroupGet($data['id_content'], $type['id_type']); ?>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="listing">
                <thead>
                    <tr>
                        <th width="200">Nom</th>
                        <th width="75" style="text-align:center;">Visible</th>
                        <th width="75" style="text-align:center;">Achetable</th>
                        <th width="75" style="text-align:right;">Prix HT</th>
                        <th width="75" style="text-align:right;">Prix TTC</th>
                        <th width="75" style="text-align:right;">Prix Normal</th>
                        <th style="padding-left:50px;">Comment</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach($groups as $id_group => $e){
                        $disabled = ($e['is_view']) ? NULL : "disabled=\"disabled\"";
                ?>
                <tr id="line-<?php echo $id_group ?>">
                    <td>
                        <span style="padding-left:<?php echo ($e['level']+1) * 10 ?>px;"><?php echo $e['groupName'] ?></span>
                        <input type="hidden" name="group[<?php echo $id_group ?>][1]" value="1" />
                    </td>
                    <td style="text-align:center"><input type="checkbox"    name="group[<?php echo $id_group ?>][is_view]"              value="1" <?php if($e['is_view']) echo  " checked"; ?> class="cb-view" onClick="toggleLine(<?php echo $id_group ?>,this)" accept="<?php echo $id_group ?>" /></td>
                    <td style="text-align:center"><input type="checkbox"    name="group[<?php echo $id_group ?>][is_buy]"               value="1" <?php if($e['is_buy'])  echo  " checked"; ?> class="cb-buy is-toggle" <?php echo $disabled ?> /></td>
                    <td style="text-align:right;"><input type="text"        name="group[<?php echo $id_group ?>][contentPrice]"         value="<?php echo $app->formValue($e['contentPrice'],           $_POST['group'][$id_group]['contentPrice']) ?>" size="6" class="fl-ht is-toggle" <?php echo $disabled ?> /></td>
                    <td style="text-align:right;"><input type="text"        name="group[<?php echo $id_group ?>][contentPriceTax]"      value="<?php echo $app->formValue($e['contentPriceTax'],        $_POST['group'][$id_group]['contentPriceTax']) ?>" size="6" class="fl-tt is-toggle" <?php echo $disabled ?> /></td>
                    <td style="text-align:right;"><input type="text"        name="group[<?php echo $id_group ?>][contentPriceNormal]"   value="<?php echo $app->formValue($e['contentPriceNormal'],     $_POST['group'][$id_group]['contentPriceNormal']) ?>" size="6" class="fl-no is-toggle" <?php echo $disabled ?> /></td>
                    <td style="padding-left:50px;"><input type="text"       name="group[<?php echo $id_group ?>][contentPriceComment]"  value="<?php echo $app->formValue($e['contentPriceComment'],    $_POST['group'][$id_group]['contentPriceComment']) ?>" class="fl-co is-toggle" <?php echo $disabled ?> /></td>
                </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td style="text-align:center">
                            <a href="javascript:chk('view',false)"><img src="ressource/img/boxcheck.png" /></a>
                            <a href="javascript:chk('view',true)"><img src="ressource/img/boxchecked.png" /></a>
                            <a href="javascript:permu('view')"><img src="ressource/img/boxcheckreverse.png" /></a>
                        </td>
                        <td style="text-align:center">
                            <a href="javascript:chk('buy',false)"><img src="ressource/img/boxcheck.png" /></a>
                            <a href="javascript:chk('buy',true)"><img src="ressource/img/boxchecked.png" /></a>
                            <a href="javascript:permu('buy')"><img src="ressource/img/boxcheckreverse.png" /></a>
                        </td>
                        <td style="text-align:right"><a href="javascript:dupli('ht')"><img src="ressource/img/bigt.png" /></a></td>
                        <td style="text-align:right"><a href="javascript:dupli('tt')"><img src="ressource/img/bigt.png" /></a></td>
                        <td style="text-align:right"><a href="javascript:dupli('no')"><img src="ressource/img/bigt.png" /></a></td>
                        <td style="padding-left:50px"><a href="javascript:dupli('co')"><img src="ressource/img/bigt.png" /></a></td>
                    </tr>
                </tfoot>
            </table>

                                            <span class="panelLabel">
                                                Shop &nbsp; &nbsp;
                                                <a href="javascript:sizer('id_shop',100,100)">plus</a> / <a href="javascript:sizer('id_shop',100,-100)">moins</a>
                                            </span>
                                            <div class="panelBody" style="width:95%;"><?php echo 
                                                $app->apiLoad('shop')->shopSelector(array(
                                                    'name'      => 'id_shop[]',
                                                    'id'        => 'id_shop',
                                                    'multi'     => true,
                                                    'style'     => 'width:100%; height:200px',
                                                    'profile'   => true,
                                                    'value'     => $app->formValue($data['id_shop'], $_POST['id_shop'])
                                                ));
                                            ?></div>    
        </div>
    </li>
    <?php } ?>

    <li id="contentMediaBox" class="clearfix form-item">
        <div class="hand">&nbsp;</div>
        <div class="toggle">&nbsp;</div>
        <label>Media</label>
        <div class="form"><?php echo
            $app->apiLoad('field')->fieldForm(
                NULL,
                $app->formValue($data['contentMedia'], $_POST['contentMedia']),
                array(
                    'name'  => 'contentMedia',
                    'id'    => 'contentMedia',
                    'style' => 'width:100%',
                    'field' => array(
                        'fieldType' => 'media'
                    ),
                )
            );
        ?></div>
    </li>

</ul>

</form>

<script type="text/javascript">

    doMove          = false;
    useEditor       = true;
    replace         = <?php echo json_encode($replace); ?>;
    textarea        = "<?php echo @implode(',', $GLOBALS['textarea']) ?>";
    MceStyleFormats = [<?php echo @file_get_contents(USER.'/config/tinymceStyleFormats.php') ?>];

    function sizer(e, min, add){
        if($(e)){
            hauteur = $(e).getStyle('height').toInt();
            if(hauteur + add >= min) $(e).setStyle('height', hauteur+add);
        }
    }

    function toggleLine(id,trigger){
        list = $('line-'+id).getElements('.is-toggle');
        v = (trigger.checked) ? false : true;
        list.each(function(e){
            if(e.hasClass('is-toggle')){
                e.set('disabled', v);
            }
        })
    }

    function dupli(id){
        lst = $$('.fl-'+id); 
        lst.each(function(me, i){
            if(me.value == '') me.value = lst[0].value;
        });
    }
    
    function chk(id, state, doFnc){
        $$('.cb-'+id).each(function(me){
            me.set('checked', ((state) ? 'checked' : ''));
            toggleLine(me.accept, me);
        });
    }

    function empty(id){
        $$('.'+id).each(function(me, i){
            me.value = '';
        });
    }

    function permu(id){
        $$('.cb-'+id).each(function(me, i){
            me.checked = (me.checked) ? false : true;
        });
    }

    window.addEvent('domready', function(){
        boot();
        openView(0,0);
    });

    MooTools.lang.setLanguage("fr-FR");

        calCrea = new CalendarEightysix('contentDateCreation', {
            'startMonday':true, 'alignX':'middle', 'alignY':'top', 'format':'%Y-%m-%d'
        });
    
        calUpd = new CalendarEightysix('contentDateUpdate', {
            'startMonday':true, 'alignX':'middle', 'alignY':'bottom', 'format':'%Y-%m-%d'
        });
    
        calStr = new CalendarEightysix('contentDateStart', {
            'startMonday':true, 'alignX':'middle', 'alignY':'top', 'format':'%Y-%m-%d'
        });
        if(!$('contentDateStartDo').checked) $('contentDateStart').value = '';
        $('contentDateStartDo').addEvent('click', function(){
            $('contentDateStart').value = ($('contentDateStartDo').checked) ? '<?php echo date("Y-m-d") ?>' : '';
        });
    
        calEnd = new CalendarEightysix('contentDateEnd', {
            'startMonday':true, 'alignX':'middle', 'alignY':'bottom', 'format':'%Y-%m-%d'
        });
        if(!$('contentDateEndDo').checked) $('contentDateEnd').value = '';
        $('contentDateEndDo').addEvent('click', function(){
            $('contentDateEnd').value = ($('contentDateEndDo').checked) ? '<?php echo date("Y-m-d") ?>' : '';
        });

    function version(sel){
        var next = '';
        var id   = sel.options[sel.selectedIndex].value;

        if(id > 0) next = '&reloadFromVersion='+id;

        if(next != ''){
            if(confirm("LOADER L'ARCHIVE ?")){
                document.location = 'product.picker.data.php?id_content=<?php echo $_REQUEST['id_content'] ?>'+next;
            }
        }else{
            if(confirm("LOADER LA DERNIERE VERSION ?")){
                document.location = 'product.picker.data.php?id_content=<?php echo $_REQUEST['id_content'] ?>';
            }
        }
    }

    function insertProduct(id_content){
        parent.opener.document.getElementById('id_content').value=id_content;
        parent.opener.document.getElementById('form-cart').submit();
        
        window.close();      
    }

</script>

<?php } ?>

</div></body></html>