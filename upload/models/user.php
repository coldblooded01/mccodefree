<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class User {

    public function __construct($userid, $username, $userpass, $level, $exp, $exp_needed, $money, $crystals,
            $laston, $lastip, $energy, $will, $brave, $hp, $maxenergy, $maxwill, $maxbrave, $maxhp,
            $lastrest_life, $lastrest_other, $location, $hospital, $jail, $fedjail, $user_level,
            $gender, $daysold, $signedup, $course, $cdays, $donatordays, $email, $login_name,
            $display_pic, $duties, $bankmoney, $cybermoney, $staffnotes, $mailban, $mb_reason,
            $hospreason, $pass_salt, $strength, $agility, $guard, $labour, $IQ) {
        $this->userid = $userid;
        $this->username = $username;
        $this->userpass = $userpass;
        $this->level = $level;
        $this->exp = $exp;
        $this->money = $money;
        $this->crystals = $crystals;
        $this->last_time_online = $laston;
        $this->last_ip = $lastip;
        $this->energy = $energy;
        $this->max_energy = $maxenergy;
        $this->will = $will;
        $this->max_will = $maxwill;
        $this->brave = $brave;
        $this->max_brave = $maxbrave;
        $this->hp = $hp;
        $this->max_hp = $maxhp;
        $this->location = $location;
        $this->in_hospital = $hospital;
        $this->jail = $jail;
        $this->fedjail = $fedjail;
        $this->user_level = $user_level;
        $this->gender = $gender;
        $this->daysold = $daysold;
        $this->donatordays = $donatordays;
        $this->course = $course;
        $this->cdays = $cdays;
        $this->email = $email;
        $this->bank_money = $bankmoney;
        $this->strength = $strength;
        $this->agility = $agility;
        $this->guard = $guard;
        $this->labour = $labour;
        $this->IQ = $IQ;
    }

    public static function search($levelmin, $levelmax, $nom, $gender, $house, $online, $dayo_min, $dayo_max) {
        global $c;

        $levelmin_clause = "WHERE level >= '{$levelmin}'";
        $levelmax_clause = " AND level <= '{$levelmax}'";
        $name_clause = ($nom) ? " AND username LIKE('%{$nom}%')" : "";
        $gender_clause = ($gender) ? " AND gender = '{$gender}'" : "";
        $house_clause = ($house) ? " AND maxwill = '{$house}'" : "";
        $online_clause = ($online) ? " AND laston >= " . (time() - $online) : "";
        $daysmin_clause = ($dayo_min) ? " AND daysold >= '{$dayo_min}'" : "";
        $daysmax_clause = ($dayo_max) ? " AND daysold <= '{$dayo_max}'" : "";

        $query = "SELECT * FROM users $levelmin_clause$levelmax_clause$name_clause$gender_clause$house_clause$online_clause$daysmin_clause$daysmax_clause";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create($r));
        }
        return $result;
    }

    public static function create($r) {
        return new User(
            $r['userid'],
            $r['username'],
            $r['userpass'],
            $r['level'],
            $r['exp'],
            $r['exp_needed'],
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
            $r['guard'],
            $r['labour'],
            $r['IQ']
        );
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
        return self::create($r);
    }

    public static function get_mailban() {
        global $c;
        $query = "SELECT * FROM users WHERE mailban>0 ORDER BY mailban ASC";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create($r));
        }
        return $result;
    }

    public static function get_users_in_fedjail() {
        global $c;
        $query = "SELECT f.*,u.username,u2.username as jailer FROM fedjail f LEFT JOIN users u ON f.fed_userid=u.userid LEFT JOIN users u2 ON f.fed_jailedby=u2.userid ORDER BY f.fed_days ASC";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create($r));
        }
        return $result;
    }

    public function is_in_hospital() {
        return $this->in_hospital != 0;
    }

    public function is_unconscious() {
        return $this->hp == 1;
    }

    public function is_donator() {
        return $this->donatordays > 0;
    }

    public function is_male() {
        return $this->gender == "Male";
    }

    public function has_energy_to_attack() {
        return $this->energy > ($this->max_energy / 2);
    }

    public function get_last_visit() {
        return date('F j, Y, g:i a', $this->last_time_online);
    }

    public function get_house() {
        global $c;
        $query = "SELECT * FROM houses WHERE hWILL = {$this->max_will}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        return $r;
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

    public function get_exp_needed() {
        return ($this->level + 1) ^ 3;
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
        $me = $this->max_energy / 2;
        $query = "UPDATE users SET energy=energy-{$me} WHERE userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function exp_penalty() {
        global $c;
        $this->exp = 0;
        $query = "UPDATE users SET exp=0 WHERE userid=$this->userid";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
    }

    public function check_level()
    {
        global $c;
        if ($this->exp >= $this->get_exp_needed())
        {
            $expu = $this->exp - $this->get_exp_needed();
            $this->level += 1;
            $this->exp = $expu;
            $this->energy += 2;
            $this->brave += 2;
            $this->max_energy += 2;
            $this->max_brave += 2;
            $this->hp += 50;
            $this->max_hp += 50;
            mysqli_query(
                $c,
                "UPDATE users SET level=level+1,exp=$expu,energy=energy+2,brave=brave+2,maxenergy=maxenergy+2,maxbrave=maxbrave+2,
                    hp=hp+50,maxhp=maxhp+50 where userid=$this->userid"
            );
        }
}
}