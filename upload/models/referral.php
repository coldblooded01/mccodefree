<?php

require_once(dirname(__FILE__) . "/../mysql.php");
require_once(dirname(__FILE__) . "/user.php");

class Referral {

    public function __construct($id, $referer, $refered, $time, $referral_ip, $refered_ip) {
        $this->id = $id;
        $this->referer = $referer;
        $this->refered = $refered;
        $this->time = $time;
        $this->referer_ip = $referer_ip;
        $this->refered_ip = $refered_ip;
    }

    public static function create_from_mysqli_array($r) {
        return new Referral(
            $r['refID'],
            User::get($r['refREFER']),
            User::get($r['refREFED']),
            $r['refTIME'],
            $r['refREFERIP'],
            $r['refREFEDIP']
        );
    }

    public static function add($referer_id, $refered_id, $referer_ip, $refered_ip) {
        global $c;
        $query = "INSERT INTO `referals`
            VALUES(NULL, {$referer_id}, $refered_id, " . time()
            . ", '{$referer_ip}', '$refered_ip')";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function count_referrals_by_referer($referer_id) {
        global $c;
        $query = "SELECT * FROM referals WHERE refREFER={$referer_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die("SQL Query failed when trying to count referrals by referer id" . mysqli_error($c));
        $num_rows = mysqli_num_rows($q);
        mysqli_free_result($q);
        return $num_rows;
    }

    public static function change_refered_id($refered_id, $new_id) {
        global $c;
        $query = "UPDATE referals SET refREFED=$new_id WHERE refREFED = $refered_id";
        $q = mysqli_query(
            $c,
            $query
        ) or die("Referral - change_refered_id - " . mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function change_referer_id($referer_id, $new_id) {
        global $c;
        $query = "UPDATE referals SET refREFER=$new_id WHERE refREFER = $referer_id";
        $q = mysqli_query(
            $c,
            $query
        ) or die("Referral - change_referer_id - " . mysqli_error($c));
        mysqli_free_result($q);
    }
}