<?php

class ad extends content {

    function __clone(){}

    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function ad(){
        $this->size = array(
            array('width' => 300, 'height' => 100, 'name' => '300x100 Rectangle'),
            array('width' => 728, 'height' => 90,  'name' => '728x90  Leaderboard'),
            array('width' => 468, 'height' => 60,  'name' => '468x60  Full Banner'),
            array('width' => 234, 'height' => 60,  'name' => '234x60  Half Banner'),
            array('width' => 120, 'height' => 420, 'name' => '210x240 Vertical Banner'),
            array('width' => 300, 'height' => 250, 'name' => '300x250 Medium Rectangle'),
            array('width' => 720, 'height' => 300, 'name' => '720x300 Pop-Under'),
            array('width' => 120, 'height' => 90,  'name' => '120x90  Button 1'),
            array('width' => 120, 'height' => 60,  'name' => '120x60  Button 2'),
            array('width' => 88,  'height' => 31,  'name' => '88x31   Micro Bar '),
            array('width' => 300, 'height' => 600, 'name' => '300x600 Half Page'),
            array('width' => 160, 'height' => 600, 'name' => '160x600 Wide Skycrapper'),
            array('width' => 120, 'height' => 600, 'name' => '120x600 Skycrapper'),
            array('width' => 250, 'height' => 250, 'name' => '250x250 Square Pop-U'),
            array('width' => 336, 'height' => 280, 'name' => '336x280 Large Rectangle'),
            array('width' => 240, 'height' => 400, 'name' => '240x400 Vertical Rectangle'),
            array('width' => 180, 'height' => 150, 'name' => '180x150 Rectangle'),
            array('width' => 125, 'height' => 125, 'name' => '125x125 Square Button'),
            array('width' => 'x', 'height' => 'x', 'name' => 'Habillage (Background cliquable)')
        );
    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function adPick($opt){

        if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep='adPick() @='.json_encode($opt));

        $opt['noLimit'] = true;
        $opt['noOrder'] = true; // ???

        $ads = $this->apiLoad('content')->contentGet($opt);

        if(sizeof($ads) > 0){
            // Ponderation
            $ads2   = array();
            $total  = sizeof($ads);
            foreach($ads as $id=>$a) {
                $ratio = ceil(($a['contentAdPriority'] / $total) * $total);
                if($ratio == 0) $ratio = 1;
                for($i=0; $i < $ratio; $i++) {
                    $ads2[] = $id;
                }
            }

            $id = $ads2[array_rand($ads2, 1)];
            $ad = $ads[$id];

            $this->adStat(array(
                'id_content'	=> $ad['id_content'],
                'language'		=> $ad['language'],
                'field'			=> 'view',
                'debug'			=> false
            ));

            if($ad['adCode'] == NULL && sizeof($ad['contentMedia']['image']) > 0){
                $ad['html'] = "<a href=\"/ad".$ad['id_content']."\" target=\"_blank\"><img src=\"".$ad['contentMedia']['image'][0]['url']."\" /></a>";
            }

            $out = $ad;
        }else{
            $out = array();
        }
        if(BENCHME) @$GLOBALS['bench']->benchmarkMarker($bmStep);

        return $out;
    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function adStat($opt){

        if(!$this->adIsCrawler()) {

            if($opt['field'] != 'view' && $opt['field'] != 'click') return false;

            $this->dbQuery(
                "INSERT INTO k_contentadstats\n".
                    "(id_content, language, year, month, day, ".$opt['field'].")\n".
                    "VALUES\n".
                    "(".$opt['id_content'].", '".$opt['language']."', '".date("Y")."', '".date("m")."', '".date("d")."', 1)\n".
                    "ON DUPLICATE KEY UPDATE ".$opt['field']."=".$opt['field']."+1"
            );

            if($opt['debug']) $this->pre($this->db_query, $this->db_error);

            $field = 'contentAdCache'.ucfirst($opt['field']);
            $this->dbQuery("UPDATE k_contentad SET ".$field."=".$field."+1 WHERE id_content=".$opt['id_content']." AND language='".$opt['language']."'");
            if($opt['debug']) $this->pre($this->db_query, $this->db_error);

        }
    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function adZoneGet($opt=array()){

        if($opt['zoneCode'] != NULL){
            $zone = $this->dbOne("SELECT * FROM k_adzone WHERE zoneCode = '".$opt['zoneCode']."'");
        }else
            if($opt['id_adzone'] != NULL){
                $zone = $this->dbOne("SELECT * FROM k_adzone WHERE id_adzone = ".$opt['id_adzone']);
            }else{
                $zone = $this->dbMulti("SELECT * FROM k_adzone ".$opt['sqlWhere']." ORDER BY zoneName ASC");
            }

        if($opt['debug']) $this->pre($this->db_query, $this->db_error);

        return $zone;
    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function adZoneSet($id_adzone, $def){

        if($id_adzone  > 0){
            $q = $this->dbUpdate($def)." WHERE id_adzone = ".$id_adzone;
        }else{
            $q = $this->dbInsert($def);
        }

        @$this->dbQuery($q);
        if($this->db_error != NULL) return false;

        $this->id_adzone = ($id_adzone > 0) ? $id_adzone : $this->db_insert_id;

        return true;
    }


    /* + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + -
    + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - */
    public function asZoneRemove($id_adzone){

        if($id_adzone == NULL) return false;

        $this->dbQuery("DELETE FROM k_adzone WHERE id_adzone=".$id_adzone);

        return true;
    }
    public function adIsCrawler() {
        if(!defined('IS_CRAWLER')) {
            $agentArray = array("ArchitextSpider", "Googlebot", "TeomaAgent",
                "Zyborg", "Gulliver", "Architext spider", "FAST-WebCrawler",
                "Slurp", "Ask Jeeves", "ia_archiver", "Scooter", "Mercator",
                "crawler@fast", "Crawler", "InfoSeek Sidewinder",
                "almaden.ibm.com", "appie 1.1", "augurfind", "baiduspider",
                "bannana_bot", "bdcindexer", "docomo", "frooglebot", "geobot",
                "henrythemiragorobot", "sidewinder", "lachesis", "moget/1.0",
                "nationaldirectory-webspider", "naverrobot", "ncsa beta",
                "netresearchserver", "ng/1.0", "osis-project", "polybot",
                "pompos", "seventwentyfour", "steeler/1.3", "szukacz",
                "teoma", "turnitinbot", "vagabondo", "zao/0", "zyborg/1.0",
                "Lycos_Spider_(T-Rex)", "Lycos_Spider_Beta2(T-Rex)",
                "Fluffy the Spider", "Ultraseek", "MantraAgent","Moget",
                "T-H-U-N-D-E-R-S-T-O-N-E", "MuscatFerret", "VoilaBot",
                "Sleek Spider", "KIT_Fireball", "WISEnut", "WebCrawler",
                "asterias2.0", "suchtop-bot", "YahooSeeker", "ai_archiver",
                "Jetbot"
            );

            $theAgent = $_SERVER["HTTP_USER_AGENT"];

            foreach ($agentArray as $anAgent) {
                if (stripos($theAgent, $anAgent) !== false) {
                    define("IS_CRAWLER", true);
                    return true;
                };
            };
            define("IS_CRAWLER", false);
            return false;
        }else {
            return IS_CRAWLER;
        }
    }

}

?>