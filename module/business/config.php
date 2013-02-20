<?php

	if(!defined('COREINC')) die('Direct access not allowed');

    if($_POST['action']){
        $do = true;

        $def['k_businessconfig'] = array(
            'configCustom'     => array('value' => $_POST['configCustom'])
        );

        if(!$app->formValidation($def)) $do = false;

        if($do){
            $result = $app->apiLoad('business')->businessConfigSet($_POST['configField'], $_POST['configKey'], $def);

            $message = ($result)
                ? 'OK: Enregistrement dans la base'
                : 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

        }else{
            $message = 'KO: Validation failed';
        }

    }

    if($_REQUEST['configField'] != NULL && $_REQUEST['configKey'] != NULL){
        $data = $app->apiLoad('business')->businessConfigGet(array(
            'configField'   => $_REQUEST['configField'],
            'configKey'     => $_REQUEST['configKey'],
            'debug'         => false
        ));
        $data = $data[0];
    }
    $configName = array(
                        'cartStatus'            => 'Statut de commande',
                        'cartDeliveryStatus'    => 'Etat de livraison',
                        'cartPayment'           => 'Mode de règlement',
                        );
    $config = $app->apiLoad('business')->businessConfigGet(array('order' => 'ORDER BY configField ASC'));

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

<div id="app"><div class="wrapper">

	<div style="float:left; width:500px; margin-right:20px;">
	<form action="config" method="post" id="listing">
	
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
	    <thead>
	        <tr>
	            <th>Champ</th>
	            <th>Clé</th>
	            <th>Libellé</th>
	        </tr>
	    </thead>
	    <tbody><?php
	    if(sizeof($config) > 0){
	        foreach($config as $c){ ?>
	        <tr class="<?php if($c['configField'] == $_REQUEST['configField'] && $c['configKey'] == $_REQUEST['configKey']) echo "selected" ?>">
	            <td class="sniff" ><a href="config?configField=<?php echo $c['configField'] ?>&configKey=<?php echo $c['configKey'] ?>"><?php echo $configName[$c['configField']] ?></a></td>
	            <td class="sniff" ><a href="config?configField=<?php echo $c['configField'] ?>&configKey=<?php echo $c['configKey'] ?>"><?php echo $c['configKey'] ?></a></td>
	            <td class="sniff" ><a href="config?configField=<?php echo $c['configField'] ?>&configKey=<?php echo $c['configKey'] ?>"><?php echo $c['configCustom'] ?></a></td>
	        </tr>
	        <?php }
	    }else{ ?>
	        <tr>
	            <td colspan="3" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
	                Auncune donn&eacute;e
	            </td>
	        </tr>
	    <?php } ?>
	    </tbody>
	</table>
	</form>
	</div>
	
	<div style="float:left; width:550px;">
	    <?php
	        if($message != NULL){
	            list($class, $message) = $app->helperMessage($message);
	            echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	        }
	    ?>
	    
	    <?php if(sizeof($data) > 0){ ?>
	    
	    <form action="config" method="post" id="data">
	    <input type="hidden" name="action" value="1" />
	    <input type="hidden" name="configField" value="<?php echo $data['configField'] ?>" />
	    <input type="hidden" name="configKey" value="<?php echo $data['configKey'] ?>" />
	    
	    <table cellpadding="3" border="0" width="600">
	        <tr>
	            <td width="150">Champ</td>
	            <td><?php echo $configName[$data['configField']] ?></td>
	        </tr>
	        <tr>
	            <td width="150">Clé</td>
	            <td><?php echo $data['configKey'] ?></td>
	        </tr>
	        <tr>
	            <td width="150">Libellé</td>
	            <td><input type="text" name="configCustom" value="<?php echo $app->formValue($data['configCustom'], $_POST['configCustom']); ?>" /></td>
	        </tr>
	        <tr>
	            <td></td>
	            <td>
	                <a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
	                <!--<a href="business.config.php" class="button rButton">Nouveau</a>-->
	            </td>
	        </tr>
	    </table>
	
	    </form>
	    <?php } ?>
	</div>

</div></div>


</body></html>