# Changelog

#### 2012-xx-xx (build 50)
- Refonte integrale de tout le backoffice
* Changement de la numerotation 50 (4* = ancienne branche)


#### 2012-07-11 (build 43)
- Correction dans contentGet pour un search avec un array complement vide (l'argument n'est pas prit en compte)
- Correction dans la sortie d'un contentGet lors du reformatge d'un champ MULTI-CONTENT, quand il est en mode SOLO, l'ARRAY de sortie un d'une seul dimension (si multi, multi dimenssion)
- Correction de la recherche de contenu, si un champ est de type MULTI-CONTENT (type=solo), la sous-requete est simplifie avec une association champ <> value
- Correction de l'ordre des instructions des patch (core en prmier, ensuite par ordre alphabethique)
- Correction d'un bug dans la gestion des categories en mode multilingue, qui prenait une langue traduite comme une copie.
- Correction d'un bug lie l'extension en majuscule des fichiers dans l'outil de recadrage
- Correction d'un bug de JS dans le mode GALLERY des items
- Correction d'un bug dans la recherche de table externes (content.js)
+ Ajout de la creation a la volee d'un contenu depuis une table de liaison
+ Ajout de l'edition d'un contenue depuis une table de donnÈe (sauf pour dbtable)
+ Integration du watermark sur les images
+ Mise jour des recherche enregistre pour les utilisateurs a la mise a jour / creation d'un USER et de la mise a jour des PARAM d'une SEARCH
+ Modification de l'API user pour simplifier le mapping des champs (possibilite de l'appeler depuis une autre API)
+ Possibilite de demander du contenu lie a un forum (INT,ARRAY) via un contentGet()
+ Ajout d'option suplementaire pour la selection des cotentu (use_group, use_chapter user_category ...)
+ Simplification de l'interface dans CONTENT quand on a pas besoin des group, chpater etc
+ Ajout de la liaison entre CONTENT & SOCIAL FORUM
+ Amelioration du contentGet sur la gestion des *Asso (avec dbKey)
+ Changement de la structure des FIELD sur tout kodeine (content, category, chapter...)
+ Ajout d'un classement par ID et par DATE sur la liste des commandes


#### 2012-04-14 (build 42)
- Correction d'un bug lors de l'enregistrement d'un contenu depuis le site (contentSet) dans un autre langue que FR (merci Paul)
- Correction de la detection du CHAPTER dans l'URL 
- Correction dans la gestion des id_user poour ALBUM / CONTENT
- Correction d'un bug dans la generation d'une image liee a un poster en mode gallery (content)
- Correction la mise a jour d'un content ne force pas le id_user (auteur) depuis le back office (seulement a la creation)
- Correction d'une faille de sÈcuritÈ sur le login (SQL Injection)
- Correction de la fonction "poster" sur les galleries
- Correction de apiLoad pour pouvoir loader des instance de testME > api.testme.php
+ PossibilitÈ d'avoir 2 colonnes supplementaire dans la liste des utilisateurs (champs)
+ Mise a jour du template d'installation
+ Separation des API social en differents API independant (post, message, circle...)


####  2012-03-08 (build 41)
- Correction de la recuperation des champs pour lors d'un contentSet pour un album
- Correction dans le selecteur des SHOP, shopSelector() le parmatre empty est prit en compte sur un appel ONE.
- Correction d'un bug dans la recuperation des groupes pour une enquete api::survey::surveyGroupGet()
- Correction dans la liaison des champs user/content pour le CONTENT et le USER (merci paul)
- Correction de la fonction contentSet. Si les champs envoyes a la fonction ne sont pas relie au type, il ne sont pas mis a jour.
- Correction dans l'appel de mediaDataGet() pour des fichier qui contienne des apostrophe
- Correction dans la creation d'un type GALERIE
+ Amelioration du system de numerotation de facture, le numero est ajoute uniquement quand la facture est en mode status=OK
+ Ajout du filtre par SHOP dans la liste des factures (Remi)
+ Meilleur controle du classement dans un contentGet() avec l'option 'sqlOrder', qui rajoute des condition de regroupement suplementaire (Remi)
+ Ajout des champs shopChequeOrder et shopChequeAddress pour simplifier la gestion des cheques pour les shops (Remi)
+ Visibilite de la consommation des mails envoyes avec le module newsletter
+ Possibilite d'enregister les champs via le fieldKey dans le contentSet (merci Remi. ie: field = array('monChamp' => 'ma valeur') ou field = array('33' => 'ma valeur')
+ Finalisaiton du champs de liaison USER (champs visible parametrable)
+ Harmonisation dans la sortie des champs de liaisons (tous des array)
+ Ajout de l'arborescence ‡ droite pour la vue des albums
+ Quelques corrections dans l'interface de gestion des albums
+ Ajout des dates dans la gestion des albums
+ Nouvelle fonctionnalite d'import directement dans les album
+ Ajout d'un nouveau type de champs "content-type" qui offre la liste des type de contenu
+ Cleanup code (syntaxe + api folder)


#### 2012-01-31 (build 40)
- Retour arriere sur le selecteur de categorie (de nouveau le menu) le temps que le systËme soit plus stable
- Correction du pb d'arrondie dans l'ajout d'un produit avec un prix a virgule ',' au lieu de '.' => mysql => arrondie
- Correction d'un probleme qui afficher les mauvaise factures pour les utilisateurs
- Correction d'un bug quand on appel une langue qui n'existe pas
+ Update de TinyMCE pour la compatibilite avec FF 8


#### 2012-01-13 (build 39)
- Correction d'un probleme eventuel lorsque l'on selectionne des categorie d'une meme branche, les nodes les plus bas sont supprimer (contentSet)
- Correction du patch USER qui ne rajoute plus les anciennes table "community"
- Correction dans l'utilisation de la cache (contexte site) pour les category / chapter / content
- Correction de la fonction helperUrlEncode() pour bien prendre en compte les accents
- Correction d'un bug sur les label qui pouvait faire apparaitre une erreur sur le back office
- Correction d'un champs fantome dans la suppression d'un champ dans la configuration generale
+ Nouvelle classe CACHE avec SQLLOCAL ou MEMCACHE
+ Nouvelle maniere de gerer les categorie, plus simple pour les gros site
+ Ajout d'un token pour le panier (a la creation d'un panier)
+ Ajout du champ civilite dans le carnet d'adresse
+ Ajout du bouton EDITER pour la gestion des sous-contenu
+ Ajout de la reference sur la liste des produits en mode is_business
+ Introduction de memcache
+ Ajout des parametres MEMCACHE_PREFIX(string), MEMCACHE_SERVER(string), MEMCACHE_LOG(boolean) dans /user/config/app.php
+ Modification de la fonction apiLoad pour la bonne propagation de l'objet memcache
+ Un user n'est plus supprime de la BDD, il est note is_deleted=1
+ Champs externe, affiche en plus le numero de l'ID (dans la table + dans le cartouche)
+ Revision complete du module newsletter, tous les mails sont envoyes depuis une infrastructure externe
+ Mise a jour du plugin TINYMCE
+ Possibilite de demander des liaisons unique sur les tables externe (value = 1 et plus value = @@1@@)
* Suppression de l'API coreOS => merge avec coreAPP


#### 2011-11-11 (build 38)
- Correction de l'API field
- Correction la variable dblog devient $GLOBALS['dblog'] pour etre bien prise en compte dans l'API MySQL
- Correction da la gestion des stats dans le module newsletter et de helper/newsletter.php (mauvaise gestion des FLAG=SENT)
- Correction de la gestion des LABEL permet d'avoir plus de _ dans la partie slave (Ex: MY_STRANGE_VAR)
- Correction mineur de l'API field et category
- Correction d'erreur diveses sur content.index lors de la vue en heritage
- Correction de la fonction helperUrlEncode qui prend en compte les id_content
- Amelioration de la fonction de gere les liaison du content (evite les doublon)
- Correction de l'API FIELD, les champs lies ne retourne plus d'erreur SQL quand la valeur est vide IN(?)
- Meilleure gestion des FAMILY pour les category (temps de generation accelerer pour trouver les parents)
- La pagination de la liste des newsletter est correctement gerer
- Correction de la fonction configSet() qui encode correctement le JSON
+ Nouveau patch sur la table k_contentasso pour gerer la relation user/order sur les content
+ Notion de classement des elements relies (contentasso)
+ Possibilite de filtrer par shop dans la liste des contents
+ Ajout des options sqlJoin et sqlWhere dans les conditions de requetes pour CONTENT et USER (se rajoute a la fin de la requete)
+ Petite amelioration de la gestion des templates en cas de copy brutale (sans renommer le fichier XML) 
* Suppression de toute la partie user-board et community, cette partie doit etre repense de manire independante de l'API user (API social)
* Nettoyae des API plus utilisees


#### 2011-10-10 (build 37)
- Correcction dans la gestion des mails supplementaires lors de l'envois d'une newsletter (format du champ RAW)
- Correciton de l'API field qui ne retournait pas les bon champs (fieldKey + Array)
- Correction du ptach du module ad (beaucoup de champs manquants sont desormais correctement ajoutes)
- Correction du template MySQL pour l'installation, (erreur de syntaxe)
+ Amelioration du rendu des champs complementaires pour la configuration (config.index.php)


#### 2011-09-29 (build 36)
- Correction dans l'appel de chargement de la configuration pour un domaine
- Correction de l'appel des content par l'URL d'une categorie (la categorie n'etait pas prise en compte cat == monoCat)
- Renommage de l'API grade en rate (plus revelateur du fonctionnement), les fonction grade* sont desormais rate*
- Correction de la gestion des titre/description sur les media
- Correction d'un bug qui retournÈ de mauvaises information pour les groupes 
- Correction et simplification du parametrage de l'interfce dans la gestion des contenus
- Correction du formulaire des legendes sur FF + IE (hidden input broken)
- Correction du mauvais appel de la fonction setBackgroundColor() dans helper/image.php en mode SQUARE
- Utiliser media->mediaMimeType() pour detecter le type mime, (finfo si PHP 5.3 ou apache-myme-list si non app/helper/mime-type.txt)
- Le media manager utilise la cahe (les preferences useCache est fixe a OFF pour le moment)
- Correction d'un bug dans le nettoyage des image en BDD pour les images
+ Protection contre la sur-pixelisation des image en mode crop
+ Nouvelle syntaxe pour les image en mode crop (x,y ou x-y) au lieu de value=x second=y (pour aller plus vite)
+ La sauvegarde des elements des formulaires se fait automatiquement (sans avoir besoin de sauver/recharger la page)
+ La generation de l'URL peut desormais etre automatique ou manuel 
+ Les fonctions du theme sont desormais dans une API independante (api.theme.php)
+ Creation d'une methode d'installation (tres rudimentaire)
* Suppression des modules hosting et clientftp, ils ne font plus partis du projet


#### 2011-08-29 (build 35)
- Correction de la regeneration du mot de passe depuis le panel de log du back office (bon lien)
- Corrections mineures dans l'API user pour l'appel des tables
- Amelioration du systeme de gestion du desabonnement des listes depuis l'admin
+ Modification de l'API template, (pour le moment, les templates sont uniquement des fichiers)
+ Classement des menu template par ordre alphabetique (content + category + type) et prise en compte des fichierd XML (le vrai nom)
+ Suppression du dossier /interface/ dans le theme et simplification du fichier html.build.php (themeInclude modifie)
+ Ajout des bonnes reponses pour les enquete/quizz (surveyQueryItemIsTrue)
* Meilleure gestion de la cache des images a l'appel de la fonction mediaUrlData() (prise en compte du filemtime dans la prise de decision)


#### 2011-08-11 (build 34)
+ Ajout des champs shopMailTemplate et shopMailTitle pour l'envois des commande par mail dans le cadre d'un shop
+ Possibilite de desactiver le reglement par CB sur le shop (allow_card=true|lase)
+ Ajout de la fonction contentMediaLink qui permet d'ajouter des media hors admin facilement, ne prend pas encore en compte les autres champs (que contentMedia) pour le moment
+ Ajout de la fonction businessCmdMail() qui permet de gerer simplement l'envois d'un email suite a une commande (en passant par un shop ou en forcant le main sur les parametres)
+ Suppression du la notion SHARE pour rendre le APP plus simple d'utilisation (le theme kodeine contient le controller par defaut recommander).
+ Possibilite d'importer le contenu d'un dossier dans un album (racine) ou bien de creer un sous-album et d'importer des elements a l'interieur
+ DÈclenchement d'un event lors d'une commande
+ DÈclenchement d'un event lors de l'envois d'un mail pour une commande
* Correction du calcul du nombre de mail envoyes dans les stats des newsletters
* Correction de la generation de la fieldCache lors de la modification d'un champs (plus besoin de passer par l'edition d'un type pour generer la cache des FIELD + TYPE)
* Correction du bug lors de l'ajout d'un nouveau type (crash des droits)
* Correction d'un bug qui empeche la suppression d'un album / item dans la vue en galerie dans l'admin


#### 2011-08-08 (build 33)
- SHARE / CART : on ne peut pas ajouter un produit qui n'appartient pas au SHOP associÈ au panier (passer id_shop=% dans l'URL)
- Deplacement des fonctions businessCarriage* vers l'API business
+ Coupon : ajout de la notion de shop (un coupon peut etre forcÈ pour un shop)
+ Ajout un next/previous sur les album comme les items pour passer d'un album a l'autre depuis le mode edition
+ Gestion du numÈro de facture en fonction du id_shop
+ Ajout du champs newsletterTitle pour dissocier le nom dans l'admin et le titre de l'email
* Correction de la duplication, prend desormais en charge le SHOP associe a un produit
* Correction de la gestion des frais de ports
* Nettoyage de la class core.admin.php et deplacement des fonction content* dans l'API content (+ correction des fichier d'admin)
* Correction du gestionnaire de media embarque (embed), le deplacement des dossiers est de nouveau opperationnel
* Correction de l'encodage des URL dans l'envois des newsletter (newsletterPrepareBody)
* Correction de l'ordre du pathway qui decrit l'arborescence dans un album (cotent.gallery.album.php)
* Correction d'un bug pour les champs a valeur multiple
* Correction d'un bug losr de la suppression d'un groupe
* Correction d'un bug dans la gestion du patway des album sur le BO
* Correction du fichier SHARE:content:overview = mauvais appel shopGet()


#### 2011-07-30 (buid 32)
- Classement des modules dans l'admin (home) par nom (celui qui est visible)
+ Shop: ajout des destinaitres To, Cc, Bcc
+ Shop: ajout des options allow_cheque + allow_coupon
+ Simplification du script de customisation des CSS pour tinymce (dans /user/config/tinymce.php) en fonction de l'emplacement des CSS du theme (/media/ui ou /user/themes/montheme/css)
* Simplication du numÈro de version de version de Kodeine kodeine.build.XX


#### 2011-07-28
* Correction du recadrages des images


#### 2011-07-26
+ Ajout de l'argument 'generate' sur mediaUrlData() pour focer la generation de l'image au moment de l'appel de la fonction et non plus au moment de l'appel de l'image (une fois que le code HTML est genere)
* Correction de la page de consolidation, meilleure affichage si aucune donnÈe prÈsente
* Nettoyage des cacheTTL qui n'est plus utilisÈ pour la generation de la cache des images


#### 2011-07-23
+ Gestion des albumFamily en action SQL (add,move,delete) pour gerer le albumThrough (au travers des albums)
+ PossibilitÈ de demander les ITEM ou ALBUM avec albumThrough
+ PossibilitÈ d'activer ou pas la CACHE sur un fieldGet depuis le context du site (il faut le apisConfig defined) pour un TYPE seulement (pas Album ni ITEM)
+ Simplification des EVENT / HOOK dans /user/event/event.xxxxx.php (plus que les event CUSTOM, les autres n'etant plus necessaire + verification que la fonction existe, ce n'Ètait pas le cas avant)
+ PossibilitÈ de demander un TOKEN d'identification externe 
+ DÈclenchement d'un event ‡ l'upload d'une image
+ DÈclenchement d'un event quand un user s'identifit (access granted, pas via cookie ou session)


#### 2011-07-22
* Correction de la page content-safe qui permet de gÈnÈrer la cache des pages (toutes)
* Correction de la fonction de generation de la contentCache

#### 2011-07-21
* Correction de la generation de la groupFamily, simplification du code, classement automatique, bonne pos_group a l'ajout
* Correction du systeme de cache pour les champs des Types


#### 2011-07-19
+ Ajout du champ shopApiFolder au SHOP pour relier plusieurs API differente a different SHOP

#### 2011-07-18
+ Ajout des champs dbtable, (source de donnees d'une autre table non kodeine)
* Correction du bug, lors de la recuparation des champs d'un ITEM ou ALBUM le TYPE n'Ètait pas prit en compte


#### 2011-07-14
- Amelioration du rendu des posters sur le contenu gallerie, (contour blanc)
- Simplification de la gestion des champs au sens large "field center"
+ Ajout de la distinction des champs pour les ALBUMS + ITEMS


#### 2011-07-13
- Si on demandes du content avec id_content=array(x,y,z) et que le Array est vide, il n'y a plus d'erreur
- Quelques corrections sur les champs (esthetique)
+ Ajout des champs user (relier un contenu et un user a des contenu et des users)
+ Ajout des champs contentHeadTitle, contentMetaKeywords et contentMetaDescription


#### 2011-07-11
* Correction des erreurs de doublons dans l'outils de mailinglist


#### 2011-07-10
+ Ajout du poster pour les dossier d'une gallery


#### 2011-07-08
- Amelioration de la gestion des champs de type content (liaison de deux contenus)
- Commencement de la creation des champs TAG


#### 2011-07-07
- Amelioration de la gestion de la cache (TTL plus utile), c'est le mtime de la source qui compte


#### 2011-07-01
- Rewrite Rule pour lighttpd reprise pour gerer le QSA (regle a modifier sur le vhost)


#### 2011-06-29
- Gestion des champs invisibles sur l'admin
- Utilisation de la cache dans le gestionnaire de media si la hauteur ou largeur/hauteur > 900 px uniquement, si non utilise l'image source
- Delocalisation des fichiers de LOG d'erreur MySQL constante DBLOG
- Delocalisation des fichiers de DUMP MySQL constante DUMPDIR et du binaire mysqldump DUMPBIN (user/config/app.php)
* Correction du bug de liaisons entre le champ et un array() field de type content ou user


#### 2011-06-27
+ Ajout de l'archivage sur le module de gestion de pub


#### 2011-06-19
- Debut du dev du module hosting


#### 2011-06-17
+ Ajout du module clientftp


#### 2011-06-11
- Synchronisation d'un dossier physique (folder) avec un dossier virtuel (album)


#### 2011-06-10
+ Ajout de l'option multi-shop
* Correction des Patchs


#### 2011-06-03
- L'admin n'utilise plus la syntaxe <?= mais <?php echo pour des raisons de comptabilitÈ


#### 2011-05-27
- Simplification des regles de recritues (apache + lighttpd + nginx)


#### 2011-05-19
- Meilleurs gestion de la suppression des fichier de cache, dossier image etc...
- Script de maintenance ameliorer pour supprimer les infos inutiles


#### 2011-05-18
- Cration des posters des VIDEO depuis l'admin
- Cration des posters des PDF depuis l'admin


#### 2011-05-16
- Gestion des gallery simplifiee
- Amelioration de l'enregistrement des content relie (asso)
- Amelioration de l'API user


#### 2011-05-11
- Allegement du .htaccess pour apache + ajout du support natif pour NGINX et LIGHTTPD
- Creation des 3 nouvelles API : comment, grade et field, allegement de l'api CONTENT des fonctions precedentes


#### 2011-05-09
* Corrections des api CONTTENT / CATEGORY / USER


#### 2011-04-19
+ Amelioration de la saisie d'un label dans la partie traduction


#### 2011-04-13
* Changement de la structure des patch (un patch par module + core pour les autres)


#### 2011-03-22
+ Ajousatellite_caught_exception() du champ k_content.contentView qui est incrementer a chaque affichage de la page (sur le detail, et non pas sur le GET)
- Possibiliter de demande X content via id_content => array()


#### 2011-03-16
+ Ajout du userToken pour la gestion du autologin (pas encore gere)
* Correction des JS en erreur sur l'admin (content)
+ Ajout de la fonction helperNoAccent() qui supprime les accents
- Gestion du nombre de commentaire sur la content (contentCommentCount) + allegement du listing dans l'admin


#### 2011-02-09
- Reorganisaition des dossier dans /app
+ Ajout du support des modules USER et CORE
- Simplication du sommaire de l'admin


#### 2011-02-07
- Quelques correction/amelioration sur le module newsletter


#### 2011-02-03
- Divers corrections sur le module newsletter, (confort visuel)
- Gestion des desabonnement comme un tracking
- Liste des desabonnement depuis une newsletter
- Le dossier /media/.cache n'est plus visible depuis le module de gestion de fichier


#### 2011-02-01
* Correction da la gestion du TTL pour les images (depuis le module media)


#### 2011-01-27
* Correction du module abonnement au newsletter sur l'admin et le site (en cas de copie du theme comme dÈpart d'un nouveau projet)
* Correction du browser de contenu, les accents sont bien traitÈs maintenant


#### 2011-01-25
- Les types de newsletter ne sont plus utilisÈes, elles sont remplacÈes par les listes.
* Correction de tout le systËme d'envois des mails (api newsletter)


#### 2011-01-24
- Encodage en HTML du titre du mail dans l'export pour le cloud (pb encodage en JSON)
* Correction des URL des images + liens dans le corps de la newsletter (border=0 + correction si plusieurs fois le mÍme lien (http://http://...)


#### 2011-01-18
- Reprise de tout le fichier helper/newsletter.php
- Envois des mails depuis un autre serveur (cloud)
* Correction diverse dans le module newsletter


#### 2010-12-12
+ Ajout des champs sur les catÈgories
+ Ajout des champs sur les chapitres


#### 2010-12-07
- PossibilitÈ de loader les class core depuis le dossuer /user/core au lieu de /app/core pouur eviter un ecrasement hasardeux (mysql nottament)


#### 2010-12-04
- Deplacement des template de mail dans le dossier /user/mail
* Correction api:content:contentGet() pour la recherche multi champs
* Correction du fichier .htaccess (correction du fichier index.php + helper/ad.php && ajout helper/robots.php)


#### 2010-12-02
- Fonction core:app:kTalk(), prend en compte les constantes user dans les {} (exemple, define('moi', 'Benjamin') : {moi} => Benjamin)


#### 2010-11-30
+ Ajout de l'icone du fichier par dÈfaut pour le viewer de media de contents (content, add, user ...)


#### 2010-11-29
- Gestion des API user (dossier /user/api/api.*.php) via apiLoad (user prioritaire sur core)
- Suppression du benchmark de memoire au load (doit etre rajoute a la main dans le theme /app/helper/benchmark.php)
- Choix du moteur de generateur d'image GD (par defaut) ou IMAGEMAGICK dans le fichier /user/app/app.php