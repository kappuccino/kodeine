<div class="fnName fnNameFirst">Le chargement des fichiers</div>

<div class="fnDesc">
	Les fichiers utilisés pour afficher du contenu sont placés dans différents dossier.<br />
	Un  design pattern d'inspiration "MVC" est utilisé pour manipuler les infos, mais il n'est
	pas aussi précis que le modèle MVC au sens strict.<br /><br />

	Ici on se contente d'avoir:<br />
	- une vue (qui affiche les données)<br />
	- controlleur (qui recupère/insert les données dans la base de données pour les fournir à la vue).
</div>

<div class="fnName">Ordre des fichiers</div>

<div class="fnDesc">
	<b>1 :</b>
	Le premier fichier qui appel tous les autres se trouve dans le dossier /app.<br />
	Le fichier index.php (commodité)<br /><br />
	Ce fichier permet de regler plusieurs variables système, initialiser Kodeine
	et permet de lancer la vue pour le theme. (vous ne devriez pas modifier ce fichier)
</div>
<pre class="fnSample">/app/index.php</pre>

<div class="fnDesc">
	<b>2 :</b>
	Le second fichier qui est appelé se trouve dans le theme. Il est impératif de
	conserver le même nom pour tous les themes.<br /><br />
	Ce fichier permet de paramétrer la manière dont la vue sera affiché
</div>
<pre class="fnSample">/user/theme/montheme/html.build.php</pre>



<div class="fnName">Autres fichiers utilisés</div>

<div class="fnDesc">
	D'autre fichier sont utilisé pour rendre la vue de la page.<br /><br />
	Il y a 2 autres fichiers utilisés pour modifier le comportement de l'affichage
</div>
<pre class="fnSample">
controller	= /user/montheme/controller/module/fichier
view		= /user/montheme/view/module/fichier
</pre>

<div class="fnDesc">
	<b>CONTROLLER</b><br />
	Ce fichier gère les connections à la base de donnée, retournes les data, met à
	jour ou insert des infos.<br />
	Ce fichier ne doit pas afficher de HTML.<br /><br />

	<b>VIEW</b><br />
	Ce fichier représente la vue HTML qui sera affiché.<br />
	Ce fichier contient le code HTML, et souvent affiche les données qui sont chargé par le CONTROLLER .<br /><br />
</div>

<pre class="fnSample">
Exemple pour l'URL /fr/content/cart

CONTROLLER	/theme/montheme/interface/controller/content/cart.php
SHARE 		/app/share/content/cart.php

Si le fichier CONTROLLER existe
	le fichier CONTROLLER est chargé
	
	Si le fichier VIEW existe il est chargé

Si non,
	
	le fichier VIEW existe il est chargé

Si non
	on affiche le dump 404 qui précise pourquoi aucune fichier ne peut être chargé
</pre>


