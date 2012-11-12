
<div class="fnName fnNameFirst">Les Images</div>

<div class="fnDesc">
	Ce document décrit la manière dont les images sont appelées via les URLs.
</div>

<div class="fnName">Demande l'image source</div>

<pre class="fnSample">&lt;img src=&quot;/media/mon-image.jpg&quot; /&gt;</pre>


<div class="fnName">Demander une image réduite</div>

<div class="fnDesc">
	Si l'on souhaite une image réduite en conservant les proportions.
</div>

<div class="fnDesc">
	<b>Utiliser la LARGEUR comme nouvelle valeur</b><br />
	Nous voulons une nouvelle image qui a pour largeur un nombre X de pixels,<br />
	<i>C'est donc la hauteur du document qui va s'adapter</i>.<br /><br />
	Nous voulons la même image, mais avec une largeur de 150 pixels.
</div>
<pre class="fnSample">&lt;img src=&quot;/w:150/media/mon-image.jpg&quot; /&gt;</pre>

<div class="fnDesc">
	<b>Utiliser la HAUTEUR comme nouvelle valeur</b><br />
	Nous voulons une nouvelle image qui a pour hauteur un nombre Y de pixels,<br />
	<i>C'est donc la largeur du document qui va s'adapter</i>.<br /><br />
	Nous voulons la même image, mais avec une hauteur de 175 pixels.
</div>
<pre class="fnSample">&lt;img src=&quot;/h:175/media/mon-image.jpg&quot; /&gt;</pre>


<div class="fnName">Demander une portion carrée de l'image</div>

<div class="fnDesc">
	Si l'on souhaite avoir une image carré de l'image originale.<br />
	Dans ce cas, la portion est calculé en fonction de l'orientation de l'image, la zone la plus grande
	est alors utilisée.
	
	<p style="text-align:center; margin:20px 0px 20px 0px;">
		<img src="ressource/img/image-crop-landscape.jpg" style="margin-right:120px;" />
		<img src="ressource/img/image-crop-portrait.jpg" />
	</p>
	
	Il suffit ensuite de spécifier le taille de l'image a générer.
</div>

<pre class="fnSample">&lt;img src=&quot;/s:150/media/mon-image.jpg&quot; /&gt;</pre>

<div class="fnDesc">
	<p>Si l'on souhaite avoir une portion libre, qui ne soit pas un carré, il faut spécifier
	deux paramètres : la largeur ET la hauteur de l'image finale.</p>

	<p style="text-align:left; margin:20px 0px 20px 0px;">
		<img src="ressource/img/image-crop-free.jpg" style="margin-left:30px;" />
	</p>
	
	Il suffit ensuite de spécifier le taille de l'image a générer.

</div>

<pre class="fnSample">&lt;img src=&quot;/c:300,100/media/mon-image.jpg&quot; /&gt;</pre>

<div class="fnDesc">
	<p>Liste des paramétres</p>
	<table border="1" width="100%" class="tablo">
		<tr>
			<th width="200">Paramètre</th>
			<th>Précisions</th>
		</tr>
		<tr>
			<td>/w:X/media/image.jpg</td>
			<td>X, un nombre spécifiant la largeur de l'image d'arrivée</td>
		</tr>
		<tr>
			<td>/h:Y/media/image.jpg</td>
			<td>Y, un nombre spécifiant la hauteur de l'image d'arrivée</td>
		</tr>
		<tr>
			<td>/s:Z/media/image.jpg</td>
			<td>Z, un nombre spécifiant le coté de l'image d'arrivée (carré)</td>
		</tr>
		<tr>
			<td>/c:X,Y/media/image.jpg</td>
			<td>X, un nombre spécifiant le coté de l'image d'arrivée, Y sa hauteur</td>
		</tr>
	</table>
	
	<p><b>Mémo</b><br />
		H = Height (hauteur)<br />
		W = Width (largeur)<br />
		S = Square (carré)<br />
		C = Crop (portion)<br />
	</p>
</div>


<div class="fnName">
	Comment utiliser ces URls dans le code du theme
</div>
<div class="fnDesc">
	Ces urls sont un moyen rapide d'avoir l'image directement à la bonne taille,
	vous pouvez utiliser la fonction mediaUrlData() pour avoir les infos sur la sortie<br /><br />
	
	<b>NOTE :</b>
	Kodeine délégue la création des vignettes à un autre fichier (helper/image.php), à l'aide
	de la réécriture d'URL, ce qui implique que le script de rendu ne peut communiquer avec la page.
</div>

<div class="fnDesc">
	Demander les infos sur une image à générer
</div>

<pre class="fnSample"> $data = $this->mediaUrlData(array(
	'mode'	=> 'height', // height, width, square, crop
	'value'	=> 150,
	'second'=> 75, // uniquement necessaire si mode=crop
	'url'	=> '/media/test.jpg',
	'ttl' => '+8 days', // Time To Live : Cache
	'debug' => true // true, false
));
</pre>

<div class="fnDesc">La sortie du mode debug</div>
<pre class="fnSample">Array
(
    [src]	=> /media/test.jpg
    [ratio]	=> 0,0575153374233
    [value]	=> 150
    [source]	=> Array
        (
            [url]	=> /home/www/media/test.jpg
            [height]	=> 2608
            [width]	=> 1916
        )
    [height]	=> 150
    [width]	=> 110
    [cache]	=> 1
    [ttl] 	=> <?php echo time()."\n" ?>
    [ttlDate] 	=> <?php echo date("Y-m-d H:m:i")."\n" ?>
    [render]	=> /h:150/media/test.jpg
    [store]	=> /home/www/media/.cache/h-150-test.jpg
    [img]	=> /media/.cache/h-150-test.jpg
)
</pre>

<div class="fnDesc">
	Afficher l'image dans la page
</div>

<pre class="fnSample">
echo "&lt;img src=\"".$data['img']."\" /&gt;";
</pre>


















