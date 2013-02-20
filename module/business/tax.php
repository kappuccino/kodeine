<?php

	if(!defined('COREINC')) die('Direct access not allowed');

	// Remove
    if(sizeof($_POST['del']) > 0){
        foreach($_POST['del'] as $e){
            $app->dbQuery("DELETE FROM k_businesstax WHERE id_tax=".$e);
        }
        $app->go('tax');
    }else
    if($_POST['action']){
        $do = true;

        $def['k_businesstax'] = array(
            'tax'     => array('value' => $_POST['tax'])
        );

        if(!$app->formValidation($def)) $do = false;

        if($do){
            $result = $app->apiLoad('business')->businessTaxSet($_POST['id_tax'], $def);

            $message = ($result)
                ? 'OK: Enregistrement dans la base'
                : 'KO: Une erreur est survenue, APP:<br />'.$app->db_error;

        }else{
            $message = 'KO: Validation failed';
        }

    }

    if($_REQUEST['id_tax'] != NULL){
        $data = $app->apiLoad('business')->businessTaxGet(array(
            'id_tax'    => $_REQUEST['id_tax'],
            'debug'         => false
        ));
    }

    $tax = $app->apiLoad('business')->businessTaxGet();
//$app->pre($tax);

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
	
	<div class="span6">
		<form action="tax" method="post" id="listing">
			<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing">
			    <thead>
			        <tr>
			            <th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
			            <th class="filter">
			            	<span>TVA %</span>
			            	<input type="text" id="filter" class="input-small" onkeyup="recherche($(this))" onkeydown="recherche($(this))" />
			            </th>
			        </tr>
			    </thead>
			    <tbody><?php
			    if(sizeof($tax) > 0){
			        foreach($tax as $e){ $countchk++ ?>
			        <tr class="<?php if($e['id_tax'] == $_REQUEST['id_tax']) echo "selected" ?>">
			            <td class="check check-red"><input id="chkdel-<?php echo $countchk ?>" type="checkbox" name="del[]" value="<?php echo $e['id_tax'] ?>" class="cb chk" /></td>
			            <td class="sniff" colspan="2"><a href="tax?id_tax=<?php echo $e['id_tax'] ?>"><?php echo $e['tax'] ?> %</a></td>
			        </tr>
			        <?php }
			    }else{ ?>
			        <tr>
			            <td colspan="2" style="font-weight:bold; padding-top:30px; padding-bottom:30px;" align="center">
			                Auncune donn&eacute;e
			            </td>
			        </tr>
			    <?php } ?>
			    </tbody>
			    <tfoot>
			        <tr>
			            <?php if(sizeof($tax) > 0){ ?>
			            <td class="check check-red" height="25"><input class="chk" id="chkdel-all" type="checkbox" onchange="cbchange($(this))" /></td>
			            <td><a href="#" onClick="apply();" class="btn btn-mini">Supprimer la s&eacute;l&eacute;ction</a></td>
			        </tr>
			        <?php }else{ ?>
			        <tr>
			            <td colspan="3">&nbsp;</td>
			        </tr>
			        <?php } ?>
			    </tfoot>
			</table>
		</form>
		
	</div>
	
	<div class="span6">
	    <?php
	        if($message != NULL){
	            list($class, $message) = $app->helperMessage($message);
	            echo "<div class=\"message message".ucfirst($class)."\">".$message."</div>";
	        }
	    ?>
	    
	    <form action="tax" method="post" id="data">
	    <input type="hidden" name="action" value="1" />
	    <input type="hidden" name="id_tax" value="<?php echo $data['id_tax'] ?>" />
	    
	    <table cellpadding="0" cellspacing="0" border="0" class="form">
	        <tr>
	            <td width="150">Taux de TVA %</td>
	            <td><input type="text" name="tax" value="<?php echo $app->formValue($data['tax'], $_POST['tax']); ?>" /></td>
	        </tr>
	        <tr>
	            <td></td>
	            <td>
	                <a href="javascript:$('#data').submit();" class="btn btn-mini">Enregistrer</a>
	                <a href="tax" class="btn btn-mini">Nouveau</a>
	            </td>
	        </tr>
	    </table>
	
	    </form>
	</div>
	
</div></div>

<?php include(COREINC.'/end.php'); ?>
<script>

    function apply(){
        if(confirm("SUPPRIMER ?")){
            $('#listing').submit();
        }
    }

</script>

</body>
</html>