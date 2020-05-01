<?php

require_once(dirname(__FILE__) . "/../mysql.php");
require_once(dirname(__FILE__) . "/user.php");

class BlacklistRecord {

    public function __construct($id, $adder_user, $added_user, $comment) {
        $this->id = $id;
        $this->adder_user = $adder_user;
        $this->added_user = $added_user;
        $this->comment = $comment;
    }

    public static function create_from_mysqli_array($r) {
        return new BlacklistRecord(
            $r['bl_ID'],
            User::get($r['bl_ADDER']),
            User::get($r['bl_ADDED']),
            $r['bl_COMMENT']
        );
    }

    public static function add($adder_user, $added_user, $comment) {
        global $c;
        $query = "INSERT INTO blacklist VALUES(NULL, $adder_user->userid, {$added_user->userid}, '{$comment}')";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function get($id) {
        global $c;
        $query = "SELECT * FROM blacklist WHERE bl_ID=$id";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

    public static function filter_by_added($added_user) {
        global $c;
        $query = "SELECT * FROM blacklist WHERE bl_ADDED=$added_user->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $results = [];
        while($r = mysqli_fetch_array($q)) {
            array_push($results, self::create_from_mysqli_array($r));
        }
        mysqli_free_result($q);
        return $results;
    }

    public static function filter_by_adder($adder_id) {
        global $c;
        $query = "SELECT * FROM blacklist WHERE bl_ADDER=$adder_id";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $results = [];
        while($r = mysqli_fetch_array($q)) {
            array_push($results, self::create_from_mysqli_array($r));
        }
        mysqli_free_result($q);
        return $results;
    }

    public static function filter_most_hated() {
        global $c;
        $query = "SELECT u.userid,count( * ) as cnt FROM blacklist bl LEFT JOIN users u on bl.bl_ADDED=u.userid GROUP BY bl.bl_ADDED ORDER BY cnt DESC LIMIT 5";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $results = [];
        while($r = mysqli_fetch_array($q)) {
            array_push($results, User::get($r['userid']));
        }
        mysqli_free_result($q);
        return $results;
    }

    public static function is_user_in_blacklist($adder_user, $added_user) {
        global $c;
        $query = "SELECT * FROM blacklist WHERE bl_ADDER=$adder_user->userid AND bl_ADDED={$added_user->userid}";
        $q = mysqli_query(
            $c,
            $query
        );
        $num_rows = mysqli_num_rows($q);
        mysqli_free_result($q);
        return $num_rows != 0;
    }

    public static function remove($id, $adder_id) {
        global $c;
        $query = "DELETE FROM blacklist WHERE bl_ID={$id} AND bl_ADDER=$adder_id";
        $q = mysqli_query(
            $c,
            $query
        );
        mysqli_free_result($q);
    }

    public static function edit_comment($id, $adder_id, $new_comment) {
        global $c;
        $query = "UPDATE blacklist SET bl_COMMENT='{$new_comment}' WHERE bl_ID={$id} AND bl_ADDER=$adder_id";
        $q = mysqli_query(
            $c,
            $query
        );
        mysqli_free_result($q);
    }

    public static function check_adder($id, $adder_id) {
        global $c;
        $query = "SELECT * FROM blacklist WHERE bl_ID={$_GET['f']} AND bl_ADDER=$adder_id";
        $q = mysqli_query(
            $c,
            $query
        );
        $num_rows = mysqli_num_rows($q);
        mysqli_free_result($q);
        return $num_rows != 0;
    }
}