<div class="fnName fnNameFirst">Configuration de la base de données</div>

<h3>Fichier de configuration</h3>
<pre class="fnSample">
/user/config/db.php
</pre>

<div class="fnArg">
	<table border="1" width="100%" class="tablo">
		<tr>
			<td width="150">$host</td>
			<td>Le nom ou l'IP du serveur auquel se connecter (le plus souvent localhost)</td>
		</tr>
		<tr>
			<td>$login</td>
			<td>L'identifiant utilisé pour se connecter au serveur</td>
		</tr>
		<tr>
			<td>$passwd</td>
			<td>Le mot de passe associé au login</td>
		</tr>
		<tr>
			<td>$database</td>
			<td>Le nom de la base de données</td>
		</tr>
		<tr>
			<td>$type</td>
			<td>Le type de base de donnée, pour le moment uniquement MySQL</td>
		</tr>
		<tr>
			<td>$GLOBALS['dblog']</td>
			<td>true | false indique si les erreurs MySQL sont enregistré dans des fichiers log</td>
		</tr>
	</table>
</div>

<div class="fnName">Configuration supplémentaire</div>

<h3>Fichier de configuration</h3>
<pre class="fnSample">
/user/config/app.php
</pre>

<div class="fnArg">
	<p>Ce fichier est chargé pour définir des constantes personnalisées.
	Si les valeurs suivantes ne sont pas définir, alors les valeurs par défaut
	seront utilisé : THEME, TEMPLATE, DBLOG, DUMPDIR, DUMPBIN, IMGENGINE</p>
</div>
