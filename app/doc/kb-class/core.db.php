<h1>core.db</h1>

<div class="fnIntro">
	<p>Cette API permer de gérer les accès à la base de données,
	elle offre de nombreux raccourcis simplifiant l'écriture
	des requetes et leur utilisation.</p>

	<p>Il est inutile de vouloir se connecter manuellement, des que la
	classe est initialisé elle se connecte automatiquement au serveur,
	via un mecanisme de singleton</p>
</div>

<div class="fnName">dbQuery($query, $link=NULL)</div>
<h3>Description</h3>
<div class="fnDesc">Execute une requete</div>
<h3>Argument</h3>
<div class="fnArg">
$query représente la requete a executer,<br />
$link le flux de connection, si aucun flux n'est indiqué alors la connection courante est utilisé
</div>
<h3>Exemple</h3>
<pre class="fnSample">$this->dbQuery("SELECT NOW()");</pre>



<div class="fnName">dbOne($query, $link=NULL)</div>
<h3>Description</h3>
<div class="fnDesc">Execute la requete est retourne un Array associatif simple entre champ => valeur</div>
<h3>Argument</h3>
<div class="fnArg">
$query représente la requete a executer,<br />
$link le flux de connection, si aucun flux n'est indiqué alors la connection courante est utilisé
</div>
<h3>Exemple</h3>
<pre class="fnSample">
$this->dbOne("SELECT * FROM table WHERE id=1");

// Return
Array (
	[champA] => valeurA,
	[champB] => valeurB
)
</pre>





<div class="fnName">dbMulti($query, $link=NULL)</div>
<h3>Description</h3>
<div class="fnDesc">Execute la requete est retourne un Array associatif a double entré</div>
<h3>Argument</h3>
<div class="fnArg">
$query représente la requete a executer,<br />
$link le flux de connection, si aucun flux n'est indiqué alors la connection courante est utilisé
</div>
<h3>Exemple</h3>
<pre class="fnSample">
$result = $this->dbOne("SELECT * FROM table");

// Return
Array (
	[0] => Array (
		[champA] => valeurA,
		[champB] => valeurB
	),
	[1] => Array (
		[champA] => valeurA,
		[champB] => valeurB
	)
)

// En situation
// $data represente une ligne, on traite ligne a ligne

foreach($result as $data){
	echo $data['champA'].' '.$data['champB'];
}
</pre>



<div class="fnName">dbMatch($field, $value, $mode)</div>
<h3>Description</h3>
<div class="fnDesc">Retourne une chaine formaté pour associé un champs ($field)
et une valeur ($value), par un oppérateur ($mode)</div>
<h3>Argument</h3>
<div class="fnArg">
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="100">mode</th>
			<th width="100">SQL</th>
			<th>Exemple</th>
		</tr>
		<tr>
			<td>EG</td>
			<td>=</td>
			<td>field = value</td>
		</tr>
		<tr>
			<td>NE</td>
			<td>!=</td>
			<td>field != value</td>
		</tr>
		<tr>
			<td>BW</td>
			<td></td>
			<td>field LIKE 'value%'</td>
		</tr>
		<tr>
			<td>EW</td>
			<td></td>
			<td>field LIKE '%value'</td>
		</tr>
		<tr>
			<td>CT</td>
			<td></td>
			<td>field LIKE '%value%' (valeur utilisé par defaut)</td>
		</tr>
		<tr>
			<td>MT</td>
			<td>></td>
			<td>field > value</td>
		</tr>
		<tr>
			<td>LT</td>
			<td><</td>
			<td>field < value</td>
		</tr>
		<tr>
			<td>ME</td>
			<td>>=</td>
			<td>field >= value</td>
		</tr>
		<tr>
			<td>LE</td>
			<td><=</td>
			<td>field <= value</td>
		</tr>
		<tr>
			<td>IN</td>
			<td></td>
			<td>field IN(value,value)</td>
		</tr>
		<tr>
			<td>NI</td>
			<td></td>
			<td>field NI(value,value)</td>
		</tr>
	</table>
</div>
<h3>Exemple</h3>
<pre class="fnSample">
// L'argument BW (Begin With) peut changer
// sans pour autant reformuler la requete
$query = "SELECT * FROM table WHERE ".$this->dbMatch('champA', 'linux', 'BW');


// qui sera traduit par
$query = "SELECT * FROM table WHERE champA LIKE 'linux%');
</pre>



<div class="fnName">dbInsert($def, $opt=array())</div>
<h3>Description</h3>
<div class="fnDesc">
Retourne une chaine de caractère préparé pour INSERT.<br />
$opt représente un Array d'option (cf le détail plus bas)
</div>
<pre class="fnSample">INSERT INTO table (champA,champB) VALUES ('valueA', 'valueB')</pre>
<h3>Argument</h3>
<div class="fnArg">
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="100">Clé</th>
			<th width="100">Exemple</th>
			<th>Explication</th>
		</tr>
		<tr>
			<td>value</td>
			<td>apple</td>
			<td>La valeur que prendra le champ</td>
		</tr>
		<tr>
			<td>check</td>
			<td>[A-Z]{2}</td>
			<td>L'expression regulière qui sert à savoir si le champ est bien remplit</td>
		</tr>
		<tr>
			<td>null</td>
			<td>true/false</td>
			<td>Si la valeur est vide est que null=true, alors MySQL recevra NULL</td>
		</tr>
		<tr>
			<td>zero</td>
			<td>true/false</td>
			<td>Si la valeur est vide est que zero=true, alors MySQL recevra 0</td>
		</tr>
		<tr>
			<td>function</td>
			<td>MD5('password')</td>
			<td>La valeur sera remplacé par la fonction champA=MD5('password') par exemple</td>
		</tr>
	</table>
	
	<p>Valeur possible pour $opt</p>
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="100">Clé</th>
			<th width="100">Valeur</th>
			<th>Explication</th>
		</tr>
		<tr>
			<td>ignore</td>
			<td>true/false</td>
			<td>Rajoute IGNORE dans la requete INSERT INTO</td>
		</tr>
	</table>
</div>

<h3>Exemple</h3>
<pre class="fnSample">
// $def est une structure de champs

$def[table] = Array(
	'champsA'	=> Array(
		'value' => 'ma valeur',
		'check'	=> '*',
		'null'	=> true,
		'zero'	=> false,
		'function' => 'NOW()'
	)
);

// Retournera
INSERT INTO table (champA) VALUES ('ma valeur');
</pre>

<div class="fnCool">
	La meme structure de base (table => champs) peut être utilisé pour une requete
	INSERT et UPDATE, la seul différence tient dans la condition WHERE qui doit être
	relié indépendament.
</div>


<div class="fnName">dbUpdate($def)</div>
<h3>Description</h3>
<div class="fnDesc">Même fonctionnement que dbInsert() mais prépare une requete UPDATE</div>
<pre class="fnSample">UPDATE table SET champA='valueA', champB='valueB' WHERE id=1</pre>
<h3>Argument</h3>
<div class="fnArg">Identique a la fonction dbInsert()</div>
<h3>Exemple</h3>
<pre class="fnSample">
$query = $this->dbUpdate($def)." WHERE id=1";

// Retournera
UPDATE table SET champA='ma valeur' WHERE id=1
</pre>
 
 