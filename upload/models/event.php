<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class Event {

    public function __construct($event_id, $event_user, $event_time, $event_read, $event_text) {
        $this->id = $event_id;
        $this->user_id = $event_user;
        $this->time = $event_time;
        $this->read = $event_read;
        $this->text = $event_text;
    }

    public function read() {
        global $c;
        $this->read = 1;
        $query = "UPDATE events SET evREAD=1 WHERE evID=$this->id";
        mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function is_new() {
        return $this->read == 0;
    }

    public function delete() {
        global $c;
        self::delete_event($this->id, $this->user_id);
    }

    public static function create($r) {
        return new Event(
            $r['evID'],
            $r['evUSER'],
            $r['evTIME'],
            $r['evREAD'],
            $r['evTEXT']
        );
    }

    public static function add($user_id, $text) {
        global $c;
        $query = "INSERT INTO events VALUES(NULL, $user_id, " . time() . ", 0, '$text')";
        $r = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        return 1;
    }

    public static function count_new_events($user_id) {
        global $c;
        $query = "SELECT COUNT(*) as cnt FROM events WHERE evUSER={$user_id} AND evREAD=0";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return $r['cnt'];
    }

    public static function count_total_events() {
        global $c;
        $query = "SELECT COUNT(`evID`) as cnt FROM events";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return $r['cnt'];
    }

    public static function get_events_for_user($user_id, $limit=10) {
        global $c;
        $query = "SELECT * FROM events WHERE evUSER={$user_id} ORDER BY evTIME DESC LIMIT {$limit}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $events = [];
        while ($r = mysqli_fetch_array($q)) {
            array_push($events, self::create($r));
        }
        mysqli_free_result($q);
        return $events;
    }

    public static function delete_event($event_id, $user_id) {
        global $c;
        $query = "DELETE FROM events WHERE evID={$event_id} AND evUSER=$user_id";
        mysqli_query(
            $c,
            $query
        );
    }

    public static function mark_all_as_read($user_id) {
        global $c;
        mysqli_query($c, "UPDATE events SET evREAD=1 WHERE evUSER=$user_id");
    }

}
