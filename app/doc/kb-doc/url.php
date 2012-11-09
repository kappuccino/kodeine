<div class="fnName fnNameFirst">Les URLs</div>

<h3>Description</h3>
<div class="fnDesc">
	Les URLs sont une base fondamentale de Kodeine.<br />
	Leur structure permet de choisir quel composant est appelé
</div>

<h3>Exemple</h3>
<div class="fnArg">
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="150">Url</th>
			<th>Précisions</th>
		</tr>
		<tr>
			<td>/</td>
			<td>Racine (chapitre, langue et utilisateur par défaut)</td>
		</tr>
		<tr>
			<td>/xx/</td>
			<td>Racine en spécifiant la langue (xx = fr = us = ...)</td>
		</tr>
		<tr>
			<td>/xx/yyyy/</td>
			<td>Racine en spécifiant la langue et le module (yyyy = content = user = newsletter)</td>
		</tr>
		<tr>
			<td>/xx/yyyy/zzz</td>
			<td>Une page sur le site avec une langue, un module et un fichier (zzzz = index = cart = login)</td>
		</tr>
		<tr>
			<td>/xx/aaaa/bbb/yyyy/zzz.html</td>
			<td>Identique à la règle précédente, mais spécifit un chapitre en plus (utilisé que pour l'Url)</td>
		</tr>
	</table>
</div>
<h3>Exemple</h3>
<pre class="fnSample">
// Racine pour la langue Espagnole
http://monsite.com/es/


// La page du panier pour le eBusiness
http://monsite.com/fr/content/cart
=> Fichier lus
   /user/theme/montheme/controller/content/cart.php (controller)
   /user/theme/montheme/view/content/cart.php (rendu)


// Un formulaire
http://monsite.com/fr/contact/
=> Affichera le fichier 
   /user/theme/montheme/view/contact/index.php (rendu)
</pre>
