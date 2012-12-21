># Kodeine, webApp with a brain

### Requirement

* Nginx, Apache 2 or Lighttp
* PHP 5.3
* MySQL 5.1

### Installation

1. Clone me 
2. Rename /kodeine to /app in your project and choose rewrite rules sets
3. Have fun

##### Lighttpd
<pre>
url.rewrite-if-not-file = (
	"^([^?]*)?(?:\?(.*))?"  => "/app/index.php?rewrite=$1&$2"
)
</pre>

##### Apache2
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
