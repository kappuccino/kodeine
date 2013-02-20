<?php

	if(!defined('COREINC')) die('Direct access not allowed');
	$cmd = $app->dbMulti("SELECT * FROM k_businesscart WHERE is_admin=1 AND is_cmd=0 ORDER BY id_cart ASC");

?><!DOCTYPE html>
<html lang="fr">
<head>
	<?php include(COREINC.'/head.php'); ?>
</head>
<body>
	
	<div class="pbg">
		
		<!-- BANDEAU TOP - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - --> 
		
		<div class="top">
			<div a href="../" class="logo">Logo</div>
			<div class="pathway clearfix">
				<h1>
					<a href="../business/">Business</a> &raquo;
					<a href="../business/data-index">Commandes en cours</a>
				</h1>
				<!--<div class="types clearfix">
					<div class="button button-blue"><a href="next.php">Lien #1</a></div>
					<div class="button button-blue"><a href="#">type</a></div>
					<div class="button button-blue selected"><a href="#">type</a></div>
					<div class="button button-blue"><a href="#">type</a></div>
				</div>-->
			</div>
		</div>
	</div>




<div class="bocontainer">
	<div class="row-fluid">
	<?php include('lib/menu.php'); ?>

	<div class="app">
	
	<?php
	    if(sizeof($cmd) > 0){
	?>
	<form action="data-index" id="listing" name="listing" method="post">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="listing table table-striped">
	    <thead>
	        <tr>
	            <th width="30" class="icone"><i class="icon-remove icon-white"></i></th>
	            <th>Date</th>
	            <th>Nom de la commande</th>
	            <th>Client</th>
	        </tr>
	    </thead>
	    <tbody>
	<?php        
	        foreach($cmd as $c){ $countchk++;
	?>
	        <tr>
	            <td class="check-red"><input type="checkbox" name="del[]" value="<?php echo $c['id_cart'] ?>" id="chkdel-<?php echo $countchk ?>" class="cb chk" />
	            	<label for="chkdel-<?php echo $countchk ?>"><i class="icon-remove icon-white"></i></label>
	            </td>
	            <td>
	                <a href="data?id_cart=<?php echo $c['id_cart']; ?>"><?php echo $c['cartDateCreate']; ?></a>
	            </td>
	            <td>
	                <a href="data?id_cart=<?php echo $c['id_cart']; ?>"><?php echo $c['cartName']; ?></a>
	            </td>
	            <td>
	                <a href="data?id_cart=<?php echo $c['id_cart']; ?>"><?php echo $c['cartDeliveryName']; ?></a>
	            </td>
	        </tr>
	<?php
	        }
	?>
	    </tbody>
	    <tfoot>
	        <tr>
	            <td height="25" class="check check-red"><input class="chk" type="checkbox" id="chkall" onchange="cbchange($(this))" />
	            	<label for="chkall"><i class="icon-remove icon-white"></i></label>
	            </td>
	            <td coslpan="3"><a href="#" onClick="apply();" class="button button-red">Supprimer la s&eacute;l&eacute;ction</a></td>
	        </tr>
	    </tfoot>
	</table>
	</form>
	<?php include(COREINC.'/end.php'); ?>
	<?php
	    }else {
	        echo '<p>Auncune commande en cours...</p>';
	    }
	?>
	
	<script>
	
	    function apply(){
	        
	        if(confirm('Etes vous sur de vouloir supprimer les commandes sélectionées ?')){
	            $('#listing').submit();
	        }
	    }
	
	</script>
	
	</div>
</div>
</div>
</body></html>