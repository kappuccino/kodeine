<h1>API Content</h1>

<div class="fnIntro">
	<p>Cette API permer de gérer tout le contenu.</p>
</div>

<div class="fnName">contentGet()</div>

<h3>Description</h3>
<div class="fnDesc">Retourne du contenu de maniere structuré et organisé</div>

<h3>Argument</h3>
<pre class="fnSample">$opt</pre>
<div class="fnArg">
	$opt respresente un Array() associatif. nom => valeur.<br />
	Voici la liste des couples clé/valeur que vous pouvez utilisez.
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="100">nom</th>
			<th width="70">type</th>
			<th>Info</th>
		</tr>
		<tr>
			<td>id_type</td>
			<td>integer</td>
			<td>L'id_type qui correspond au type de contenu, inutile si on demande un content avec id_content ou contentUrl directement</td>
		</tr>
		<tr>
			<td>id_content</td>
			<td>integer</td>
			<td>L'id_content que l'on souhaite récupérer</td>
		</tr>
		<tr>
			<td>contentUrl</td>
			<td>string</td>
			<td>Effectu la recherche en se basant sur l'URL du content</td>
		</tr>
		<tr>
			<td>id_content</td>
			<td>array()</td>
			<td>Les id_content que l'on souhaite récupérer</td>
		</tr>
		<tr>
			<td>id_group</td>
			<td>integer</td>
			<td>Change ponctuellement le groupe, si non utilise le groupe courant</td>
		</tr>
		<tr>
			<td>id_chapter</td>
			<td>integer</td>
			<td>Change ponctuellement le chapitre, si non utilise le chapitre courant</td>
		</tr>
		<tr>
			<td>language</td>
			<td>string (2)</td>
			<td>Change ponctuellement la langue, si non utilise la langue courante</td>
		</tr>
		<tr>
			<td>useField</td>
			<td>boolean</td>
			<td>Reformate la sortie en separant les champs custom dans un sous array (ex field37 &raquo; [field]['nom']), true par défaut</td>
		</tr>
		<tr>
			<td>useGroup</td>
			<td>boolean</td>
			<td>Si true prends en compte la liaison avec les groupes, si non l'inverse (par défaut true)</td>
		</tr>
		<tr>
			<td>useChapter</td>
			<td>boolean</td>
			<td>Si true prends en compte la liaison avec les chapitres, si non l'inverse (par défaut true)</td>
		</tr>
		<tr>
			<td>limit</td>
			<td>integer</td>
			<td>Le nombre d'enregistrement a retourner, par défaut 30</td>
		</tr>
		<tr>
			<td>offset</td>
			<td>integer</td>
			<td>Le décalage à effectuer pour restiture les enregistrement, par défaut 0, (a utiliser avec limit)</td>
		</tr>
		<tr>
			<td>noLimit</td>
			<td>boolean</td>
			<td>Si TRUE, ne limite pas la requete (attention au jeu d'enregistrement) - par défaut FALSE</td>
		</tr>
		<tr>
			<td>categoryThrough</td>
			<td>boolean</td>
			<td>Si TRUE, effectue une recherche en prenant la categorie et les sous categories</td>
		</tr>
		<tr>
			<td>chapterThrough</td>
			<td>boolean</td>
			<td>Si TRUE, effectue une recherche en prenant le chapitre et les sous chapitre</td>
		</tr>
		<tr>
			<td>groupThrough</td>
			<td>boolean</td>
			<td>Si TRUE, effectue une recherche en prenant le groupe et les sous groupe</td>
		</tr>
		<tr>
			<td>human</td>
			<td>boolean</td>
			<td>Si TRUE, reformate la sortie pour etre plus lisible : media + field</td>
		</tr>
		<tr>
			<td>raw</td>
			<td>boolean</td>
			<td>Si TRUE, force les valeurs : useField= false human=false useChapter=false useGroup=false assoChapter=true assoCategory=true assoGroup=true assoSearchtrue contentSee=ALL</td>
		</tr>
	</table>
</div>

<h3>Exemple</h3>
<pre class="fnSample">&lt;?php

	$app->apiLoad('content')->contentGet(array(
		'arg'	=> 'val'
	));

?&gt;</pre>
