<?php

if(!defined('COREINC')) die('Direct access not allowed');

if($_POST['actionParam']){
    // pre
    $app->apiLoad('user')->userSearchCache(array(
        'debug'		=> false,
        'id_search'	=> $_POST['id_search'],
        'clean'		=> true
    ));

    // update
    $app->dbQuery("UPDATE k_search SET searchChain='".$_POST['searchChain']."', searchParam='".serialize($_POST['searchParam'])."' WHERE id_search=".$_POST['id_search']);

    // post
    $app->apiLoad('user')->userSearchCache(array(
        'debug'		=> false,
        'id_search'	=> $_POST['id_search']
    ));

}else
    if(sizeof($_POST['remove']) > 0){
        foreach($_POST['remove'] as $e){
            $app->dbQuery("DELETE FROM k_search WHERE id_search=".$e);
        }
    }else
        if($_POST['action']){
            $do = true;

            $def['k_search'] = array(
                'searchName' 	=> array('value' => $_POST['searchName'], 'check' => '.'),
                'searchType'	=> array('value' => $_POST['searchType'])
            );

            if(!$app->formValidation($def)) $do = false;

            if($do){
                $result = $app->searchSet($_POST['id_search'], $def);
                $message = ($result) ? 'OK' : 'PAS OK';
                if($result) $_REQUEST['id_search'] = $app->id_search;
            }else{
                $message = 'validation failed';
            }
        }

if($_REQUEST['id_search'] != NULL){
    $data = $app->searchGet(array(
        'id_search' 	=> $_REQUEST['id_search'],
    ));

    $title	= "Modification ".$data['searchName'];
}else{
    $title 	= "Nouvelle recherche";
}

$search = $app->searchGet();

?><!DOCTYPE html>
<html lang="fr">
<head>
    <?php include(COREINC.'/head.php'); ?>
    <style>
        .trigger { display: block; }
        .before-cond {
            margin-top: 10px;
            border-top: 1px solid #444;
            padding-top: 10px;
        }
        .foo-sel {
            margin: 0 10px 10px 10px;
        }
    </style>
</head>
<body>

<header><?php
    include(COREINC.'/top.php');
    include(__DIR__.'/ui/menu.php')
    ?></header>

<div id="app"><div class="wrapper">

    <div class="app">

        <div class="span5">
            <form action="search" method="post" id="listing">

                <table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
                    <thead>
                    <tr>
                        <th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
                        <th><b>Nom</b></th>
                        <th width="100">
                            <input type="text" class="field input-small nomargin" onkeyup="recherche($(this))" onkeydown="recherche($(this))" size="10" style="float:right;" />
                        </th>
                    </tr>
                    </thead>
                    <tbody><?php
                    $search = $app->searchGet(array('type' => 'user'));
                    //$app->pre($search);
                    if(sizeof($search) > 0){
                        foreach($search as $e){ $chkdel++; ?>
                        <tr class="<?php if($e['id_search'] == $_REQUEST['id_search']) echo "selected" ?>">
                            <td class="check-red"><input type="checkbox" name="remove[]" id="chkdel<?php echo $chkdel ?>" class="chk" value="<?php echo $e['id_search'] ?>" /></td>
                            <td class="sniff"><a href="search?id_search=<?php echo $e['id_search'] ?>"><?php echo $e['searchName'] ?></a></td>
                            <td align="right"><a href="search?id_search=<?php echo $e['id_search'] ?>&param">Paramètres</a></td>
                        </tr>
                            <?php }
                    }else{ ?>
                    <tr>
                        <td colspan="3" style="font-weight:bold; padding:30px 0px 30px 0px; text-align:center;">
                            Aucune recherche enregistré
                        </td>
                    </tr>
                        <? }
                    ?></tbody>
                    <tfoot>
                    <tr>
                        <td height="30"></td>
                        <td colspan="2"><?php
                            if(sizeof($search) > 0){ ?>
                                <a href="javascript:$('#listing').submit();" class="btn btn-mini">Supprimer</a>
                                <?php } ?></td>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>

        <div class="ee span12">

            <table border="1" width="100%" cellpadding="5" cellspacing="0" bordercolor="#ddd">
                <tr>
                    <th>&nbsp;</th>
                    <th>Champ 1</th>
                    <th>Opérateur</th>
                    <th>Champ 2 / Valeur</th>
                    <th>&nbsp;</th>
                </tr>
                <tr class="trsortable">
                    <td valign="top">
                        <select name="p1">
                            <option value=""></option>
                            <option value="">(</option>
                        </select>
                    </td>
                    <td valign="top">
                        <div id="f1" style="margin:5px 0 10px ;font-size: 1.2em;font-weight: bold;text-transform: uppercase;"></div>
                        <a href="" onclick="$('#sfield1').toggle();return false;">Sélectionner un champ / valeur</i></a>
                        <div id="sfield1" style="display: none;background: #eee;">
                            <ul style="padding: 5px;">
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfieldu1').toggle();return false;"><b>Utilisateur</b></a>
                                    <ul id="sfieldu1" style="display: none;">
                                        <li><a href="" onclick="$('#f1').html('Email');$('#sfield1').hide();return false;">Email</a></li>
                                        <li><a href="" onclick="$('#f1').html('Nom');$('#sfield1').hide();return false;">Nom</a></li>
                                        <li><a href="" onclick="$('#f1').html('Prénom');$('#sfield1').hide();return false;">Prénom</a></li>
                                    </ul>
                                </li>
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfield1ca2').toggle();return false;"><b>Carnet adresses</b></a>
                                    <ul id="sfield1ca2" style="display: none;">
                                        <li><a href="" onclick="$('#f1').html('Adresse > Livraison');$('#sfield1').hide();return false;">Livraison (booléen)</a></li>
                                        <li><a href="" onclick="$('#f1').html('Adresse > Facturation');$('#sfield1').hide();return false;">Facturation (booléen)</a></li>
                                        <li><a href="" onclick="$('#f1').html('Adresse > Ville');$('#sfield1').hide();return false;">Ville</a></li>
                                        <li><a href="" onclick="$('#f1').html('Adresse > Code Pays');$('#sfield1').hide();return false;">Code Pays</a></li>
                                        <li>
                                            <a href="" onclick="$('#sfield1c3').toggle();return false;"><b>Pays</b></a>
                                            <ul id="sfield1c3" style="display: none;">
                                                <li><a href="" onclick="$('#f1').html('Adresse > Pays > Nom');$('#sfield1').hide();return false;">Nom</a></li>
                                                <li><a href="" onclick="$('#f1').html('Adresse > Pays > Continent');$('#sfield1').hide();return false;">Continent</a></li>
                                                <li></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfield1c1').toggle();return false;"><b>Garage</b></a>
                                    <ul id="sfield1c1" style="display: none;">
                                        <li><a href="" onclick="$('#f1').html('Garage > Date création');$('#sfield1').hide();return false;">Date création</a></li>
                                        <li><a href="" onclick="$('#f1').html('Garage > Titre');$('#sfield1').hide();return false;">Titre</a></li>
                                        <li>
                                            <a href="" onclick="$('#sfield1c2').toggle();return false;"><b>Moto</b></a>
                                            <ul id="sfield1c2" style="display: none;">
                                                <li><a href="" onclick="$('#f1').html('Garage > Moto > Nom');$('#sfield1').hide();return false;">Nom</a></li>
                                                <li><a href="" onclick="$('#f1').html('Garage > Moto > Marque');$('#sfield1').hide();return false;">Marque</a></li>
                                                <li></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td valign="top">
                        <select name="op">
                            <option value="">=</option>
                            <option value="">></option>
                            <option value="">>=</option>
                            <option value=""><</option>
                            <option value=""><=</option>
                            <option value="">Contient</option>
                            <option value="">Commence par</option>
                            <option value="">Finit par</option>
                        </select>
                    </td>
                    <td valign="top">
                        <div id="f2" style="margin:5px 0 10px ;font-size: 1.2em;font-weight: bold;text-transform: uppercase;"></div>
                        <a href="" onclick="$('#sfield2').toggle();return false;"><i>Sélectionner un champ / valeur</i></a>
                        <div id="sfield2" style="display: none;background: #eee;">
                            <ul style="padding: 5px;">
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#f2').html('<input type=text>');$('#sfield2').hide();return false;"><b>Valeur</b></a>
                                </li>
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfieldu2').toggle();return false;"><b>Utilisateur</b></a>
                                    <ul id="sfieldu2" style="display: none;">
                                        <li><a href="" onclick="$('#f2').html('Email');$('#sfield2').hide();return false;">Email</a></li>
                                        <li><a href="" onclick="$('#f2').html('Nom');$('#sfield2').hide();return false;">Nom</a></li>
                                        <li><a href="" onclick="$('#f2').html('Prénom');$('#sfield2').hide();return false;">Prénom</a></li>
                                    </ul>
                                </li>
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfield2ca2').toggle();return false;"><b>Carnet adresses</b></a>
                                    <ul id="sfield2ca2" style="display: none;">
                                        <li><a href="" onclick="$('#f2').html('Adresse > Livraison');$('#sfield2').hide();return false;">Livraison (booléen)</a></li>
                                        <li><a href="" onclick="$('#f2').html('Adresse > Facturation');$('#sfield2').hide();return false;">Facturation (booléen)</a></li>
                                        <li><a href="" onclick="$('#f2').html('Adresse > Ville');$('#sfield2').hide();return false;">Ville</a></li>
                                        <li><a href="" onclick="$('#f2').html('Adresse > Code Pays');$('#sfield2').hide();return false;">Code Pays</a></li>
                                        <li>
                                            <a href="" onclick="$('#sfield2c3').toggle();return false;"><b>Pays</b></a>
                                            <ul id="sfield2c3" style="display: none;">
                                                <li><a href="" onclick="$('#f2').html('Adresse > Pays > Nom');$('#sfield2').hide();return false;">Nom</a></li>
                                                <li><a href="" onclick="$('#f2').html('Adresse > Pays > Continent');$('#sfield2').hide();return false;">Continent</a></li>
                                                <li></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li style="margin: 10px 0;">
                                    <a href="" onclick="$('#sfield2c1').toggle();return false;"><b>Garage</b></a>
                                    <ul id="sfield2c1" style="display: none;">
                                        <li><a href="" onclick="$('#f2').html('Garage > Date création');$('#sfield2').hide();return false;">Date création</a></li>
                                        <li><a href="" onclick="$('#f2').html('Garage > Titre');$('#sfield2').hide();return false;">Titre</a></li>
                                        <li>
                                            <a href="" onclick="$('#sfield2c2').toggle();return false;"><b>Moto</b></a>
                                            <ul id="sfield2c2" style="display: none;">
                                                <li><a href="" onclick="$('#f2').html('Garage > Moto > Nom');$('#sfield2').hide();return false;">Nom</a></li>
                                                <li><a href="" onclick="$('#f2').html('Garage > Moto > Marque');$('#sfield2').hide();return false;">Marque</a></li>
                                                <li></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td valign="top">
                        <select name="p2">
                            <option value=""></option>
                            <option value="">OU</option>
                            <option value="">ET</option>
                            <option value="">)</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</div></div>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/search.js"></script>

<script>
    $(function() {

        $('.trsortable').sortable();
    });
</script>

</div></body></html>