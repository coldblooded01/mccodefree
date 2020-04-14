<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class Ad {

    public function __construct($ad_id, $ad_img, $ad_url, $ad_views, $ad_clicks, $ad_login, $ad_pass) {
        $this->id = $ad_id;
        $this->img = $ad_img;
        $this->url = $ad_url;
        $this->views = $ad_views;
        $this->clicks = $ad_clicks;
        $this->login = $ad_login;
        $this->pass = $ad_pass;
    }

    public static function get($id) {
        global $c;
        $query = "SELECT * FROM ads WHERE adID={$id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        return new Ad(
            $r['adID'],
            $r['adIMG'],
            $r['adURL'],
            $r['adVIEWS'],
            $r['adCLICKS'],
            $r['adLOGIN'],
            $r['adPASS']
        );
    }

    public static function get_random() {
        global $c;
        $query = "SELECT * FROM ads ORDER BY rand() LIMIT 1";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        return new Ad(
            $r['adID'],
            $r['adIMG'],
            $r['adURL'],
            $r['adVIEWS'],
            $r['adCLICKS'],
            $r['adLOGIN'],
            $r['adPASS']
        );
    }

    public static function exists($id) {
        global $c;
        $query = "SELECT * FROM ads WHERE adID={$id}";
        $q = mysqli_query(
            $c,
            $query
        );
        return mysqli_num_rows($q) != 0;
    }

    public function click() {
        global $c;
        $this->clicks += 1;
        $query = "UPDATE ads SET adCLICKS=adCLICKS+1 WHERE adID={$this->id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function view() {
        global $c;
        $this->views += 1;
        $query = "UPDATE ads SET adVIEWS=adVIEWS+1 WHERE adID={$this->id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }
}