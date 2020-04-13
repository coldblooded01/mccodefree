<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class User {

    public function __construct($userid, $username, $userpass, $level, $exp, $exp_needed, $money, $crystals,
            $laston, $lastip, $energy, $will, $brave, $hp, $maxenergy, $maxwill, $maxbrave, $maxhp,
            $lastrest_life, $lastrest_other, $location, $hospital, $jail, $fedjail, $user_level,
            $gender, $daysold, $signedup, $course, $cdays, $donatordays, $email, $login_name,
            $display_pic, $duties, $bankmoney, $cybermoney, $staffnotes, $mailban, $mb_reason,
            $hospreason, $pass_salt, $strength, $agility, $guard) {
        $this->userid = $userid;
        $this->username = $username;
        $this->userpass = $userpass;
        $this->level = $level;
        $this->exp = $exp;
        $this->exp_needed = $exp_needed;
        $this->money = $money;
        $this->crystals = $crystals;
        $this->last_time_online = $laston;
        $this->last_ip = $lastip;
        $this->energy = $energy;
        $this->max_energy = $maxenergy;
        $this->will = $will;
        $this->max_will = $maxwill;
        $this->brave = $brave;
        $this->max_brave = $max_brave;
        $this->hp = $hp;
        $this->max_hp = $maxhp;
        $this->location = $location;
        $this->in_hospital = $hospital;
        $this->strength = $strength;
        $this->agility = $agility;
        $this->guard = $guard;
    }

    public static function getAll() {

    }

    public static function exists($userid) {
        global $c;
        $query = "SELECT u.*,us.* FROM users u LEFT JOIN userstats us ON u.userid=us.userid WHERE u.userid={$userid}";
        $q = mysqli_query(
            $c,
            $query
        );
        return mysqli_num_rows($q) != 0;
    }

    public static function get($userid) {
        global $c;
        $query = "SELECT u.*,us.*
            FROM users u 
            LEFT JOIN userstats us ON u.userid=us.userid 
            WHERE u.userid=$userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        return new User(
            $r['userid'],
            $r['username'],
            $r['userpass'],
            $r['level'],
            $r['exp'],
            $r['exp_needed']
            $r['money'],
            $r['crystals'],
            $r['laston'],
            $r['lastip'],
            $r['energy'],
            $r['will'],
            $r['brave'],
            $r['hp'],
            $r['maxenergy'],
            $r['maxwill'],
            $r['maxbrave'],
            $r['maxhp'],
            $r['lastrest_life'],
            $r['lastrest_other'],
            $r['location'],
            $r['hospital'],
            $r['jail'],
            $r['fedjail'],
            $r['user_level'],
            $r['gender'],
            $r['daysold'],
            $r['signedup'],
            $r['course'],
            $r['cdays'],
            $r['donatordays'],
            $r['email'],
            $r['login_name'],
            $r['display_pic'],
            $r['duties'],
            $r['bankmoney'],
            $r['cybermoney'],
            $r['staffnotes'],
            $r['mailban'],
            $r['mb_reason'],
            $r['hospreason'],
            $r['pass_salt'],
            $r['strength'],
            $r['agility'],
            $r['guard']
        );
    }

    public function is_in_hospital() {
        return $this->in_hospital != 0;
    }

    public function is_unconscious() {
        return $this->hp == 1;
    }

    public function has_energy_to_attack() {
        return $this->energy  >= $this->max_energy / 2;
    }

    public function kill() {
        $ZERO_HP = 0;
        global $c;
        $this->hp = $ZERO_HP;
        $query = "UPDATE users SET hp=$ZERO_HP WHERE userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function damage($damage) {
        global $c;
        if ($damage < 1)
        {
            $damage = 1;
        }
        $this->hp -= $damage;

        $query = "UPDATE users SET hp=hp-$damage WHERE userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function spend_attack_energy() {
        global $c;
        $this->energy -= $this->max_energy / 2;
        $me = $user->energy;
        $query = "UPDATE users SET energy=energy-{$me} WHERE userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function exp_penalty() {
        global $c;
        $this->exp = 0;
        $query = "UPDATE users SET exp=0 where userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }
}