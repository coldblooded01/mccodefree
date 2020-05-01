<?php

require_once(dirname(__FILE__) . "/../mysql.php");

class House {

    public function __construct($id, $name, $price, $will) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->will = $will;
    }

    public static function create_from_mysqli_array($r) {
        return new House(
            $r['hID'],
            $r['hNAME'],
            $r['hPRICE'],
            $r['hWILL']
        );
    }

    public static function add($name, $price, $will) {
        global $c;
        
        $query = "INSERT INTO houses (hNAME, hPRICE, hWILL) VALUES ('{$name}', '{$price}', '{$will}')";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }

    public static function get($house_id) {
        global $c;
        $query = "SELECT * FROM houses WHERE hID={$house_id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

    public static function get_by_will($will) {
        global $c;
        $query = "SELECT * FROM houses WHERE hWILL = {$will}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

    public static function get_all($order_by='hID', $order_dir='ASC') {
        global$c;
        $query = "SELECT * FROM houses ORDER BY {$order_by} {$order_dir}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create_from_mysqli_array($r));
        }
        mysqli_free_result($q);
        return $result;
    }

    public static function filter_by_will_gt($will, $order_by='hID', $order_dir='ASC') {
        global$c;
        $query = "SELECT * FROM houses WHERE hWILL>$will ORDER BY {$order_by} {$order_dir}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        $result = [];
        while ($r = mysqli_fetch_array($q))
        {
            array_push($result, self::create_from_mysqli_array($r));
        }
        mysqli_free_result($q);
        return $result;
    }
}