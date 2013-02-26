<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	$uploaddir  = USER.'/temp';
    $file       = '/user.tmp';
    
    //die($app->pre($_POST));
    
    if($_POST['action'] && $_FILES['upTemplate']['tmp_name'] != NULL){

        umask(0);
        
        if(!file_exists($uploaddir)){
            mkdir($uploaddir, 0755, true);
        }

        # Si le fichier est bien deplacé dans le bon dossier
        if(@move_uploaded_file($_FILES['upTemplate']['tmp_name'], $uploaddir.'/'.$file)){
            $_POST['myFile'] = $uploaddir.'/'.$file;
            $message = "Le fichier est sur le serveur, vous allez &ecirc;tre guid&eacute; pour les &eacute;tapes suivantes.";
        }else{
            $type    = 'error'; 
            $message = "Echec de la mise en ligne de la base utilisateur, verifier que le dossier <i>/module/custom</i> existe et que Kappuccino poss&egrave;de bien les droits d'ecriture pour celui ci (755)";
        }
    }

    if($_POST['myFile'] != NULL && !file_exists($_POST['myFile'])){
        $type       = 'error';
        $message    = 'Le fichier d\'import n\'est plus accessible';
        unset($_POST['myFile']);
    }
    
    $fields = array(
        'addressbookTitle'              => _('Adress title'),
        'addressbookCivility'           => _('Civility'),
        'addressbookLastName'           => _('Name'),
        'addressbookFirstName'          => _('Last name'),
        'addressbookEmail'              => _('Email'),
        'addressbookCompanyName'        => _('Company'),
        'addressbookCompanyFonction'    => _('Fonction'),
        'addressbookAddresse1'          => _('Adress 1'),
        'addressbookAddresse2'          => _('Adress 2'),
        'addressbookAddresse3'          => _('Adress 3'),
        'addressbookCityCode'           => _('Zip Code'),
        'addressbookCityName'           => _('City'),
        'addressbookCountryCode'        => _('Country'),
        'addressbookStateName'          => _('State'),
        'addressbookPhone1'             => _('Phone 1'),
        'addressbookPhone2'             => _('Phone 2'),
        'addressbookTVAIntra'           => _('TVA Intra')
    );
	
    foreach($fields as $k=>$v){
        $field[$k] = $v;
    }
?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
	
<header><?php
	include(COREINC.'/top.php');
	include(__DIR__.'/ui/menu.php')
?></header>
	
<div id="app">
	<div class="wrapper">

		<div class="alert" id="message" style="display:<?php if($message == '') echo 'none' ?>"><?php echo $message ?>&nbsp;</div>
		<div class="alert alert-error" id="error"   style="display:none;"  >Erreur</div>
		<div class="alert alert-error"   id="doublon" style="display:none;"  >Doublon</div>
		
		<?php if($_POST['myFile'] != NULL){ ?>
		<form action="import-book" method="POST" id="formulaire">
		    <input type="hidden" name="myFile" value="<?php echo $_POST['myFile'] ?>" />
		    
		        <?php
		            $return = $app->apiLoad('user')->userImportAddressBookCSV($_POST['myFile'], $_POST);
		            
		            if(!is_array($return)){
		                $app->pre($return);
		            }else{      
		                list($step, $data) = $return;
		            #   $k->pre($step, $data, $error);
		    
		                /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
		                 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
		                function body($data){
		                    echo "<tbody>";
		                    foreach(array_splice($data['lignes'], 0, 10) as $ligne){
		                        if(trim($ligne) != ''){
		                            echo "<tr>";
		                                foreach(explode($data['sepColonne'], $ligne) as $colonne){
		                                    echo "<td>".$colonne."</td>";
		                                }
		                            echo "</tr>";
		                        }
		                    }
		                    echo "</tbody>";
		                }
		    
		                /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - 
		                 - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
		    
		                function menu($index, $label, $me){
		                
		                    $out  =
		                    "<select name=\"headers[".$index."]\">".
		                        "<option value=\"\">Ne pas prendre en compte</option>".
		                        "<optgroup label=\"Champs obligatoire\">";
		                            foreach(array('id_user' => 'ID Utilisateur (unique)', 'userMail' => 'Email (login)') as $field => $e){
		                                $sel  = ($field == $me) ? ' selected' : NULL;
		                                $out .= "<option value=\"".$field."\"".$sel.">".$e."</option>";
		                            }
		                        $out .= "</optgroup><optgroup label=\"Carnet d'adresses\">";
		                            foreach($label as $k=>$e){
		                                $sel  = ($k == $me) ? ' selected' : NULL;
		                                $out .= "<option value=\"label-".$k."\"".$sel.">".$e."</option>";
		                            }
		                        $out .= "</optgroup>".
		                    "</select>";
		                
		                    return $out;
		                }
		    
		    
		    
		    
		                switch($step){
		                    case 'needHeaders' :
		                        echo "<div class=\"step\">Etape 2 : Associer chaque colonne au champs utilisateur de Kappuccino</div>";
		                        if(sizeof($data['lignes']) > 10){
		                            echo "<p>Le fichier contient ".sizeof($data['lignes'])." lignes, les 10 premieres lignes seulement sont affich&eacute;es</p>";
		                        }
		    
		                        echo "<table cellspacing=\"0\" class=\"listing\" border=\"0\"><thead><tr>";
		                        foreach($data['colonnes'] as $index => $colonne){
		                            echo "<th>".menu($index, $field, $_POST['headers'][$index])."</th>";
		                        }
		                        echo "</tr></thead>";
		                        body($data);
		                        echo "</table>".
		    
		                        "<p><input type=\"submit\" value=\"Continuer\" /></p>";
		    
		                    break;
		                
		                    case 'needID' : 
		                        echo "<div class=\"step\">Etape 3 : Pour terminer l'import vous devez completer les options suivantes</div>";
		                        if(sizeof($data['lignes']) > 10){
		                            echo "<p>Le fichier contient ".sizeof($data['lignes'])." lignes, les 10 premieres lignes seulement sont affich&eacute;es</p>";
		                        }
		                        echo
		                        "<table cellspacing=\"0\" class=\"listing\" border=\"1\">".
		                        "<thead>".
		                            "<tr>";
		                                foreach($data['colonnes'] as $index => $colonne){
		                                    echo "<th><b>";
											
		                                    if($_POST['headers'][$index] != NULL){
		                                        echo $_POST['headers'][$index];
		                                    }else{
		                                        echo "-";
		                                    }
		                                    echo "</b></th>";
		                                }
		                            echo "</tr>";
		                        echo "</tr>";
		                        echo "</thead>";
		                        body($data);
		                        echo "</table>";
		
		                        echo "<br />".
		                        "<table cellspacing=\"0\" class=\"listing\" width=\"100%\" border=\"0\">".
		                        "<thead>".
		                            "<tr>".
		                                "<th colspan=\"2\">Compl&eacute;ment d'information</th>".
		                            "</tr>".
		                        "</thead>".
		                        "<tbody>".
		                            "<tr>".
		                                "<td width=\"400\">S&eacute;parateur de colonne</td>".
		                                "<td><input type=\"text\" name=\"sepColonne\" value=\";\" size=\"2\"/></td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td width=\"400\">La premi&egrave;re ligne du fichier ne contient pas d'utilisateur</td>".
		                                "<td><input type=\"checkbox\" name=\"removeFirst\" value=\"1\" /></td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td width=\"400\">Adresse livraison par d&eacute;faut</td>".
		                                "<td><input type=\"checkbox\" name=\"deliveryDefault\" value=\"1\" /></td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td width=\"400\">Adresse facturation par d&eacute;faut</td>".
		                                "<td><input type=\"checkbox\" name=\"billingDefault\" value=\"1\" /></td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td>Verifier si un utilisateur avec cet email existe dej&agrave;</td>".
		                                "<td><input type=\"checkbox\" name=\"checkDoublon\" value=\"1\" /></td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td>Si non pr&eacute;ciser, integrer ces utilisateurs au groupe</td><td>".
		                                $app->apiLoad('user')->userGroupSelector(array(
		                                    'one'   => true,
		                                    'name'  => 'id_group'
		                                ))."</td>".
		                            "</tr>".
		                            "<tr>".
		                                "<td>Si non pr&eacute;ciser, rendre actif ces utilisateurs</td>".
		                                "<td><input type=\"checkbox\" name=\"activate\" value=\"1\" /></td>".
		                            "</tr>".
		                            /*"<tr valign=\"top\">".
		                                "<td>Si non pr&eacute;ciser, ces membres recevront les mailings</td>".
		                                "<td>";
		                                foreach($app->apiLoad('newsletter')->newsletterTypeGet() as $n){
		                                    echo "<input type=\"checkbox\" name=\"id_newslettertype\" value=\"".$n['id_newslettertype']."\" /> ".$n['newsletterType']."<br />\n";
		                                }
		                                echo "</td>".
		                            "</tr>".*/
		                        "</tbody>".
		                        "</table>";
		                        
		                        foreach($_POST['headers'] as $indexH => $header){
		                            echo "<input type=\"hidden\" name=\"headers[".$indexH."]\" value=\"".$header."\" />\n";
		                        }
		    
		                        echo "<p><input type=\"button\" onClick=\"debuter();\" name=\"js\" value=\"importer\" /></p>";
		    
		                    break;
		                
		                    case 'imported' : 
		    
		                        echo ($data['count'] > 0)
		                            ? "<p>Import termin&eacute; : ".$data['count']." membre(s) import&eacute;(s) dans votre base utilisateur.</p>"
		                            : "<p>Aucun membre import&eacute;.</p>";
		    
		                        if(sizeof($data['doublon']) > 0){
		                            echo "<p>Des doublons ont &eacute;t&eacute; detect&eacute; pendant l'import email en doublon</p><p>";
		                            foreach($data['doublon'] as $doublon){
		                                echo "- ".$doublon['user']." (".$doublon['id_user'].")<br />";
		                            }
		                            echo "</p>";
		                        }
		    
		                        if(sizeof($data['error']) > 0){
		                            echo "<p>Des erreurs sont survenues pendant l'import. Impossible d'ajouter : </p></p>";
		                            foreach($data['error'] as $error){
		                                echo "- ".$error['user']." (".$error['id_user'].")<br />";
		                            }
		                            echo "</p>";
		                        }
		    
		                    break;
		                }
		            }
		        ?>
		        
		    </form>
		
		<?php }else{
		
		    if(file_exists($uploaddir.'/'.$file)) unlink($uploaddir.'/'.$file);
		?>
		    
		    <div class="step">Etape 1 : Choisir sur votre ordinateur le fichier contenant la liste des utilisateurs</div>
		    
		    <form action="import-book" method="post" enctype="multipart/form-data">
		        <input type="hidden" name="max_file_size" value="100000000" />
		        <input type="hidden" name="action" value="1" />
		        
		        <input type="file" name="upTemplate" /> <input type="submit" value="Envoyer ce fichier" />
		    </form> 
		    
		    <p><br /><br /></p>
		    <p>Les fichiers servant a importer massivement des utilisateurs doivent : 
		    <ul>
		        <li>Etre des fichiers texte (.TXT, .CSV)</li>
		        <li>Avoir un utilisateur par ligne</li>
		        <li>Les propri&eacute;t&eacute;s doivent etre sur des colonnes identifiables</li>
		    </ul>
		    <p>Kappuccino detecte automatiquement le s&eacute;parateur de ligne et de colonne en se basant sur les configuration les plus courantes.</p>
		    <p>Si votre fichier contient un caractere qui prot&eacute;ge les colonnes (Exemple : "valeur","valeur","valeur"), il sera   supprim&eacute;.</p>
		
		<?php } ?>
		    
		</div>
	</div>
</body>

<?php include(COREINC.'/end.php'); ?>
<script type="text/javascript" src="ui/js/addressbook.js"></script> 
</html>