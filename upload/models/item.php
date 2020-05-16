<?php

require_once(dirname(__FILE__) . "/../mysql.php");
require_once(dirname(__FILE__) . "/item_type.php");

class Item {

    public function __construct($id, $item_type, $name, $description, $buy_price, $sell_price, $buyable) {
        $this->id = $id;
        $this->item_type = $item_type;
        $this->name = $name;
        $this->description = $description;
        $this->buy_price = $buy_price;
        $this->sell_price = $sell_price;
        $this->buyable = $buyable;
    }

    public static function create_from_mysqli_array($r) {
        return new Item(
            $r['itmid'],
            ItemType::get($r['itmtype']),
            $r['itmname'],
            $r['itmdesc'],
            $r['itmbuyprice'],
            $r['itmsellprice'],
            $r['itmbuyable']
        );
    }

    public static function get_all($order_by='itmname', $order_dir='ASC') {
        global $c;
        $query = "SELECT * FROM items ORDER BY {$order_by} {$order_dir}";
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

    public static function get($id) {
        global $c;
        $query = "SELECT * FROM items WHERE itmid={$id}";
        $q = mysqli_query(
            $c,
            $query
        );
        $r = mysqli_fetch_array($q);
        mysqli_free_result($q);
        return self::create_from_mysqli_array($r);
    }

    public static function exists($id) {
        global $c;
        $query = "SELECT * FROM items WHERE itmid={$id}";
        $q = mysqli_query(
            $c,
            $query
        );
        $num_rows = mysqli_num_rows($q);
        mysqli_free_result($q);
        return $num_rows != 0;
    }

    public static function add($name, $item_type_id, $description, $buy_price, $sell_price, $buyable) {
        global $c;
        
        $query = "INSERT INTO items VALUES (
            NULL,
            {$item_type_id},
            '{$name}',
            '{$description}',
            {$buy_price},
            {$sell_price},
            {$buyable}
        )";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
        return mysqli_insert_id($c);
    }

    public function is_buyable() {
        return !!$this->buyable;
    }

    public function is_food() {
        return $this->item_type->id == ItemType::$FOOD;
    }

    public function is_weapon() {
        $item_type_id = $this->item_type->id;
        return $item_type_id == ItemType::$MELEE_WEAPON || $item_type_id == ItemType::$GUN;
    }

    public function is_medical() {
        return $this->item_type->id == ItemType::$MEDICAL;
    }

    public function is_armour() {
        return $this->item_type->id == ItemType::$ARMOUR;
    }

    public function save() {
        global $c;
        $query = "UPDATE items 
            SET 
                itmtype={$this->item_type->id},
                itmname='{$this->name}',
                itmdesc='{$this->description}',
                itmbuyprice={$this->buy_price},
                itmsellprice={$this->sell_price},
                itmbuyable={$this->buyable}
            WHERE itmid={$this->id}";
        $q = mysqli_query(
            $c,
            $query
        ) or die(mysqli_error($c));
        mysqli_free_result($q);
    }
}