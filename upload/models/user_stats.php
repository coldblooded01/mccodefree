<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class UserStats {

    const STRENGTH = "strength";
    const AGILITY = "agility";
    const GUARD = "guard";
    const LABOUR = "labour";
    const IQ = "IQ";

    public function __construct($user_id, $strength, $agility, $guard, $labour, $iq) {
        $this->user_id = $user_id;
        $this->strength = $strength;
        $this->agility = $agility;
        $this->guard = $guard;
        $this->labour = $labour;
        $this->IQ = $iq;
    }

    public function set_stat($stat, $new_value) {
        global $c;
        
        $stat = self::get_stat_field_name($stat);
        $this->{$stat} = $new_value;
        $query = "UPDATE userstats SET $stat=$new_value WHERE userid=$this->user_id";
        $q = mysqli_query(
            $c,
            $query
        ) or die(
            "Couldn't set stat $stat, value: {$new_value} - ". mysqli_error($c));
    }

    public function increase_stat($stat, $inc) {
        $new_value = $this->{$stat} + $inc;
        $this->set_stat($stat, $new_value);
    }

    public function set_iq($new_iq) {
        $this->set_stat(self::IQ, $new_iq);
    }

    public function increase_iq($iq_inc) {
        $this->set_iq($this->iq + $iq_inc);
    }

    public function set_strength($new_strength) {
        $this->set_stat(self::STRENGTH, $new_strength);
    }

    public function increase_strength($strength_inc) {
        $this->set_strength($this->strength + $strength_inc);
    }

    public function set_agility($new_agility) {
        $this->set_stat(self::AGILITY, $new_agility);
    }

    public function increase_agility($agility_inc) {
        $this->set_agility($this->agility + $agility_inc);
    }

    public function set_guard($new_guard) {
        $this->set_stat(self::GUARD, $new_guard);
    }

    public function increase_guard($guard_inc) {
        $this->set_guard($this->guard + $guard_inc);
    }

    public function set_labour($new_labour) {
        $this->set_stat(self::LABOUR, $new_labour);
    }

    public function increase_labour($labour_inc) {
        $this->set_labour($this->labour + $labour_inc);
    }

    public static function create_from_mysqli_array($r) {
        return new UserStats(
            $r['userid'],
            $r['strength'],
            $r['agility'],
            $r['guard'],
            $r['labour'],
            $r['IQ']
        );
    }

    public static function get($user_id) {
        global $c;
        $query = "SELECT * FROM userstats WHERE userid={$user_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

    public static function add_user_stats($user_id) {
        global $c;
        
        $query = "INSERT INTO userstats VALUES ($user_id, 10, 10, 10, 10, 10)";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function get_stat_field_name($stat) {
        switch ($stat) {
            case self::STRENGTH:
                return self::STRENGTH;
            case self::AGILITY:
                return self::AGILITY;
            case self::GUARD:
                return self::GUARD;
            case self::LABOUR:
                return self::LABOUR;
            case self::IQ:
                return self::IQ;
            default:
                die("Bad stat field name");
        }
    }


}
