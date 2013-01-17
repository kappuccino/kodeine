># Kodeine, webApp with a brain

### Requirement

* Nginx, Lighttp or Apache 2
* PHP 5.3
* MySQL 5.1

### Installation

1. Clone me 
2. Rename /kodeine to /app in your project and choose rewrite rules sets below
3. Have fun

##### nginx
<pre>
server {
	...
	
	try_files $uri $uri/ /app/index.php?$args&rewrite=$uri;

	location = / {
    	rewrite / /app/index.php last;
	}

	...
}
</pre>


##### Apache
<pre>
Options None
Options +FollowSymLinks

&lt;Files .[lo*|sql|ht*]&gt;
	order allow,deny
	deny from all
&lt;/Files&gt;

&lt;IfModule mod_rewrite.c&gt;
	RewriteEngine On
	RewriteBase /	
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-s
	RewriteRule ^(.*)?$ app/index.php?rewrite=$1 [QSA,L]
	RewriteRule ^$ app/index.php [QSA,L]
&lt;/IfModule&gt;
</pre>

##### Lighttpd
<pre>
url.rewrite-if-not-file = (
	"^([^?]*)?(?:\?(.*))?"  => "/app/index.php?rewrite=$1&$2"
)
</pre>