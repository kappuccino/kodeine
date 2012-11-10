<?php
	$u = $this->apiLoad('user')->userGet(array(
		'id_user'	=> $this->user['id_user'],
		'useMedia'	=> true
	));
	
	if(sizeof($u['userMedia']['image']) > 0){
		$image	= $this->mediaUrlData(array(
			'url'	=> $u['userMedia']['image'][0]['url'],
			'mode'	=> 'square',
			'value'	=> 220
		));
		
		echo "<img ".$image['html']." />";
	}
	unset($image, $u);

?>
	<a href="/">Accueil</a><br /><br />
	
	<a href="/cart">Panier</a><br />
	<a href="/search">Recherche</a><br /><br />

	<a href="/newsletter/">Newsletter</a><br /><br />

	<a href="/contact">Contact (template de page)</a><br /><br />

	<a href="/survey/?id_survey=3&userMail=<?php echo $this->user['userMail'] ?>&autoSlot">Survey</a><br /><br />

	<a href="/user/index"><b>User</b></a>
		(<?php echo ($this->userIsLogged)
			? "<a href=\"/?logout\">Se d&eacute;connecter</a>"
			: "<a href=\"/user/login\">Se connecter</a>"
		?>)<br />

	<a href="/user/login">login</a><br />
	<a href="/user/lost">lost</a><br />
	<a href="/user/cmd">Mes commandes</a><br />
	<a href="/user/addressbook">Mon carnet d'adresses</a><br />
	<a href="/user/coupon">Mes coupons</a><br /><br />


	<a href="/social/"><b>Social</b></a><br />
	<a href="/social/post-my">Mes posts</a><br />
	<a href="/social/message">Message</a><br />
	<a href="/social/circle">Cercle</a><br />
	<a href="/social/activity">Activit&eacute;</a><br />
	<a href="/social/profile">Mon profil</a><?php if($this->userIsLogged) echo ' ('.$this->user['id_user'].')'; ?><br />
	<a href="/social/world">Tout le monde</a><br />
	<a href="/social/follow">Follow</a>
